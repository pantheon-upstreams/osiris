<?php
/**
 * @phpcs:ignoreFile
 *
 * Parse a PHPInfo Response.
 * @warning    THIS CLASS MUST WORK ON EVERY VERSION OF PHP WE CURRENTLY SUPPORT
 * @author     stovak <stovak@gmail.com>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL v2
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace Pantheon\Osiris;

/**
 * Class PHPInfo
 * @package Pantheon\Osiris
 */
class PHPInfo extends \DOMDocument
{

    public static $CIRCLE_CHECK = "data:image/svg+xml;base64,PHN2ZyBhcmlhLWhpZGRlbj0idHJ1ZSIgZm9jdXNhYmxlPSJmYWxzZSIgZGF0YS1wcmVmaXg9ImZhcyIgZGF0YS1pY29uPSJjaGVjay1jaXJjbGUiIGNsYXNzPSJzdmctaW5saW5lLS1mYSBmYS1jaGVjay1jaXJjbGUgZmEtdy0xNiIgcm9sZT0iaW1nIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik01MDQgMjU2YzAgMTM2Ljk2Ny0xMTEuMDMzIDI0OC0yNDggMjQ4UzggMzkyLjk2NyA4IDI1NiAxMTkuMDMzIDggMjU2IDhzMjQ4IDExMS4wMzMgMjQ4IDI0OHpNMjI3LjMxNCAzODcuMzE0bDE4NC0xODRjNi4yNDgtNi4yNDggNi4yNDgtMTYuMzc5IDAtMjIuNjI3bC0yMi42MjctMjIuNjI3Yy02LjI0OC02LjI0OS0xNi4zNzktNi4yNDktMjIuNjI4IDBMMjE2IDMwOC4xMThsLTcwLjA1OS03MC4wNTljLTYuMjQ4LTYuMjQ4LTE2LjM3OS02LjI0OC0yMi42MjggMGwtMjIuNjI3IDIyLjYyN2MtNi4yNDggNi4yNDgtNi4yNDggMTYuMzc5IDAgMjIuNjI3bDEwNCAxMDRjNi4yNDkgNi4yNDkgMTYuMzc5IDYuMjQ5IDIyLjYyOC4wMDF6Ij48L3BhdGg+PC9zdmc+";

  /**
   * PHPInfo constructor.
   */
    public function __construct()
    {
        ob_start();
        phpinfo(INFO_GENERAL | INFO_CREDITS | INFO_MODULES | INFO_LICENSE);
        $phpinfo = ob_get_contents();
        ob_end_clean();
        @$this->loadHTML($phpinfo);
        $this->normalizeDocument();
    }

    public function getModuleList()
    {
        $modules = get_loaded_extensions();
        $toReturn = array_combine($modules, array_fill(0, count($modules), sprintf('<img width="15" height="15" src="%s" alt="✅" />', static::$CIRCLE_CHECK)));
        $toReturn['Core'] = PHP_VERSION;
        $toReturn['gd'] = static::execInclude("gd.php");
        $toReturn['imagick'] = static::execInclude("imagick.php");

        return $toReturn;
    }


  /**
   * @return void
   */
    public function __toArray()
    {
        $toReturn = array();
        $section = "extra";
        $sibling = $this->getElementsByTagName('h2')->item(0);
        while ($sibling != null) {
            switch (get_class($sibling)) {
                case "DOMElement":
                    switch ($sibling->tagName) {
                        case "h2":
                            $section = strtolower($sibling->textContent);
                            if (!isset($toReturn[$section])) {
                                  $toReturn[$section] = array();
                            }
                            break;
                        case "table":
                            $rows = $sibling->getElementsByTagName("tr");
                            for ($rowNum=0; $rowNum <= $rows->length; $rowNum++) {
                                $row = $rows->item($rowNum);
                                if ($row instanceof \DOMElement
                                && $cells = $row->getElementsByTagName('td')) {
                                        $cellValue = array();
                                    if ($cells instanceof \DOMNodeList) {
                                        for ($i = 0; $i <= $cells->length; $i++) {
                                            $value = @trim(strip_tags(strtolower($cells->item($i)->textContent)));
                                            if ($value == "on" || $value == "enabled") {
                                                $cellValue[] = true;
                                            } elseif ($value == "off" || $value == "disabled") {
                                                $cellValue[] = false;
                                            } elseif (!empty($value)) {
                                                $cellValue[] = mb_convert_encoding($value, "UTF-8", "UTF-8");
                                            }
                                        }
                                    }
                                    if (is_array($cellValue) && count($cellValue)) {
                                        $first = array_shift($cellValue);
                                        if (strlen($first) > 15) {
                                            array_unshift($cellValue, $first);
                                            $first = 'credits';
                                        }
                                        $toReturn[$section][$first] = $cellValue;
                                    }
                                }
                            }
                    }
            }
            $sibling = $sibling->nextSibling;
        }
        return $toReturn;
    }

    public function display()
    {
        $entries = explode(",", $_SERVER['HTTP_ACCEPT']);
        foreach ($entries as $entry) {
            [ $noparams ]  = explode(";", $entry);
            [$type, $format] = explode("/", strtolower($noparams));
            switch ($format) {
                case "html":
                    header("Content-Type: text/html");
                    static::getCacheControl();
                    phpinfo(INFO_GENERAL | INFO_CREDITS | INFO_MODULES | INFO_LICENSE);
                    exit();


                case "json":
                    header("Content-Type: application/json");
                    $info = new \Pantheon\Osiris\PHPInfo();
                    $toReturn['modules'] = $info->getModuleList();
                    $pieces = explode(".", PHP_VERSION);
                    $toReturn['version'] = $_GET['version'] ?? "v".$pieces[0].$pieces[1];
                    $encoded = json_encode($toReturn, JSON_UNESCAPED_UNICODE && JSON_UNESCAPED_SLASHES && JSON_UNESCAPED_LINE_TERMINATORS);
                    if (json_last_error()) {
                        print_r(json_last_error_msg());
                        exit();
                    }
                    echo $encoded;
                    exit();
                break;

                case "yaml":
                    if (extension_loaded('yaml')) {
                        header("Content-Type: text/yaml");
                        $info = new \Pantheon\Osiris\PHPInfo();
                        yaml_emit($info->__toArray());
                    }

                default:
            }
        }
        throw new \Exception("No response for this Accept Criteria.");
    }

    public static function factory()
    {
        static::getCacheControl();
        $p = new static();
        $p->display();
    }

  /**
   * Add cache-control headers to HTML response.
   */
    public static function getCacheControl()
    {
        header("Cache-Control: max-age=-1");
        header("Cache-Control: no-cache");
        header("Cache-Control: no-store");
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Request-Method: GET');
    }

    public static function execInclude($path)
    {
        ob_start();
        require($_SERVER['DOCUMENT_ROOT'] . "/" . $path);
        $included = ob_get_contents();
        ob_end_clean();
        return $included;
    }
}
