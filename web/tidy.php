<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

// Get the input value from the POST variable. If empty, supply
// a default value to tidy.
$source = "Tidy example.\n\n<ul>\n  <li>One\n  <li>Two\n</ul>";
if (isset($_POST['tidy'])) {
  $source = $_POST['tidy'];
}

// Clean up the input
$escaped_source = htmlspecialchars($source);

$tidy_output = do_tidy($source);
$escaped_tidy_output = htmlspecialchars($tidy_output);

function do_tidy($source) {
  if (!class_exists('tidy')) {
    return "Tidy extension not available\n\n$source";
  }

  $config = array(
    'indent'         => true,
    'output-xhtml'   => true,
    'wrap'           => 200
  );

  $tidy = new tidy;
  $tidy->parseString($source, $config, 'utf8');
  $tidy->cleanRepair();

  return $tidy;
}


?>

<head>
  <style>
    pre {
      border: 1px solid black;
      width: 400px;
      padding: 8px;
    }
    textarea {
      width: 400px;
      padding: 8px;
    }
  </style>
</head>

<form action="tidy.php" method="post">
  <p>
    <textarea name="tidy" rows="20" cols="80">
<?=$escaped_source?>
    </textarea>
  </p>
  <p>
    <input type="submit" value="Update"/>
  </p>
</form>

<br/>

<p>
  <pre>
<?=$escaped_tidy_output?>
  </pre>
</p>
