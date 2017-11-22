<?php
/**
 * getID3 Metadata handler
 */
class ID3Handler extends TimedMediaHandler {
	// XXX match GETID3_VERSION ( too bad version is not a getter )
	const METADATA_VERSION = 2;

	/**
	 * @param $path string
	 * @return array
	 */
	protected function getID3( $path ) {
		// Create new id3 object:
		$getID3 = new getID3();

		// Don't grab stuff we don't use:
		$getID3->option_tag_id3v1         = false;  // Read and process ID3v1 tags
		$getID3->option_tag_id3v2         = false;  // Read and process ID3v2 tags
		$getID3->option_tag_lyrics3       = false;  // Read and process Lyrics3 tags
		$getID3->option_tag_apetag        = false;  // Read and process APE tags
		$getID3->option_tags_process      = false;  // Copy tags to root key 'tags' and encode to $this->encoding
		$getID3->option_tags_html         = false;  // Copy tags to root key 'tags_html' properly translated from various encodings to HTML entities

		// Analyze file to get metadata structure:
		$id3 = $getID3->analyze( $path );

		// remove file paths
		unset( $id3['filename'] );
		unset( $id3['filepath'] );
		unset( $id3['filenamepath']);

		// Update the version
		$id3['version'] = self::METADATA_VERSION;

		return $id3;
	}

	/**
	 * @param $file File
	 * @param $path string
	 * @return string
	 */
	function getMetadata( $file, $path ) {
		$id3 = $this->getID3( $path );
		return serialize( $id3 );
	}

	/**
	 * @param $metadata
	 * @return bool|mixed
	 */
	function unpackMetadata( $metadata ) {
		wfSuppressWarnings();
		$unser = unserialize( $metadata );
		wfRestoreWarnings();
		if ( isset( $unser['version'] ) && $unser['version'] == self::METADATA_VERSION ) {
			return $unser;
		} else {
			return false;
		}
	}

	/**
	 * @param $file File
	 * @return mixed
	 */
	function getBitrate( $file ){
		$metadata = $this->unpackMetadata( $file->getMetadata() );
		if ( !$metadata || isset( $metadata['error'] ) ) {
			return 0;
		} else {
			return $metadata['bitrate'];
		}
	}

	/**
	 * @param $file File
	 * @return int
	 */
	function getLength( $file ) {
		$metadata = $this->unpackMetadata( $file->getMetadata() );
		if ( !$metadata || isset( $metadata['error'] ) ) {
			return 0;
		} else {
			return $metadata['playtime_seconds'];
		}
	}

	/**
	 * @param $file File
	 * @return bool|int
	 */
	function getFramerate( $file ){
		$metadata = $this->unpackMetadata( $file->getMetadata() );
		if ( !$metadata || isset( $metadata['error'] ) ) {
			return 0;
		} else {
			// return the frame rate of the first found video stream:
			if( isset( $metadata['video'] )
				&& isset( $metadata['video']['frame_rate'] ) ) {
				return $metadata['video']['frame_rate'];
			}
			return false;
		}
	}
}
