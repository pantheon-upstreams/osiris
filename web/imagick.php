<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

$URL_PREFIX = "https://" . $_SERVER['HTTP_HOST'];

$bg = userSelectedImage('bg', 'sunrise.png');
$bg_file = $URL_PREFIX . '/images/' . $bg;

$menu = '';
$selections = array(
  'sunrise.png' => 'Sunrise',
  'snow.png' => 'Snow',
  'flowers.png' => 'Flowers',
  'lava.jpg' => 'Lava',
);

foreach ($selections as $selection => $description) {
  $checked = ($bg == $selection) ? 'checked' : '';
  $menu .= "<input type='radio' name='bg' value='$selection' $checked> $description<br>";
}

function userSelectedImage($key, $default)
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    $key = preg_replace('#[^a-z0-9_.-]#', '', $_POST[$key]);
    $user_selected = __DIR__ . '/images/' . $key;

    if (!file_exists($user_selected)) {
        return $default;
    }

    return $key;
}

?>
<style type="text/css">
  img {
    border: 3px solid black;
    margin: 8px;
    vertical-align: middle;
  }

  #images {
    font-size: 48pt;
    font-weight: bold;
  }
</style>
<div id="images">
  <div>
    <img src="<?php echo $bg_file; ?>" alt="background"/>
  </div>
  +
  <div>
    <img src="<?php echo $URL_PREFIX; ?>/images/pantheon.png" alt="pantheon"/>
  </div>
  =
  <div>
    <img src="<?php echo $URL_PREFIX; ?>/composite.php?bg=<?=$bg?>" alt="composite"/>
  </div>
</div>

<h1>To Do: test PDF generation</h1>
