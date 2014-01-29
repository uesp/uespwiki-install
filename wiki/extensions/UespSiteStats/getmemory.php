<?php

   if ( !array_key_exists('host', $_GET) )
   {
      exit;
   }

   $host=$_GET['host'];

   if ( !preg_match("/[a-zA-Z0-9.]+/", $host) )
   {
      exit;
   }

   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
   header("Cache-Control: private, must-revalidate, no-cache");
   header("Pragma: no-cache");
   header("content-type:text/html");

   $cmd="ssh uespkey@$host 'free -m' 2>&1";
   $result=`$cmd`;

   echo $result;
?>
