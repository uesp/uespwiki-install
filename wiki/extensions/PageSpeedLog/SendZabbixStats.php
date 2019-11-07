<?php

require_once("/home/uesp/www/w/extensions/PageSpeedLog/ComputePageSpeedStats.class.php");

$PAGESPEED_KEY = "wikipagespeed";
$ZABBIX_SERVER = "10.7.143.20";
$ZABBIX_HOST = gethostname();
$ZABBIX_LOGFILE = "/var/log/zabbix/pagespeed.log";


function send_zabbix_keys($compute)
{
	global $ZABBIX_LOGFILE;
	
	if ($compute->speedDataCount <= 0) 
	{
		echo "0";
		return;
	}
	
	if (file_exists($ZABBIX_LOGFILE)) unlink($ZABBIX_LOGFILE);
	
	zabbix_send("NumDataPoints", $compute->speedDataCount);
	zabbix_send("MinLoadTime", $compute->minSpeed);
	zabbix_send("MaxLoadTime", $compute->maxSpeed);
	zabbix_send("AverageLoadTime", $compute->avgSpeed);
	zabbix_send("StdLoadTime", $compute->stdSpeed);
	zabbix_send("90LoadTime", $compute->stdSpeed90);
	
	echo "6";
}


function zabbix_send ($var, $val) 
{
	global $ZABBIX_SERVER, $ZABBIX_HOST, $ZABBIX_LOGFILE, $PAGESPEED_KEY;

	if ( !is_numeric($val) ) $val = '"'.$val.'"';

	file_put_contents($ZABBIX_LOGFILE, "$ZABBIX_SERVER $ZABBIX_HOST 10051 $PAGESPEED_KEY.$var $val\n", FILE_APPEND);
	$cmd = "/usr/local/bin/zabbix_sender -z $ZABBIX_SERVER -p 10051 -s $ZABBIX_HOST -k $PAGESPEED_KEY.$var -o $val";

	system("$cmd 2>&1 >> " . $ZABBIX_LOGFILE);
}


$compute = new ComputePageSpeedStats();
$compute->echo = false;
$compute->Parse();

send_zabbix_keys($compute);