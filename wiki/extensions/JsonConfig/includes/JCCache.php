<?php
namespace JsonConfig;

use MWNamespace;
use stdClass;
use TitleValue;

/**
 * Represents a json blob on a remote wiki.
 * Handles retrieval (via HTTP) and memcached caching.
 */
class JCCache {
	private $titleValue, $key, $cache;
	/** @var stdClass */
	private $conf;

	/** @var bool|string|JCContent */
	private $content = null;

	/** @var int number of seconds to keep the value in cache */
	private $cacheExpiration;

	/**
	 * Constructor for JCCache
	 * ** DO NOT USE directly - call JCSingleton::getCachedStore() instead. **
	 *
	 * @param TitleValue $titleValue
	 * @param stdClass $conf
	 * @param JCContent $content
	 */
	function __construct( $titleValue, $conf, $content = null ) {
		global $wgJsonConfigCacheKeyPrefix;
		$this->titleValue = $titleValue;
		$this->conf = $conf;
		$flRev = $conf->flaggedRevs;
		$key = implode( ':', array(
			'JsonConfig',
			$wgJsonConfigCacheKeyPrefix,
			$conf->cacheKey,
			( $flRev === null ? '' : ( $flRev ? 'T' : 'F' ) ),
			$titleValue->getNamespace(),
			$titleValue->getDBkey() ) );
		if ( $conf->isLocal ) {
			$key = wfMemcKey( $key );
		}
		$this->cacheExpiration = $conf->cacheExp;
		$this->key = $key;
		$this->cache = wfGetCache( CACHE_ANYTHING );
		$this->content = $content ?: null ; // ensure that if we don't have content, we use 'null'
	}

	/**
	 * Retrieves content.
	 * @return string|JCContent|false: Content string/object or false if irretrievable.
	 */
	public function get() {
		if ( $this->content === null ) {
			$value = $this->memcGet(); // Get content from the memcached
			if ( $value === false ) {
				if ( $this->conf->store ) {
					$this->loadLocal(); // Get it from the local wiki
				} else {
					$this->loadRemote(); // Get it from HTTP
				}
				$this->memcSet(); // Save result to memcached
			} elseif ( $value === '' ) {
				$this->content = false; // Invalid ID was cached
			} else {
				$this->content = $value; // Content was cached
			}
		}

		return $this->content;
	}

	/**
	 * Retrieves content from memcached.
	 * @return string|bool Carrier config or false if not in cache.
	 */
	private function memcGet() {
		global $wgJsonConfigDisableCache;

		return $wgJsonConfigDisableCache ? false : $this->cache->get( $this->key );
	}

	/**
	 * Store $this->content in memcached.
	 * If the content is invalid, store an empty string to prevent repeated attempts
	 */
	private function memcSet() {
		global $wgJsonConfigDisableCache;
		if ( $wgJsonConfigDisableCache ) {
			return true;
		}
		$value = $this->content;
		$exp = $this->cacheExpiration;
		if ( !$value ) {
			$value = '';
			$exp = 10; // caching an error condition for short time
			wfLogWarning( "No content is available, caching empty '$this->titleValue' for $exp seconds" );
		} elseif ( !is_string( $value ) ) {
			$value = $value->getNativeData();
		}

		return $this->cache->set( $this->key, $value, $exp );
	}

	/**
	 * Delete any cached information related to this config
	 * @param null|bool $updateCacheContent controls if cache should be updated with the new content
	 *   false = only clear cache, true = set cache to the new value, null = use configuration settings
	 *   New content will be set only if it is present (either get() was called before, or it was set via ctor
	 */
	public function resetCache( $updateCacheContent = null ) {
		global $wgJsonConfigDisableCache;
		if ( !$wgJsonConfigDisableCache ) {
			if ( $this->content && ( $updateCacheContent === true ||
			                         ( $updateCacheContent === null && isset( $this->conf->store ) &&
			                           $this->conf->store->cacheNewValue ) )
			) {
				$this->memcSet(); // update cache with the new value
			} else {
				$this->cache->delete( $this->key ); // only delete existing value
			}
		}
	}

