<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");


\Pantheon\Osiris\PHPInfo::getCacheControl();

// Die nicely if we cannot go on.
if (!function_exists('sodium_crypto_secretbox')) {
  print 'Sodium not avaiable on this version of PHP.';
  exit(0);
}

// Get the input value from the POST variable. If empty, supply
// a default value to encrypt.
$source = "The knowledgeable fox quickly encrypted the jumbled vizors.";
if (isset($_POST['source'])) {
  $source = $_POST['source'];
}

$title = 'Result';
$result_text = '';

$key = isset($_POST['key']) ? $_POST['key'] : 'secretsecret';

// Clean up the input
$escaped_source = htmlspecialchars($source);
$escaped_key = htmlspecialchars($key);

try
{
  if (isset($_POST['decrypt'])) {
    $result_text = safeDecrypt($source, padKey($key));
  } else {
    $result_text = safeEncrypt($source, padKey($key));
  }
}
catch (\Exception $e) {
  $title = 'Error';
  $result_text = $e->getMessage();
}

$escaped_result_text = htmlspecialchars($result_text);

/**
 * This does not guarentee a good key. You should use
 * sodium_​crypto_​secretbox_​keygen in actual cryptographic applications.
 */
function padKey($key)
{
  $repeated_key = str_repeat($key, (SODIUM_CRYPTO_SECRETBOX_KEYBYTES / strlen($key)) + 2);
  return substr($repeated_key, 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
}

/**
 * Encrypt a message
 *
 * @param string $message - message to encrypt
 * @param string $key - encryption key
 * @return string
 */
function safeEncrypt($message, $key)
{
    $nonce = random_bytes(
        SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    );

    $cipher = base64_encode(
        $nonce.
        sodium_crypto_secretbox(
            $message,
            $nonce,
            $key
        )
    );
    sodium_memzero($message);
    sodium_memzero($key);
    return $cipher;
}

/**
 * Decrypt a message
 *
 * @param string $encrypted - message encrypted with safeEncrypt()
 * @param string $key - encryption key
 * @return string
 */
function safeDecrypt($encrypted, $key)
{
    $decoded = base64_decode($encrypted);
    if ($decoded === false) {
        throw new Exception('The provided encrypted text was not base64 encoded.');
    }
    if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
        throw new Exception('The encrypted message was too short; perhaps it was truncated?');
    }
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

    $plain = sodium_crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $key
    );
    if ($plain === false) {
         throw new Exception('The message was tampered with in transit, or an incorrect key was provided.');
    }
    sodium_memzero($ciphertext);
    sodium_memzero($key);
    return $plain;
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

<form action="sodium.php" method="post">
  <h3>Source</h3>
  <p>
    <textarea name="source" rows="20" cols="80"><?=$escaped_source?></textarea>
  </p>
  <h3>Key</h3>
  <p>
    <textarea name="key" rows="2" cols="80"><?=$escaped_key?></textarea>
  </p>
  <p>
    <input type="submit" name="encrypt" value="Encrypt"/>
    <input type="submit" name="decrypt" value="Decrypt"/>
  </p>
</form>

<h3><?=$title?></h3>

<?=$escaped_result_text?>
