<?php

/**
 * MwEmbedResourceManager adds some convenience functions for loading mwEmbed 'modules'.
 *  Its shared between the mwEmbedStandAlone and the MwEmbed extension
 *
 * @file
 * @ingroup Extensions
 */
class MwEmbedResourceManager {
	protected static $moduleSet = array();
	protected static $moduleConfig = array();

	/**
	 * Register mwEmbeed resource set based on the
	 *
	 * Adds modules to ResourceLoader
	 * @param $mwEmbedResourcePath string
	 * @throws MWException
	 */
	public static function register( $mwEmbedResourcePath ) {
		global $IP, $wgExtensionMessagesFiles;
		$localResourcePath = $IP . '/' . $mwEmbedResourcePath;
		// Get the module name from the end of the path:
		$modulePathParts = explode( '/', $mwEmbedResourcePath );
		$moduleName = array_pop( $modulePathParts );
		if ( !is_dir( $localResourcePath ) ) {
			throw new MWException( __METHOD__ . " not given readable path: " . htmlspecialchars( $localResourcePath ) );
		}

		if ( substr( $mwEmbedResourcePath, -1 ) == '/' ) {
			throw new MWException( __METHOD__ . " path has trailing slash: " . htmlspecialchars( $localResourcePath ) );
		}

		// Check that resource file is present:
		$resourceListFilePath = $localResourcePath . '/' . $moduleName . '.php';
		if ( !is_file( $resourceListFilePath ) ) {
			throw new MWException( __METHOD__ . " mwEmbed Module is missing resource list: " . htmlspecialchars( $resourceListFilePath ) );
		}
		// Get the mwEmbed module resource registration:
		$resourceList = include $resourceListFilePath;

		// Look for special 'messages' => 'moduleFile' key and load all modules file messages:
		foreach ( $resourceList as $name => $resources ) {
			if ( isset( $resources['messageFile'] ) && is_file( $localResourcePath . '/' . $resources['messageFile'] ) ) {
				$resourceList[$name]['messages'] = array();
				include $localResourcePath . '/' . $resources['messageFile'];
				foreach ( $messages['en'] as $msgKey => $na ) {
					$resourceList[$name]['messages'][] = $msgKey;
				}
			}

			if ( isset( $resources['messageDir'] ) ) {
				$filename = $localResourcePath . '/' . $resources['messageDir'] . '/en.json';
				$resourceList[$name]['messages'] = self::readJSONFileMessageKeys( $filename );
			}
		};

		// Check for module loader:
		if ( is_file( $localResourcePath . '/' . $moduleName . '.loader.js' ) ) {
			$resourceList[$moduleName . '.loader'] = array(
				'loaderScripts' => $moduleName . '.loader.js'
			);
		}

		// Check for module config ( @@TODO support per-module config )
		$configPath = $localResourcePath . '/' . $moduleName . '.config.php';
		if ( is_file( $configPath ) ) {
			self::$moduleConfig = array_merge( self::$moduleConfig, include $configPath );
		}

		// Add the resource list into the module set with its provided path
		self::$moduleSet[$mwEmbedResourcePath] = $resourceList;
	}

	/**
	 * @param $vars array
	 * @return array
	 */
	public static function registerConfigVars( &$vars ) {
		// Allow localSettings.php to override any module config by updating $wgMwEmbedModuleConfig var
		global $wgMwEmbedModuleConfig;
		foreach ( self::$moduleConfig as $key => $value ) {
			if ( !isset( $wgMwEmbedModuleConfig[$key] ) ) {
				$wgMwEmbedModuleConfig[$key] = $value;
			}
		}
		$vars = array_merge( $vars, $wgMwEmbedModuleConfig );

		return $vars;
	}

	/**
	 * ResourceLoaderRegisterModules hook
	 *
	 * Adds any mwEmbedResources to the ResourceLoader
	 * @param $resourceLoader ResourceLoader
	 * @return bool
	 */
	public static function registerModules( &$resourceLoader ) {
		global $IP, $wgMwEmbedResourceLoaderFileModule, $wgScriptPath;
		// Register all the resources with the resource loader
		foreach ( self::$moduleSet as $path => $modules ) {
			//remove 'extension' prefix from path
			$remoteExtPath = explode( '/', $path );
			array_shift( $remoteExtPath );
			$remoteExtPath = implode( '/', $remoteExtPath );
			foreach ( $modules as $name => $resources ) {
				$resources['remoteExtPath'] = $remoteExtPath;

				// If running as mediawiki extension ResourceLoaderFileModule is used
				// add $wgScriptPath to path
				if ( $wgMwEmbedResourceLoaderFileModule == 'ResourceLoaderFileModule' ) {
					$resourcePath = $wgScriptPath . '/' . $path;
				} else {
					$resourcePath = $path;
				}
				$resourceLoader->register(
					$name, new $wgMwEmbedResourceLoaderFileModule(
						$resources, "$IP/$path", $resourcePath
					)
				);
			}
		}

		// Continue module processing
		return true;
	}

	/**
	 * Read a JSON file containing localisation messages and returns the
	 * message keys in it.
	 * This is copied and adapted of LocalisationCache::readJSONFile().
	 *
	 * @param string $fileName Name of file to read
	 * @throws MWException if there is a syntax error in the JSON file
	 * @return array with a 'messages' key, or empty array if the file doesn't exist
	 */
	public static function readJSONFileMessageKeys( $fileName ) {
		wfProfileIn( __METHOD__ );

		if ( !is_readable( $fileName ) ) {
			wfProfileOut( __METHOD__ );

			return array();
		}

		$json = file_get_contents( $fileName );
		if ( $json === false ) {
			wfProfileOut( __METHOD__ );

			return array();
		}

		$data = FormatJson::decode( $json, true );
		if ( $data === null ) {
			wfProfileOut( __METHOD__ );

			throw new MWException( __METHOD__ . ": Invalid JSON file: $fileName" );
		}

		// Remove keys starting with '@', they're reserved for metadata and non-message data
		foreach ( $data as $key => $unused ) {
			if ( $key === '' || $key[0] === '@' ) {
				unset( $data[$key] );
			}
		}

		wfProfileOut( __METHOD__ );

		// Only array (message) keys needed.
		return array_keys( $data );
	}
}
