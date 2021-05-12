<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");


\Pantheon\Osiris\PHPInfo::getCacheControl();

$URL_PREFIX = "https://" . $_SERVER['HTTP_HOST'] ;

$cacheBuster = "uniq=" . uniqid();

?>
<div>
  <img src="<?php echo $URL_PREFIX; ?>/images/flowers.png?<?php echo $cacheBuster; ?>"/>
</div>
<div>
  <img src="<?php echo $URL_PREFIX; ?>/rotated?<?php echo $cacheBuster; ?>">
</div>
