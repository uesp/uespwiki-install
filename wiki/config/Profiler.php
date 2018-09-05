<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains profiler related settings and should only be included if you wish
# to profile a wiki.
# It is included by LocalSettings.php.
#

 $wgProfiler['class'] = 'Profiler';
 $wgDebugLogFile = "/tmp/profile.log";
 $wgProfileLimit = 0;

 // Don't put non-profiling info into log file
 $wgProfileOnly = false;

 // Log sums from profiling into "profiling" table in db
 $wgProfileToDatabase = false;

 // If true, print a raw call tree instead of per-function report
 $wgProfileCallTree = false;

 // Should application server host be put into profiling table
 $wgProfilePerHost = false;

 // Settings for UDP profiler
 $wgUDPProfilerHost = '127.0.0.1';
 $wgUDPProfilerPort = '3811';

 // Detects non-matching wfProfileIn/wfProfileOut calls
 $wgDebugProfiling = false;

 // Output debug message on every wfProfileIn/wfProfileOut
 $wgDebugFunctionEntry = 0;

 // Lots of debugging output from SquidUpdate.php
 $wgDebugSquid = false;

