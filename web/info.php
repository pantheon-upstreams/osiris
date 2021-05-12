<?php

if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && $_SERVER['PANTHEON_ENVIRONMENT'] ===  "dev") {
  header("location: /dashboard.php");
  exit();
}

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::factory();



