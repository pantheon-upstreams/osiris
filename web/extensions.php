<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

// n.b. PHP 5.3 not tested. It is what it is. Sorry!

// A list of optional extensions. If the entry for the
// extension contains a list of php versions, then it is
// REQUIRED only for those versions, and optional for all others.
// Extensions listed here must also be listed in one of the
// other extension-test lists below.
$optional_extensions = [
    'ionCube Loader' => ['7.1'],
    'sqlsrv' => ['7.2'],
    'mcrypt' => ['5.5', '5.6', '7.0', '7.1'],
    'pgsql' => [],
    'intl' => ['5.6', '7.1', '7.2', '7.3'],
    'tidy' => ['5.6', '7.0', '7.1', '7.2', '7.3'],
    'sodium' => ['7.3'],
    'mongodb' => ['7.3'],
    'wddx' => ['7.2', '7.3'], // Removed in 7.4.0
    'png' => ['7.2', '7.3'], // Standard in 7.4.0
];

// PHP extensions dynamically linked in Pantheon
$pantheon_dynamic_extensions = [
    'ionCube Loader' => true,
    'newrelic' => true,
];

// Additional PHP extensions downloaded by Pantheon and
// compiled into PHP RPM
$pantheon_additional_extensions = [
    'apc-bc' => ['function_exists', 'apc_fetch'],
    'apcu' => true,
    'imagick' => true,
    'oauth' => true,
    'redis' => true,
    'sqlsrv' => true,
];

// Standard PHP extensions compiled by Pantheon
$pantheon_standard_extensions = [
    'bcmath' => true,
    'bz2' => true,
    'calendar' => true,
    'curl' => true,
    'dom' => true,
    'exif' => true,
    'fileinfo' => true,
    'freetype' => ['function_exists', 'imageloadfont'],
    'ftp' => true,
    'gd' => true,
    'gettext' => true,
    'gmp' => true,
    'iconv' => true,
    'imap' => true,
// TODO: need a way to detect
//    'imap-ssl' => ['check_phpinfo', 'imap', 'SSL Support => enabled'],
    'intl' => true,
// TODO: need a way to detect
//    'jpeg' => 'imagejpeg',
    'json' => true,
// TODO: need a way to detect
//    'kerberos' => ['check_phpinfo', 'curl', 'KERBEROS5 => Yes'],
    'ldap' => true,
    'mbstring' => true,
    'mcrypt' => ['function_exists', 'mcrypt_encrypt'],
    'mhash' => ['function_exists', 'mhash_count'],
    'mongodb' => true,
    'mysqli' => true,
    'opcache' => ['function_exists', 'opcache_get_status'],
    'openssl' => true,
    'pgsql' => true,
    'phar' => true,
// What is the 'pic' extension?
//    'pic' => true,
    'png' => ['function_exists', 'imagepng'],
    'shmop' => true,
    'soap' => true,
    'sockets' => true,
    'sodium' => true,
    'sqlite' => ['method_exists', 'SQLite3', 'open'],
// removed in php 7.0
//    't1lib' => true,
    'tidy' => true,
    'wddx' => true,
    'xml' => true,
    'xmlreader' => true,
    'xmlwriter' => true,
    'xpm' => ['function_exists', 'imagecreatefromxpm'],
    'xsl' => true,
    'zip' => true,
    'zlib' => true,
];

// Extensions that php turns on implicitly
// (or perhaps along with some other extension)
$php_implicit_extensions = [
    'Core' => true,
    'date' => true,
    'libxml' => true,
    'pcre' => true,
    'ctype' => true,
    'hash' => true,
    'filter' => true,
    'SPL' => true,
    'session' => true,
    'standard' => true,
    'mysqlnd' => true,
    'odbc' => true,
    'PDO' => true,
    'pdo_mysql' => true,
    'pdo_pgsql' => true,
    'pdo_sqlite' => true,
    'Reflection' => true,
    'SimpleXML' => true,
    'tokenizer' => true,
    'cgi-fcgi' => true,
    'Zend OPcache' => true,
];

// Print a header
format_output(
    "\nExtensions for PHP {version}:\n",
    "<h1>Extensions for PHP {version}</h1>\n",
    ['{version}' => PHP_VERSION]
);

