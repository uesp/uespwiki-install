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
	$wgDBname = "uesp_net_wikidev";
	
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
				'load' => 0,
				'max lag' => 10,
		),
/* Comment out to prevent issue with slave lag reading
		array(          # content3 - Backup Read
				'host' => $UESP_SERVER_CONTENT3,
				'dbname' => $uespWikiDBName,
				'user' => $uespWikiUser,
				'password' => $uespWikiPW,
				'type' => "mysql",
				'flag' => DBO_DEFAULT,
				'load' => 0,
		), */
	);
}

# Special settings for translation wikis
if ($uespLanguageSuffix != "")
{
	$wgSharedDB = $uespWikiDB;
	$wgSharedPrefix = '';
	$wgSharedTables[] = 'ipblocks';
	$wgSharedTables[] = 'interwiki';
}

# $wgMasterWaitTimeout = 6000;
