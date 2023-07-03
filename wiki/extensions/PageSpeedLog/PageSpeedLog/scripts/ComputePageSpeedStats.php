<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("ComputePageSpeedStats.class.php");

$compute = new ComputePageSpeedStats();
$compute->echo = true;
$compute->Parse();



