<?php
/**
 * MobileFrontend.body.php
 */

use Wikibase\Client\WikibaseClient;
use Wikibase\DataModel\Entity\ItemId;

/**
 * Implements additional functions to use in MobileFrontend
 */
class ExtMobileFrontend {
	/**
	 * Uses EventLogging when available to record an event on server side
	 *
	 * @param string $schema The name of the schema
	 * @param int $revision The revision of the schema
	 * @param array $data The data to be recorded against the schema
	 */
	public static function eventLog( $schema, $revision, $data ) {
		if ( is_callable( 'EventLogging::logEvent' ) ) {
			EventLogging::logEvent( $schema, $revision, $data );
		}
	}

	/**
	 * Transforms content to be mobile friendly version.
	 * Filters out various elements and runs the MobileFormatter.
	 * @param OutputPage $out
	 *
	 * @return string
	 */
	public static function DOMParse( OutputPage $out ) {
		$html = $out->getHTML();

		$context = MobileContext::singleton();

		$formatter = MobileFormatter::newFromContext( $context, $html );

		Hooks::run( 'MobileFrontendBeforeDOM', array( $context, $formatter ) );

		$title = $out->getTitle();
		$isSpecialPage = $title->isSpecialPage();
		$formatter->enableExpandableSections(
			// Don't collapse sections e.g. on JS pages
			$out->canUseWikiPage()
			&& $out->getWikiPage()->getContentModel() == CONTENT_MODEL_WIKITEXT
			// And not in certain namespaces
			&& array_search(
				$title->getNamespace(),
				$context->getMFConfig()->get( 'MFNamespacesWithoutCollapsibleSections' )
			) === false
			// And not when what's shown is not actually article text
			&& $context->getRequest()->getText( 'action', 'view' ) == 'view'
		);
		if ( $context->getContentTransformations() ) {
			// Remove images if they're disabled from special pages, but don't transform otherwise
			$formatter->filterContent( /* remove defaults */ !$isSpecialPage );
		}

		$contentHtml = $formatter->getText();

		return $contentHtml;
	}

	/**
	 * Returns the Wikibase entity associated with a page or null if none exists.
	 *
	 * @param string $item Wikibase id of the page
	 * @return mw.wikibase.entity|null
	 */
	public static function getWikibaseEntity( $item ) {
		if ( !class_exists( 'Wikibase\\Client\\WikibaseClient' ) ) {
			return null;
		}

		try {
			$entityLookup = WikibaseClient::getDefaultInstance()
				->getStore()
				->getEntityLookup();
			$entity = $entityLookup->getEntity( new ItemId( $item ) );
			if ( !$entity ) {
				return null;
			} else {
				return $entity;
			}
		} catch ( Exception $ex ) {
			// Do nothing, exception mostly due to description being unavailable in needed language
			return null;
		}
	}

	/**
	 * Returns the value of Wikibase property. Returns null if doesn't exist.
	 *
	 * @param string $item Wikibase id of the page
	 * @param string $property Wikibase property id to retrieve
	 * @return mixed|null
	 */
	public static function getWikibasePropertyValue( $item, $property ) {
		$value = null;
		$entity = self::getWikibaseEntity( $item );

		try {
			if ( !$entity ) {
				return null;
			} else {
				$statements = $entity->getStatements()->getByPropertyId(
						new Wikibase\DataModel\Entity\PropertyId(
							$property
						)
					)->getBestStatements();
				if ( !$statements->isEmpty() ) {
					$statements = $statements->toArray();
					$snak = $statements[0]->getMainSnak();
					if ( $snak instanceof Wikibase\DataModel\Snak\PropertyValueSnak ) {
						$value = $snak->getDataValue()->getValue();
					}
				}
				return $value;
			}
		} catch ( Exception $ex ) {
			return null;
		}
	}

	/**
	 * Returns a short description of a page from Wikidata
	 *
	 * @param string $item Wikibase id of the page
	 * @return string|null
	 */
	public static function getWikibaseDescription( $item ) {
		global $wgContLang;

		$entity = self::getWikibaseEntity( $item );

		try {
			if ( !$entity ) {
				return null;
			} else {
				return $entity->getFingerprint()->getDescription( $wgContLang->getCode() )->getText();
			}
		} catch ( Exception $ex ) {
			return null;
		}
	}
}
