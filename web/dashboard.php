<?php

require_once("../vendor/autoload.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

$p = new Pantheon\Osiris\DashboardPage();

$p->display();
