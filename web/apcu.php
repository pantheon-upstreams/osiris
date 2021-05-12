<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");


\Pantheon\Osiris\PHPInfo::getCacheControl();

define('USE_AUTHENTICATION',0);
$apc_path = dirname(dirname(__DIR__)) . '/php/apc.php';

if (!file_exists($apc_path)) {
  print "Content-Type: text/plain\n\n";
  print "APC tool not available.";
  exit(0);
}

include $apc_path;
