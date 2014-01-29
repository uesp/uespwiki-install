<?php

/**
 * Command-line script for MetaTemplate to be run at installation time
 *
 * This script takes care of creating the new database tables.
 * However, now that LoadExtensionSchemaUpdate hook has been implemented, this script
 * is basically obsolete -- all database updates can be done via maintenance/update.php
**/

// check that script is being run from command line
$maint = dirname( dirname( __FILE__ ) ) . '/maintenance';
if( is_file( $maint . '/commandLine.inc' ) ) {
	require_once( $maint . '/commandLine.inc' );
} else {
	$maint = dirname( dirname( dirname( __FILE__ ) ) ) . '/maintenance';
	if( is_file( $maint . '/commandLine.inc' ) ) {
		require_once( $maint . '/commandLine.inc' );
	} else {
		# We can't find it, give up
		echo( "The installation script was unable to find the maintenance directories.\n\n" );
		die( 1 );
	}
}

# Whine if we don't have appropriate credentials to hand
if( !isset( $wgDBadminuser ) || !isset( $wgDBadminpassword ) ) {
	echo( "No superuser credentials could be found. Please provide the details\n" );
	echo( "of a user with appropriate permissions to update the database. See\n" );
	echo( "AdminSettings.sample for more details.\n\n" );
	die( 1 );
}

# Get a connection
$dbclass = 'Database' . ucfirst( $wgDBtype ) ;
$dbc = new $dbclass;
$dba =& $dbc->newFromParams( $wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname, 1 );

# Check we're connected
if( !$dba->isOpen() ) {
	echo( "A connection to the database could not be established.\n\n" );
	die( 1 );
}

if( !$dba->tableExists( 'mt_save_data' ) || !$dba->tableExists( 'mt_save_set' ) ) {
	if( $dba->sourceFile( 'install.sql') ) {
		echo( "The tables have been set up correctly.\n" );
	}
# Do nothing if the table exists
} else {
	echo( "The table already exists. No action was taken.\n" );
}

# Close the connection
$dba->close();
echo( "\n" );

?>