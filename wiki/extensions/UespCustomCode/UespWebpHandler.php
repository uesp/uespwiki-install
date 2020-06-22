<?php
/*
 * UespWebpHandler.php -- Dave Humphrey (dave@uesp.net), April 2020
 * 
 * Very simple handler for WEBP images that supports resizing to WEBP format (instead of PNT) as well as basic 
 * resizing of animated images. Note that resizing animated WEBPs can result in imperfect replicas.
 * 
 * Uses the Google shell applications available in the webp-libtools package or at:
 * 		https://developers.google.com/speed/webp/download
 * 
 * Specifically created for the UESP.net wiki but can be customized using the $wgUespWebpBinPath and
 * $wgUespWebpCustomConvert global variables.
 */


global $IP;
require_once "$IP/includes/media/WebP.php";

global $wgUespWebpBinPath;
$wgUespWebpBinPath = '/usr/bin';
$wgUespWebpBinPath = '/home/uesp/webp/bin';

global $wgUespWebpCustomConvert;
$wgUespWebpCustomConvert = 'cwebp -resize %w %h %s -o %d';


class UespWebpHandler extends WebPHandler 
{
	
	public function canRender( $file ) {
		return true;
	}
	
	
	public function canAnimateThumbnail( $file ) {
		return true;
	}
	
	
	public function getThumbType( $ext, $mime, $params = null ) {
		return array( 'webp', 'image/webp' );
	}
	
	
	protected function getScalerType( $dstPath, $checkDstPath = true ) {
		return 'custom';
	}
	
	
	protected function transformCustom( $image, $params ) {
		global $wgUespWebpCustomConvert;
		global $wgUespWebpBinPath;
	
		if ($this->isAnimatedImage($image)) return $this->transformCustomAnimated($image, $params);
	
		# Variables: %s %d %w %h
		$src = wfEscapeShellArg( $params['srcPath'] );
		$dst = wfEscapeShellArg( $params['dstPath'] );
		$cmd = $wgUespWebpBinPath . "/" . $wgUespWebpCustomConvert;
		$cmd = str_replace( '%s', $src, str_replace( '%d', $dst, $cmd ) ); # Filenames
		$cmd = str_replace( '%h', wfEscapeShellArg( $params['physicalHeight'] ),
			str_replace( '%w', wfEscapeShellArg( $params['physicalWidth'] ), $cmd ) ); # Size
		wfDebug( __METHOD__ . ": Running custom convert command $cmd\n" );
		$retval = 0;
		$err = wfShellExecWithStderr( $cmd, $retval );

		if ( $retval !== 0 ) {
			$this->logErrorForExternalProcess( $retval, $err, $cmd );

			return $this->getMediaTransformError( $params, $err );
		}

		return false; # No error
	}
	
	
	protected function transformCustomAnimated( $image, $params ) {
		global $wgUespWebpCustomConvert;
		global $wgUespWebpBinPath;
		
		$src       = wfEscapeShellArg( $params['srcPath'] );
		$dst       = wfEscapeShellArg( $params['dstPath'] );
		$width     = wfEscapeShellArg( $params['physicalWidth'] );
		$height    = wfEscapeShellArg( $params['physicalHeight'] );
		$srcWidth  = $params['srcWidth'];
		$srcHeight = $params['srcHeight'];
		$pctWidth  = floatval($params['physicalWidth']) / floatval($srcWidth);
		$pctHeight = floatval($params['physicalHeight'])/ floatval($srcHeight);
				
		$cmd = "$wgUespWebpBinPath/webpmux -info $src";
		wfDebug( __METHOD__ . ": Running custom convert command $cmd\n" );
		
		$retval = 0;
		$output = wfShellExecWithStderr( $cmd, $retval );
		
		if ( $retval !== 0 ) {
			$this->logErrorForExternalProcess( $retval, $output, $cmd );
			return $this->getMediaTransformError( $params, $output );
		}
		
		$outputLines = explode("\n", $output);
		$frames = array();
		
		if (count($outputLines) <= 2) {
			$this->logErrorForExternalProcess( $retval, $output, $cmd );
			return $this->getMediaTransformError( $params, $output );
		}
		
		foreach ($outputLines as $line) {
			$frame = array();
			
			$cols = preg_split("# #", $line, -1, PREG_SPLIT_NO_EMPTY);
			if (count($cols) < 10) continue;
			
			$frame['no'] = $cols[0];
			$frame['width'] = $cols[1];
			$frame['height'] = $cols[2];
			$frame['alpha'] = $cols[3];
			$frame['xoffset'] = $cols[4];
			$frame['yoffset'] = $cols[5];
			$frame['duration'] = $cols[6];
			$frame['dispose'] = $cols[7];
			$frame['blend'] = $cols[8];
			$frame['size'] = $cols[9];
			$frame['compression'] = $cols[10];
			
			$frames[] = $frame;
		}
		
		if (count($frames) <= 1) {
			$this->logErrorForExternalProcess( $retval, $output, $cmd );
			return $this->getMediaTransformError( $params, $output );
		}
		
		$createCmd = "";
		
		for ($i = 1; $i < count($frames); ++$i)
		{
			$frame = &$frames[$i];
			
			$tmpFile = TempFSFile::factory( "anitransform_{$i}_", 'webp' );
			$tmpFile->bind($frames[$i]);
			$frames[$i]['tmpfile'] = $tmpFile;
			$tmpName = $tmpFile->getPath();
			
			$cmd = "$wgUespWebpBinPath/webpmux -get frame $i $src -o '$tmpName'";
			wfDebug( __METHOD__ . ": Running custom convert command $cmd\n" );
			
			$retval = 0;
			$output = wfShellExecWithStderr( $cmd, $retval );
			
			if ( $retval !== 0 ) {
				$this->logErrorForExternalProcess( $retval, $output, $cmd );
				return $this->getMediaTransformError( $params, $output );
			}
			
			$frameWidth  = round(floatval($frame['width']) * $pctWidth);
			$frameHeight = round(floatval($frame['height']) * $pctHeight);
			
			$cmd = "$wgUespWebpBinPath/cwebp -resize $frameWidth $frameHeight '$tmpName' -o '$tmpName'";
			wfDebug( __METHOD__ . ": Running custom convert command $cmd\n" );
			
			$retval = 0;
			$output = wfShellExecWithStderr( $cmd, $retval );
			
			if ( $retval !== 0 ) {
				$this->logErrorForExternalProcess( $retval, $output, $cmd );
				return $this->getMediaTransformError( $params, $output );
			}
			
			$di = $frame['duration'];
			$xi = round(floatval($frame['xoffset']) * $pctWidth);
			$yi = round(floatval($frame['yoffset']) * $pctHeight);
			$mi = $frame['dispose'] == "none" ? "0" : "1";
			$bi = $frame['blend'] == 'yes' ? '+b' : '-b';
			
			$createCmd .= " -frame '$tmpName' +$di+$xi+$yi+$mi$bi";
		}
		
		$cmd = "$wgUespWebpBinPath/webpmux $createCmd -o $dst";
		wfDebug( __METHOD__ . ": Running custom convert command $cmd\n" );
			
		$retval = 0;
		$output = wfShellExecWithStderr( $cmd, $retval );
		
		if ( $retval !== 0 ) {
			$this->logErrorForExternalProcess( $retval, $output, $cmd );
			return $this->getMediaTransformError( $params, $output );
		}		
		
		return false;	# No error
	}
	
};
