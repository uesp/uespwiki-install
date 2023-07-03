<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains profiler related settings and should only be included if you wish
# to profile a wiki.
# It is included by LocalSettings.php.
#

$wgProfiler = [
	'class' => 'ProfilerXhprof',
	'output' => 'text',
];

$wgDebugLogFile = "/tmp/profile.log";

