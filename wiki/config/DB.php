<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains database related configuration.
# It is included by LocalSettings.php.
#

$wgDBname = "uesp_net_wiki5" . $uespLanguageSuffix;

$wgDBprefix = "";
$wgDBmysql5 = false;
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

$uespWikiDBName = $uespWikiDB . $uespLanguageSuffix;

if ($uespIsDev)
{
	$wgDBname = "uesp_net_wikidev" . $uespLanguageSuffix;
	$uespWikiDBName = $wgDBname;
	
	$wgDBservers = array(
		array(          # content3 is the only dev wiki database
				'host' => $UESP_SERVER_BACKUP1,
				'dbname' => $uespWikiDBName,
				'user' => $uespWikiUser,
				'password' => $uespWikiPW,
				'type' => "mysql",
				'flag' => DBO_DEFAULT,
				'load' => 1,
			),
		);
}
else 
{
	$wgDBservers = array(
		array(          # db1 - Write Master
				'host' => $UESP_SERVER_DB1,
				'dbname' => $uespWikiDBName,
				'user' => $uespWikiUser,
				'password' => $uespWikiPW,
				'type' => "mysql",
				'flag' => DBO_DEFAULT,
				'load' => 1, 
		),
		array(          # db2 - Primary Read
				'host' => $UESP_SERVER_DB2,
				'dbname' => $uespWikiDBName,
				'user' => $uespWikiUser,
				'password' => $uespWikiPW,
				'type' => "mysql",
				'flag' => DBO_DEFAULT,
				'load' => 10,
				'max lag' => 1000,
		)
	);
	
		// Special case where we want to disable the read DB server
	if ($UESP_SERVER_DBWRITE == $UESP_SERVER_DBREAD)
	{
		unset($wgDBservers[1]);
	}
	
			/* Don't include by default as backup lag can affect production servers */
	$uespBackup1Db = 
		array(          # backup1 - Backup Read
				'host' => $UESP_SERVER_BACKUP1,
				'dbname' => $uespWikiDBName,
				'user' => $uespWikiUser,
				'password' => $uespWikiPW,
				'type' => "mysql",
				'flag' => DBO_DEFAULT,
				'load' => 10,
		);
	
	# Special settings for translation wikis
	$wgSharedDB = $uespWikiDB;
	$wgSharedPrefix = '';
	//$wgSharedTables[] = "actor";		//Enable for 1.31 
	$wgSharedTables[] = 'ipblocks';
	$wgSharedTables[] = 'interwiki';
}

/* Always use backup1 as the primary read DB on backup1 scripts */
if ($uespIsBackup1)
{
	$wgDBservers[0]['load'] = 0;
	$wgDBservers[1] = $uespBackup1Db;
}

if ($uespLanguageSuffix != "")
{
	$wgLocalDatabases[] = $uespWikiDB;
}

# $wgMasterWaitTimeout = 6000;
