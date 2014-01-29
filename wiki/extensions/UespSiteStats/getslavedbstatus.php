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

   $user=$_GET['user'];

   if ( !preg_match("/[a-zA-Z0-9]+/", $user) )
   {
      exit;
   }

   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
   header("Cache-Control: private, must-revalidate, no-cache");
   header("Pragma: no-cache");
   header("content-type:text/html");

   $cmd="mysql -h $host -u $user -Bse 'show slave status\G' 2>&1";
   $result=`$cmd`;

   echo $result;
?>