report_extension_loaded_status('Dynamic Extensions', $pantheon_dynamic_extensions, $optional_extensions);
report_extension_loaded_status('Additional Extensions', $pantheon_additional_extensions, $optional_extensions);
report_extension_loaded_status('Standard Extensions', $pantheon_standard_extensions, $optional_extensions);
report_extension_loaded_status('Implicit Extensions', $php_implicit_extensions, $optional_extensions);

/*
// Debug
print "<pre>";
var_export(get_loaded_extensions());
print "\n<\pre>";
*/

function format_output($cli_tmpl, $html_tmpl, $context = [])
{
    if (substr(php_sapi_name(), 0, 3) == 'cli') {
        $tmpl = $cli_tmpl;
    }
    else {
        $tmpl = $html_tmpl;
    }

    print str_replace(array_keys($context), array_values($context), $tmpl);
}

// This works for php-cli, but when running from the web server,
// phpinfo() produces html output, so the regexps would have to
// be different.
function check_phpinfo($ext, $test_string)
{
    ob_start();
    phpinfo(INFO_GENERAL | INFO_CREDITS | INFO_MODULES | INFO_LICENSE);
    $php_info_data = ob_get_clean();

    // If the php info data does not include a section on the extension, exit.
    if (!preg_match("#.*^\s*$\s*^{$ext}$[\\s]*#ms", $php_info_data)) {
        return false;
    }

    // Strip out everything from php info data except the section for
    // the extension we are interested in.
    $php_info_data = preg_replace("#.*^\s*$\s*^{$ext}$[\\s]*#ms", '', $php_info_data);
    $php_info_data = preg_replace('#^\s*$.*#ms', '', $php_info_data);

/*
    format_output(
        "\nphp info for {ext}:\n\n{data}\n",
        "  <tr><td></td><td>php info for {ext}:<br><br>{data}</td></tr>\n",
        ['{ext}' => $ext, '{data}' => htmlspecialchars($php_info_data)]
    );
*/

    // Check to see if our section contain the test string.
    return strpos($php_info_data, $test_string) !== false;
}

/**
 * @return bool|null
 *   true - extension is available
 *   false - extension is required and missing
 *   null - extension is optional and missing
 */
function check_extension($ext, $test, $optional_extensions)
{
    $result = do_check_extension($ext, $test);
    if (!$result && array_key_exists($ext, $optional_extensions)) {
        $version = preg_replace('#\.[^.]*$#', '', PHP_VERSION);
        $required_versions = $optional_extensions[$ext];
        if (!in_array($version, $required_versions)) {
            return null;
        }
    }
    return $result;
}

function do_check_extension($ext, $test)
{
    if (is_array($test)) {
        $fn = array_shift($test);
        return call_user_func_array($fn, $test);
    }
    return extension_loaded($ext);
}

function extension_status_mark($ext, $status)
{
    if ($status === null) {
        return '➖';
    }
    return $status ? "✅": "❌";
}

function extension_test_url($ext)
{
    $ext_test_url = "$ext.php";
    if (file_exists(__DIR__ . "/$ext_test_url")) {
        return $ext_test_url;
    }
}

function report_extension_loaded_status($title, $extension_list, $optional_extensions)
{
    $html_tmpl = <<< __EOT__
<h3>{title}</h3>

<table>
  <tr>
    <th>Status</th>
    <th>Extension Name</th>
  </tr>

__EOT__;

    format_output(
        "\n{title}\n{div}\n\n",
        $html_tmpl,
        ['{title}' => $title, '{div}' => str_pad('', strlen($title), '=')]
    );

    foreach($extension_list as $ext => $test_fn) {
        $status = check_extension($ext, $test_fn, $optional_extensions);
        $mark = extension_status_mark($ext, $status);
        $url = extension_test_url($ext);
        $linked_ext = $ext;
        if ($url) {
            $linked_ext = "<a href='$url'>$ext</a>";
        }
        format_output(
            "{mark} {ext}\n",
            "  <tr><td>{mark}</td><td>{linked-ext}</td></tr>\n",
            ['{mark}' => $mark, '{ext}' => $ext, '{linked-ext}' => $linked_ext]
        );
    }

    format_output(
        '',
        "</table>\n"
    );
}