	/**
	 * Retrieves the config from the local storage, and sets $this->content to the content object or false
	 */
	private function loadLocal() {
		// @fixme @bug handle flagged revisions
		$title = \Title::newFromTitleValue( $this->titleValue );
		$result = \WikiPage::factory( $title )->getContent();
		if ( !$result ) {
			$result = false; // Keeping consistent with other usages
		} elseif ( !( $result instanceof JCContent ) ) {
			if ( $result->getModel() === CONTENT_MODEL_WIKITEXT ) {
				// If this is a regular wiki page, allow it to be parsed as a json config
				$result = $result->getNativeData();
			} else {
				wfLogWarning( "The locally stored wiki page '$this->titleValue' has unsupported content model'" );
				$result = false;
			}
		}
		$this->content = $result;
	}

	/**
	 * Retrieves the config using HTTP and sets $this->content to string or false
	 */
	private function loadRemote() {
		do {
			$result = false;
			$remote = $this->conf->remote;
			$req = JCUtils::initApiRequestObj( $remote->url, $remote->username, $remote->password );
			if ( !$req ) {
				break;
			}
			$ns =
				$this->conf->nsName ? $this->conf->nsName
					: MWNamespace::getCanonicalName( $this->titleValue->getNamespace() );
			$articleName = $ns . ':' . $this->titleValue->getText();
			$flrevs = $this->conf->flaggedRevs;
			// if flaggedRevs is false, get wiki page directly, otherwise get the flagged state first
			$res = $this->getPageFromApi( $articleName, $req, $flrevs === false
					? array(
						'action' => 'query',
						'titles' => $articleName,
						'prop' => 'revisions',
						'rvprop' => 'content',
						'continue' => '',
					)
					: array(
						'action' => 'query',
						'titles' => $articleName,
						'prop' => 'info|flagged',
						'continue' => '',
					) );
			if ( $res !== false &&
			     ( $flrevs === null || ( $flrevs === true && array_key_exists( 'flagged', $res ) ) )
			) {
				// If there is a stable flagged revision present, use it.
				// else - if flaggedRevs is null, use the latest revision that exists
				// otherwise, fail because flaggedRevs is true, which means we require rev to be flagged
				$res = $this->getPageFromApi( $articleName, $req, array(
					'action' => 'query',
					'revids' => array_key_exists( 'flagged', $res ) ? $res['flagged']['stable_revid']
						: $res['lastrevid'],
					'prop' => 'revisions',
					'rvprop' => 'content',
					'continue' => '',
				) );
			}
			if ( $res === false ) {
				break;
			}
			if ( !isset( $res['revisions'][0]['*'] ) ) {
				JCUtils::warn( 'Unable to get config content',
					array( 'title' => $articleName, 'result' => $res ) );
				break;
			}

			$result = $res['revisions'][0]['*'];

		} while( false );

		$this->content = $result;
	}

	/** Given a legal set of API parameters, return page from API
	 * @param string $articleName title name used for warnings
	 * @param \CurlHttpRequest|\PhpHttpRequest $req logged-in session
	 * @param array $query
	 * @return bool|mixed
	 */
	private function getPageFromApi( $articleName, $req, $query ) {

		$revInfo = JCUtils::callApi( $req, $query, 'get remote JsonConfig' );
		if ( $revInfo === false ) {
			return false;
		}
		if ( !isset( $revInfo['query']['pages'] ) ) {
			JCUtils::warn( 'Unrecognizable API result', array( 'title' => $articleName ), $query );
			return false;
		}
		$pages = $revInfo['query']['pages'];
		if ( !is_array( $pages ) || count( $pages ) !== 1 ) {
			JCUtils::warn( 'Unexpected "pages" element', array( 'title' => $articleName ), $query );
			return false;
		}
		$pageInfo = reset( $pages ); // get the only element of the array
		if ( isset( $revInfo['missing'] ) ) {
			JCUtils::warn( 'Config page does not exist', array( 'title' => $articleName ), $query );
			return false;
		}
		return $pageInfo;
	}
}
