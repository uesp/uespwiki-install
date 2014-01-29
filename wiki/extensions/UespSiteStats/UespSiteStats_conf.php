<?php

	$this->m_UespSiteStats = array(

		"memory" => array(
			"content1",
			"content2",
			"content3",
			"squid1",
			"db1"
		),

		"diskusage" => array(
			"content1",
			"content2",
			"content3",
			"squid1",
			"db1"
		),

		"uptime" => array(
			"content1",
			"content2",
			"content3",
			"squid1",
			"db1"
		),
	
		"ifconfig" => array (
			"content1",
			"content2",
			"content3",
			"squid1",
			"db1"
		),

		"masterdbstatus" => array(
			array("host" => "db1", "user" => "slaveinfo")
		),

		"slavedbstatus" => array(
			array("host" => "content3", "user" => "slaveinfo"),
			array("host" => "backup1",  "user" => "slaveinfo")
		)

	);

?>
