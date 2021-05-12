<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

$data = '$_SERVER:' . "\n" . var_export($_SERVER, true) . "\n\n\$_ENV:\n" . var_export($_ENV, true) . "\n";
$data = str_replace($_ENV['DB_PASSWORD'], '[[REDACTED]]', $data);
$data = str_replace($_ENV['DRUPAL_HASH_SALT'], '[[REDACTED]]', $data);
$data = str_replace($_ENV['CACHE_PASSWORD'], '[[REDACTED]]', $data);
$data = preg_replace('#(drupal_hash_salt|_password)":[^,]*,#', '\1":"[[REDACTED]]",', $data);
$data = preg_replace('#\b[0-9]{1,3}(\.[0-9]{1,3}){3}\b#', '[[REDACTED]]', $data);
$data = preg_replace('#\b[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}\b#', '[[REDACTED]]', $data);

header("Content-Type: text/plain");
print $data;
