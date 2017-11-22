<?php
/**
 * MP4 handler
 */
class Mp4Handler extends ID3Handler {

	/**
	 * @param $path string
	 * @return array
	 */
	protected function getID3( $path ) {
		$id3 = parent::getID3( $path );
		// Unset some parts of id3 that are too detailed and matroska specific:
		unset( $id3['quicktime'] );
		return $id3;
	}

	/**
	 * Get the "media size"
	 * @param $file File
	 * @param $path string
	 * @param $metadata bool
	 * @return array|bool
	 */
	function getImageSize( $file, $path, $metadata = false ) {
		// Just return the size of the first video stream
		if ( $metadata === false ) {
			$metadata = $file->getMetadata();
		}
		$metadata = $this->unpackMetadata( $metadata );
		if ( isset( $metadata['error'] ) ) {
			return false;
		}
		if( isset( $metadata['video']['resolution_x'])
				&&
			isset( $metadata['video']['resolution_y'])
		){
			return array (
				$metadata['video']['resolution_x'],
				$metadata['video']['resolution_y']
			);
		}
		return array( false, false );
	}

	/**
	 * @param $image
	 * @return string
	 */
	function getMetadataType( $image ) {
		return 'mp4';
	}
	/**
	 * @param $file File
	 */
	function getWebType( $file ) {
		/**
		 * h.264 profile types:
			H.264 Simple baseline profile video (main and extended video compatible) level 3 and Low-Complexity AAC audio in MP4 container:
			type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'

			H.264 Extended profile video (baseline-compatible) level 3 and Low-Complexity AAC audio in MP4 container:
			type='video/mp4; codecs="avc1.58A01E, mp4a.40.2"'

			H.264 Main profile video level 3 and Low-Complexity AAC audio in MP4 container
			type='video/mp4; codecs="avc1.4D401E, mp4a.40.2"'

			H.264 ‘High’ profile video (incompatible with main, baseline, or extended profiles) level 3 and Low-Complexity AAC audio in MP4 container
			type='video/mp4; codecs="avc1.64001E, mp4a.40.2"'
		 */
		// all h.264 encodes are currently simple profile
		return 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"';
	}
	/**
	 * @param $file File
	 * @return array|bool
	 */
	function getStreamTypes( $file ) {
		$streamTypes = array();
		$metadata = self::unpackMetadata( $file->getMetadata() );
		if ( !$metadata || isset( $metadata['error'] ) ) {
			return false;
		}
		if( isset( $metadata['audio'] ) && $metadata['audio']['dataformat'] == 'mp4' ){
			if( isset( $metadata['audio']['codec'] )
				&&
				strpos( $metadata['audio']['codec'] , 'AAC' ) !== false
			){
				$streamTypes[] =  'AAC';
			} else {
				$streamTypes[] = $metadata['audio']['codec'];
			}
		}
		// id3 gives 'V_VP8' for what we call VP8
		if( $metadata['video']['dataformat'] == 'quicktime' ){
			$streamTypes[] =  'h.264';
		}

		return $streamTypes;
	}

	/**
	 * @param $file File
	 * @return String
	 */
	function getShortDesc( $file ) {
		$streamTypes = $this->getStreamTypes( $file );
		if ( !$streamTypes ) {
			return parent::getShortDesc( $file );
		}
		return wfMessage( 'timedmedia-mp4-short-video', implode( '/', $streamTypes )
		)->timeperiodParams(
			$this->getLength( $file )
		)->text();
	}

	/**
	 * @param $file File
	 * @return String
	 */
	function getLongDesc( $file ) {
		$streamTypes = $this->getStreamTypes( $file );
		if ( !$streamTypes ) {
			return parent::getLongDesc( $file );
		}
		return wfMessage('timedmedia-mp4-long-video',
			implode( '/', $streamTypes )
		)->timeperiodParams(
			$this->getLength( $file )
		)->bitrateParams(
			$this->getBitRate( $file )
		)->numParams(
			$file->getWidth(),
			$file->getHeight()
		)->text();

	}

}
