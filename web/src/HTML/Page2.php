<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

namespace Pantheon\HTML;

/**
 * This class is responsibly sourced from the shards of what was the PEAR
 * repository. I got rid of all the "doctype" and "XHTML" nonsense.
 * Pages now render as HTML5. Look for a 2-line bootstrap page in this
 * directory. I regret nothing.
 *
 * @author Tom Stovall <tom.stovall@pantheon.io>
 * @date 2021-APR-09
 * @basedOn PEAR::HTML_Page2
 *
 * The PEAR::HTML_Page2 package provides a simple interface for generating
 * an XHTML compliant page
 *
 * Features:
 * - supports virtually all HTML doctypes, from HTML 2.0 through XHTML 1.1 and
 *   XHTML Basic 1.0 plus preliminary support for XHTML 2.0
 * - namespace support
 * - global language declaration for the document
 * - line ending styles
 * - full META tag support
 * - support for stylesheet declaration in the head section
 * - support for script declaration in the head section
 * - support for linked stylesheets and scripts
 * - full support for header <link> tags
 * - body can be a string, object with toHtml or toString methods or an array
 *   (can be combined)
 *
 * Ideas for use:
 * - Use to validate the output of a class for XHTML compliance
 * - Quick prototyping using PEAR packages is now a breeze
 *
 * PHP versions 4 and 5
 *
 * @category HTML
 * @package  HTML_Page2
 * @author   Adam Daniel <adaniel1@eesus.jnj.com>
 * @author   Klaus Guenther <klaus@capitalfocus.org>
 * @license  http://www.php.net/license/3_0.txt PHP License 3.0
 * @version  GIT: @Id@
 * @link     http://pear.php.net/package/HTML_Page2
 * @since    PHP 4.0.3pl1
 */

// +----------------------------------------------------------------------+
// | HTML_Page2                                                           |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2004 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id$


/**#@+
 * Determines how content is added to the body.
 *
 * Use with the @see addBodyContent method.
 *
 * @since      2.0.0
 */
define('HTML_PAGE2_APPEND', 0);
define('HTML_PAGE2_PREPEND', 1);
define('HTML_PAGE2_REPLACE', 2);
/**#@-*/

/**
 * (X)HTML Page generation class
 *
 * <p>This class handles the details for creating a properly constructed XHTML page.
 * Page caching, stylesheets, client side script, and Meta tags can be
 * managed using this class.</p>
 *
 * <p>The body may be a string, object, or array of objects or strings. Objects with
 * toHtml() and toString() methods are supported.</p>
 *
 * <p><b>XHTML Examples:</b></p>
 *
 * <p>Simplest example:</p>
 * <code>
 * // the default doctype is XHTML 1.0 Transitional
 * // All doctypes and defaults are set in HTML/Page/Doctypes.php
 * $p = new HTML_Page2();
 *
 * //add some content
 * $p->addBodyContent("<p>some text</p>");
 *
 * // print to browser
 * $p->display();
 * ?>
 * </code>
 *
 * <p>Complex XHTML example:</p>
 * <code>
 * <?php
 * // The array takes an array of attributes that determine many important
 * // aspects of the page generations.
 *
 * // Possible attributes are: charset, mime, lineend, tab, doctype, namespace,
 * // language and cache
 *
 * $p = new HTML_Page2(array (
 *
 *                // Sets the charset encoding (default: utf-8)
 *                'charset'  => 'utf-8',
 *
 *                // Sets the line end character (default: unix (\n))
 *                'lineend'  => 'unix',
 *
 *                // Sets the tab string for autoindent (default: tab (\t))
 *                'tab'  => '  ',
 *
 *                // This is where you define the doctype
 *                'doctype'  => "XHTML 1.0 Strict",
 *
 *                // Global page language setting
 *                'language' => 'en',
 *
 *                // If cache is set to true, the browser may cache the output.
 *                'cache'    => 'false'
 *                ));
 *
 * // Here we go
 *
 * // Set the page title
 * $p->setTitle("My page");
 *
 * // Add optional meta data
 * $p->setMetaData("author", "My Name");
 *
 * // Put something into the body
 * $p->addBodyContent("<p>some text</p>");
 *
 * // If at some point you want to clear the page content
 * // and output an error message, you can easily do that
 * // See the source for {@link toHtml} and {@link _getDoctype}
 * // for more details
 * if ($error) {
 *     $p->setTitle("Error!");
 *     $p->setBody("<p>Houston, we have a problem: $error</p>");
 *     $p->display();
 *     die;
 * } // end error handling
 *
 * // print to browser
 * $p->display();
 * // output to file
 * $p->toFile('example.html');
 * ?>
 * </code>
 *
 * Simple XHTML declaration example:
 * <code>
 * <?php
 * $p = new HTML_Page2();
 * // An XHTML compliant page (with title) is automatically generated
 *
 * // This overrides the XHTML 1.0 Transitional default
 * $p->setDoctype('XHTML 1.0 Strict');
 *
 * // Put some content in here
 * $p->addBodyContent("<p>some text</p>");
 *
 * // print to browser
 * $p->display();
 * ?>
 * </code>
 *
 * <p><b>HTML examples:</b></p>
 *
 * <p>HTML 4.01 example:</p>
 * <code>
 * <?php
 * $p = new HTML_Page2('doctype="HTML 4.01 Strict"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * ?>
 * </code>
 *
 * <p>nuke doctype declaration:</p>
 *
 * <code>
 * <?php
 * $p = new HTML_Page2('doctype="none"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * ?>
 * </code>
 *
 * @category HTML
 * @package  HTML_Page2
 * @author   Adam Daniel <adaniel1@eesus.jnj.com>
 * @author   Klaus Guenther <klaus@capitalfocus.org>
 * @license  http://www.php.net/license/3_0.txt PHP License 3.0
 * @version  Release: 2.0.0
 * @link     http://pear.php.net/package/HTML_Page2
 */
class Page2 extends Common
{

  /**
   * Contains an instance of {@see HTML_Page2_Frameset}
   *
   * @var   object
   * @since 2.0
   */
    public $frameset;
  /**
   * Contains the content of the <body> tag.
   *
   * @var   array
   * @since 2.0
   */
    private $_body = [];
  /**
   * Controls caching of the page
   *
   * @var   bool
   * @since 2.0
   */
    private $_cache = false;
  /**
   * Contains the character encoding string
   *
   * @var   string
   * @since 2.0
   */
    private $_charset = 'utf-8';
  /**
   * Contains the page language setting
   *
   * @var   string
   * @since 2.0
   */
    private $_language = null;
  /**
   * Array of Header <link> tags
   *
   * @var   array
   * @since 2.0
   */
    private $_links = [];
  /**
   * Array of meta tags
   *
   * @var   array
   * @since 2.0
   */
    private $_metaTags = [
    'standard' => ['Generator' => 'PEAR HTML_Page']
    ];
  /**
   * Document mime type
   *
   * @var   string
   * @since 2.0
   */
    private $_mime = 'text/html';
  /**
   * Document profile
   *
   * @var   string
   * @since 2.0
   */
    private $_profile = '';
  /**
   * Array of linked scripts
   *
   * @var   array
   * @since 2.0
   */
    private $_scripts = [];
  /**
   * Array of scripts placed in the header
   *
   * @var   array
   * @since 2.0
   */
    private $_script = [];
  /**
   * Array of included style declarations
   *
   * @var   array
   * @since 2.0
   */
    private $_style = [];
  /**
   * Array of linked style sheets
   *
   * @var   array
   * @since 2.0
   */
    private $_styleSheets = [];
  /**
   * HTML page title
   *
   * @var   string
   * @since 2.0
   */
    private $_title = '';
  /**
   * Array of raw header data
   *
   * @var   array
   * @since 2.0
   */
    private $_rawHeaderData = [];


    public function __construct($attributes = null, $tabOffset = 0)
    {
        parent::__construct($attributes, $tabOffset);
        if (class_exists('Locale')) {
            $this->_language = \Locale::getDefault();
        } else {
            $this->_language = "en";
        }
    }


  /**
   * Adds a linked script to the page
   *
   * @param string $url URL to the linked script
   * @param string $type Type of script. Defaults to 'text/javascript'
   *
   * @return void
   */
    public function addScript($url, $type = "text/javascript")
    {
        $this->_scripts[$url] = $type;
    } // end func _elementToHtml

  /**
   * Adds a script to the page
   *
   * <p>Content can be a string or an object with a toString method.
   * Defaults to text/javascript.</p>
   *
   * @param mixed $content Script (may be passed as a reference)
   * @param string $type Scripting mime (defaults to 'text/javascript')
   *
   * @return void
   */
    public function addScriptDeclaration($content, $type = 'text/javascript')
    {
        $this->_script[][strtolower($type)] =& $content;
    } // end func _generateBody

  /**
   * Adds a linked stylesheet to the page
   *
   * @param string $url URL to the linked style sheet
   * @param string $type Mime encoding type
   * @param string $media Media type that this stylesheet applies to
   *
   * @return void
   */
    public function addStyleSheet($url, $type = 'text/css', $media = null)
    {
        $this->_styleSheets[$url]['mime'] = $type;
        $this->_styleSheets[$url]['media'] = $media;
    } // end func _generateHead

  /**
   * Adds a stylesheet declaration to the page
   *
   * <p>Content can be a string or an object with a toString method.
   * Defaults to text/css.</p>
   *
   * @param mixed $content Style declarations (may be passed as a reference)
   * @param string $type Type of stylesheet (defaults to 'text/css')
   *
   * @return void
   */
    public function addStyleDeclaration($content, $type = 'text/css')
    {
        $this->_style[][strtolower($type)] =& $content;
    } // end addBodyContent

  /**
   * Adds a shortcut icon (favicon)
   *
   * <p>This adds a link to the icon shown in the favorites list or on
   * the left of the url in the address bar. Some browsers display
   * it on the tab, as well.</p>
   *
   * @param string $href The link that is being related.
   * @param string $type File type
   * @param string $relation Relation of link
   *
   * @return void
   */
    public function addFavicon(
        $href,
        $type = 'image/x-icon',
        $relation = 'shortcut icon'
    ) {
        $this->_links[] = "<link href=\"$href\" rel=\"$relation\" type=\"$type\"";
    } // end func addScript

  /**
   * Adds <link> tags to the head of the document
   *
   * <p>$relType defaults to 'rel' as it is the most common relation type used.
   * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
   * Typical tag: <link href="index.php" rel="Start"></p>
   *
   * @param string $href The link that is being related.
   * @param string $relation Relation of link.
   * @param string $relType Relation type attribute.
   *                           Either rel or rev (default: 'rel').
   * @param array $attributes Associative array of remaining attributes.
   *
   * @return void
   */
    public function addHeadLink($href, $relation, $relType = 'rel', $attributes = [])
    {
        $attributes = $this->_parseAttributes($attributes);
        $tag = $this->_getAttrString($attributes);
        $generatedTag = "<link href=\"$href\" $relType=\"$relation\"" . $tag;
        $this->_links[] = $generatedTag;
    } // end func addScriptDeclaration

  /**
   * Returns the document charset encoding.
   *
   * @return string
   */
    public function getCharset()
    {
        return $this->_charset;
    } // end func addStyleSheet

  /**
   * Sets the document charset
   *
   * <p>By default, HTML_Page2 uses UTF-8 encoding. This is properly handled
   * by PHP, but remember to use the htmlentities attribute for charset so
   * that whatever you get from a database is properly handled by the
   * browser.</p>
   *
   * <p>The current most popular encoding: iso-8859-1. If it is used,
   * htmlentities and htmlspecialchars can be used without any special
   * settings.</p>
   *
   * @param string $type Charset encoding string
   *
   * @return void
   */
    public function setCharset($type = 'utf-8')
    {
        $this->_charset = $type;
    } // end func addStyleDeclaration

  /**
   * Returns the document language.
   *
   * @return string
   */
    public function getLang()
    {
        return $this->_language;
    } // end func addFavicon

  /**
   * Prepends content to the content of the <body> tag. Wrapper
   * for {@link addBodyContent}
   *
   * <p>If you wish to overwrite whatever is in the body, use {@link setBody};
   * {@link addBodyContent} provides full functionality including appending;
   * {@link unsetBody} completely empties the body without inserting new content.
   * It is possible to add objects, strings or an array of strings and/or objects
   * Objects must have a toString method.</p>
   *
   * @param mixed $content New <body> tag content (may be passed as a reference)
   *
   * @return void
   */
    public function prependBodyContent($content)
    {
        $this->addBodyContent($content, HTML_PAGE2_PREPEND);
    } // end func addHeadLink

  /**
   * Sets the content of the <body> tag
   *
   * <p>It is possible to add objects, strings or an array of strings
   * and/or objects. Objects must have a toHtml or toString method.</p>
   *
   * <p>By default, if content already exists, the new content is appended.
   * If you wish to overwrite whatever is in the body, use {@link setBody};
   * {@link unsetBody} completely empties the body without inserting new
   * content. You can also use {@link prependBodyContent} to prepend content
   * to whatever is currently in the array of body elements.</p>
   *
   * <p>The following constants are defined to be passed as the flag
   * attribute: HTML_PAGE2_APPEND, HTML_PAGE2_PREPEND and HTML_PAGE2_REPLACE.
   * Their usage should be quite clear from their names.</p>
   *
   * @param mixed $content New <body> tag content (may be passed as a reference)
   * @param int $flag Whether to prepend, append or replace the content.
   *
   * @return void
   */
    public function addBodyContent($content, $flag = HTML_PAGE2_APPEND)
    {

        if ($flag == HTML_PAGE2_REPLACE) {       // replaces any content in body
            $this->unsetBody();
            $this->_body[] =& $content;
        } elseif ($flag == HTML_PAGE2_PREPEND) { // prepends content to the body
            array_unshift($this->_body, $content);
        } else {                                // appends content to the body
            $this->_body[] =& $content;
        }
    } // end setCache

  /**
   * Unsets the content of the <body> tag.
   *
   * @return void
   */
    public function unsetBody()
    {
        $this->_body = [];
    } // end func getLang

  /**
   * Sets the content of the <body> tag.
   *
   * <p>If content exists, it is overwritten. If you wish to use a "safe"
   * version, use {@link addBodyContent}. Objects must have a toString
   * method.</p>
   *
   * <p>This function acts as a wrapper for {@link addBodyContent}. If you
   * are using PHP 4.x and would like to pass an object by reference, this
   * is not the function to use. Use {@link addBodyContent} with the flag
   * HTML_PAGE2_REPLACE instead.</p>
   *
   * @param mixed $content New <body> tag content. May be an object or passed
   *                       as a reference.
   *
   * @return void
   */
    public function setBody($content)
    {
        $this->addBodyContent($content, HTML_PAGE2_REPLACE);
    } // end func getTitle

  /**
   * Sets the attributes of the <body> tag
   *
   * <p>If attributes exist, they are overwritten. In XHTML, all attribute
   * names must be lowercase. As lowercase attributes are legal in SGML, all
   * attributes are automatically lowercased. This also prevents accidentally
   * creating duplicate attributes when attempting to update one.</p>
   *
   * @param array $attributes <body> tag attributes.
   *
   * @return void
   */
    public function setBodyAttributes($attributes)
    {
        $this->setAttributes($attributes);
    } // end func prependBodyContent

  /**
   * Defines if the document should be cached by the browser
   *
   * <p>Defaults to false.</p>
   *
   * <p>A fully configurable cache header is in the works. for now, though
   * if you would like to determine exactly what caching headers are sent to
   * to the browser, set cache to true, and then output your own headers
   * before calling {@link display}.</p>
   *
   * @param string $cache Options are currently 'true' or 'false'
   *
   * @return void
   */
    public function setCache($cache = 'false')
    {
        if ($cache == 'true') {
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end setBody

  /**
   * Sets the global document language declaration. Default is English.
   *
   * @param string $lang Two-letter language designation
   *
   * @return void
   */
    public function setLang($lang = "en")
    {
        $this->_language = strtolower($lang);
    } // end unsetBody

  /**
   * Sets an http-equiv Content-Type meta tag
   *
   * @return void
   */
    public function setMetaContentType()
    {
        $this->setMetaData(
            'Content-Type',
            $this->_mime . '; charset=' . $this->_charset,
            true
        );
    } // end setBodyAttributes

  /**
   * Sets or alters a meta tag.
   *
   * @param string $name Value of name or http-equiv tag
   * @param string $content Value of the content tag
   * @param bool $http_equiv META type "http-equiv" defaults to null
   *
   * @return void
   */
    public function setMetaData($name, $content, $http_equiv = false)
    {
        if ($content == '') {
            $this->unsetMetaData($name, $http_equiv);
        } else {
            if ($http_equiv == true) {
                $this->_metaTags['http-equiv'][$name] = $content;
            } else {
                $this->_metaTags['standard'][$name] = $content;
            }
        }
    } // end setCache

  /**
   * Unsets a meta tag.
   *
   * @param string $name Value of name or http-equiv tag
   * @param bool $http_equiv META type "http-equiv" defaults to null
   *
   * @return void
   */
    public function unsetMetaData($name, $http_equiv = false)
    {
        if ($http_equiv == true) {
            unset($this->_metaTags['http-equiv'][$name]);
        } else {
            unset($this->_metaTags['standard'][$name]);
        }
    } // end setCache

  /**
   * Shortcut to set or alter a refresh meta tag
   *
   * <p>If no $url is passed, "self" is presupposed, and the appropriate URL
   * will be automatically generated. In this case, an optional third
   * boolean parameter enables https redirects to self.</p>
   *
   * @param int $time Time till refresh (in seconds)
   * @param string $url Absolute URL or "self"
   * @param bool $https If $url == self, then set protocol to https://
   *
   * @return void
   */
    public function setMetaRefresh($time, $url = 'self', $https = false)
    {
        if ($url == 'self') {
            if ($https) {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $this->setMetaData("Refresh", "$time; url=$url", true);
    } // end setLang

  /**
   * Sets the document MIME encoding that is sent to the browser.
   *
   * <p>This usually will be text/html because most browsers cannot yet
   * accept the proper mime settings for XHTML: application/xhtml+xml
   * and to a lesser extent application/xml and text/xml. See the W3C note
   * ({@link http://www.w3.org/TR/xhtml-media-types/
   * http://www.w3.org/TR/xhtml-media-types/}) for more details.</p>
   *
   * <p>Here is a possible way of automatically including the proper mime
   * type for XHTML 1.0 if the requesting browser supports it:</p>
   *
   * <code>
   * <?php
   * // Initialize the HTML_Page2 object:
   * require 'HTML/Page2.php';
   * $page = new HTML_Page2();
   *
   * // Check if browse can take the proper mime type
   * if ( strpos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') ) {
   *     $page->setDoctype('XHTML 1.0 Strict');
   *     $page->setMimeEncoding('application/xhtml+xml');
   * } else {
   *     // HTML that qualifies for XHTML 1.0 Strict automatically
   *     // also complies with XHTML 1.0 Transitional, so if the
   *     // requesting browser doesn't take the necessary mime type
   *     // for XHTML 1.0 Strict, let's give it what it can take.
   *     $page->setDoctype('XHTML 1.0 Transitional');
   * }
   *
   * // finish building your page here..
   *
   * $page->display();
   * ?>
   * </code>
   *
   * @param string $type Optional. Defaults to 'text/html'
   *
   * @return void
   */
    public function setMimeEncoding($type = 'text/html')
    {
        $this->_mime = strtolower($type);
    } // end func setMetaData

  /**
   * Generates the document and outputs it to a file.
   *
   * <p>Uses {@link file_put_contents} when available. Includes a workaround
   * for older versions of PHP.</p>
   *
   * <p>Usage example:</p>
   * <code>
   * <?php
   * require "HTML/Page2.php";
   * $page = new HTML_Page2();
   * $page->setTitle('My Page');
   * $page->addBodyContent('<h1>My Page</h1>');
   * $page->addBodyContent('<p>First Paragraph.</p>');
   * $page->addBodyContent('<p>Second Paragraph.</p>');
   * $page->toFile('myPage.html');
   * ?>
   * </code>
   *
   * @param string $filename Filename to output document to.
   *
   * @return void
   * @since  2.0
   */
    public function toFile($filename)
    {
        if (function_exists('file_put_contents')) {
            file_put_contents($filename, $this->toHtml());
        } else {
            $file = fopen($filename, 'wb');
            fwrite($file, $this->toHtml());
            fclose($file);
        }

        if (!file_exists($filename)) {
            PEAR::raiseError(
                "HTML_Page::toFile() error: Failed to write to $filename",
                0,
                PEAR_ERROR_TRIGGER
            );
        }
    } // end func unsetMetaData

  /**
   * Generates and returns the complete page as a string
   *
   * <p>This is what you would call if you want to save the page in a
   * database. It creates a complete, valid HTML document, and returns
   * it as a string.</p>
   *
   * <p>Usage example:</p>
   * <code>
   * <?php
   * require "HTML/Page2.php";
   * $page = new HTML_Page2();
   * $page->setTitle('My Page');
   * $page->addBodyContent('<h1>My Page</h1>');
   * $page->addBodyContent('<p>First Paragraph.</p>');
   * $page->addBodyContent('<p>Second Paragraph.</p>');
   * $html = $page->toHtml();
   * // here you insert HTML into a database
   * ?>
   * </code>
   *
   * @return string
   */
    public function toHtml()
    {
      // This determines how the doctype is declared and enables various
      // features depending on whether the the document is XHTML, HTML or
      // if no doctype declaration is desired
        $strHtml = sprintf('<html lang="%s">', $this->_language);
      // indent all nodes of <html> one place
        $this->_tabOffset++;
        $strHtml .= $this->_generateHead();
        $strHtml .= $this->_generateBody();
      // In case something else is going to be done with this object,
      // let's set the offset back to normal.
        $this->_tabOffset--;
        $strHtml .= '</html>';
        return $strHtml;
    } // end func setMetaContentType

  /**
   * Generates the HTML string for the <head> tag
   *
   * @return string
   */
    private function _generateHead()
    {


        $tab = $this->_getTab();
        $tabs = $this->_getTabs();

        $strHtml = $tabs . '<head>' . PHP_EOL;

      // Generate META tags
        foreach ($this->_metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $strHtml .= $tabs . $tab
                    . "<meta http-equiv=\"$name\" content=\"$content\" />" . PHP_EOL;
                } elseif ($type == 'standard') {
                    $strHtml .= $tabs . $tab
                    . "<meta name=\"$name\" content=\"$content\" />" . PHP_EOL;
                }
            }
        }

      // Generate the title tag.
      // Pre-XHTML compatibility:
      //     This comes after meta tags because of possible
      //     http-equiv character set declarations.
        $strHtml .= $tabs . $tab
        . '<title>' . $this->getTitle() . '</title>' . PHP_EOL;

      // Generate link declarations
        foreach ($this->_links as $link) {
            $strHtml .= $tabs . $tab . $link . $tagEnd . PHP_EOL;
        }

      // Generate stylesheet links
        foreach ($this->_styleSheets as $strSrc => $strAttr) {
            $strHtml .= $tabs . $tab
            . "<link rel=\"stylesheet\" href=\"$strSrc\" type=\""
            . $strAttr['mime'] . '"';
            if (!is_null($strAttr['media'])) {
                $strHtml .= ' media="' . $strAttr['media'] . '"';
            }
            $strHtml .= PHP_EOL;
        }

      // Generate stylesheet declarations
        foreach ($this->_style as $styledecl) {
            foreach ($styledecl as $type => $content) {
                $strHtml .= $tabs . $tab . '<style type="' . $type . '">' . PHP_EOL;

              // This is for full XHTML support.
                if ($this->_mime == 'text/html') {
                    $strHtml .= $tabs . $tab . $tab . '<!--' . PHP_EOL;
                } else {
                    if (substr($content, 0, strlen('@import ')) != '@import ') {
                        $strHtml .= $tab . $tab . $tab . '<![CDATA[' . PHP_EOL;
                    }
                }

                if (is_object($content)) {
                  // first let's propagate line endings and tabs for other
                  // HTML_Common-based objects
                    if (is_subclass_of($content, "html_common")) {
                        $content->setTab($tab);
                        $content->setTabOffset(3);
                        $content->setLineEnd(PHP_EOL);
                    }

                  // now let's get a string from the object
                    if (method_exists($content, "toString")) {
                        $strHtml .= $content->toString() . PHP_EOL;
                    } else {
                        PEAR::raiseError(
                            'Error: Style content object does not support  '
                            . 'method toString().',
                            0,
                            PEAR_ERROR_TRIGGER
                        );
                    }
                } else {
                    $strHtml .= $content . PHP_EOL;
                }

              // See above note
                if ($this->_mime == 'text/html') {
                    $strHtml .= $tabs . $tab . $tab . '-->' . PHP_EOL;
                } else {
                    if (substr($content, 0, strlen('@import ')) != '@import ') {
                        $strHtml .= $tab . $tab . ']]>' . PHP_EOL;
                    }
                }
                $strHtml .= $tabs . $tab . '</style>' . PHP_EOL;
            }
        } // end generating stylesheet blocks

      // Generate script file links
        foreach ($this->_scripts as $strSrc => $strType) {
            if (is_string($strType)) {
                $strHtml .= $tabs . $tab
                . "<script type=\"$strType\" src=\"$strSrc\"></script>" . PHP_EOL;
            } elseif (is_array($strType)) {
                $type = isset($strType['type']) ?
                $strType['type'] : 'text/javascript';
                $execute
                = isset($strType['execute']) ? $strType['execute'] : 'immediate';

                $strHtml .= $tabs . $tab
                . '<script type="' . $type . '" src="' . $strSrc . '"';
                if ($execute == 'async' || $execute == 'defer') {
                    if ($this->_mime == "text/xhtml") {
                        $strHtml .= ' ' . $execute . '="' . $execute . '"';
                    } else {
                        $strHtml .= ' ' . $execute;
                    }
                }
                $strHtml .= '>';
                $strHtml .= '</script>' . PHP_EOL;
            }
        }

      // Generate script declarations
        foreach ($this->_script as $script) {
            foreach ($script as $type => $content) {
                $strHtml .= $tabs . $tab . '<script type="' . $type . '">' . PHP_EOL;

              // This is for full XHTML support.
                if ($this->_mime == 'text/html') {
                    $strHtml .= $tabs . $tab . $tab . '// <!--' . PHP_EOL;
                } else {
                    $strHtml .= $tabs . $tab . $tab . '<![CDATA[' . PHP_EOL;
                }

                if (is_object($content)) {
                  // First let's propagate line endings and tabs for
                  // other HTML_Common-based objects
                    if (is_subclass_of($content, "html_common")) {
                        $content->setTab($tab);
                        $content->setTabOffset(3);
                        $content->setLineEnd(PHP_EOL);
                    }

                  // now let's get a string from the object
                    if (method_exists($content, "toString")) {
                        $strHtml .= $content->toString() . PHP_EOL;
                    } else {
                        PEAR::raiseError(
                            'Error: Script content object does not support  '
                            . 'method toString().',
                            0,
                            PEAR_ERROR_TRIGGER
                        );
                    }
                } else {
                    $strHtml .= $content . PHP_EOL;
                }

              // See above note
                if ($this->_mime == 'text/html') {
                    $strHtml .= $tabs . $tab . $tab . '// -->' . PHP_EOL;
                } else {
                    $strHtml .= $tabs . $tab . $tab . '// ]]>' . PHP_EOL;
                }
                $strHtml .= $tabs . $tab . '</script>' . PHP_EOL;
            }
        } // end generating script blocks

        foreach ($this->_rawHeaderData as $content) {
            $strHtml .= $content . PHP_EOL;
        }

      // Close tag
        $strHtml .= $tabs . '</head>' . PHP_EOL;

      // Let's roll!
        return $strHtml;
    } // end func setMetaRefresh

  /**
   * Return the title of the page.
   *
   * @return string
   */
    public function getTitle()
    {
        if (!$this->_title) {
            return 'New Page';
        } else {
            return $this->_title;
        }
    } // end func setMimeEncoding

  /**
   * Sets the title of the page
   *
   * <p>Usage:</p>
   *
   * <code>
   * $page->setTitle('My Page');
   * </code>
   *
   * @param string $title Title of the page
   *
   * @return void
   */
    public function setTitle($title)
    {
        $this->_title = $title;
    } // end func setTitle

  /**
   * Generates the HTML string for the <body> tag
   *
   * @return string
   */
    private function _generateBody()
    {

      // get line endings
        $lnEnd = $this->_getLineEnd();
        $tabs = $this->_getTabs();

      // If body attributes exist, add them to the body tag.
      // Many attributes are depreciated because of CSS.
        $strAttr = $this->_getAttrString($this->_attributes);

      // If this is a frameset, we don't want to output the body tag, but
      // rather the <noframes> tag.
        if (isset($this->_doctype['variant'])
        && $this->_doctype['variant'] == 'frameset'
        ) {
            $this->_tabOffset++;
            $tabs = $this->_getTabs();
            $strHtml = $tabs . '<noframes>' . $lnEnd;
            $this->_tabOffset++;
            $tabs = $this->_getTabs();
        } else {
            $strHtml = '';
        }

        if ($strAttr) {
            $strHtml .= $tabs . "<body $strAttr>" . $lnEnd;
        } else {
            $strHtml .= $tabs . '<body>' . $lnEnd;
        }

      // Allow for mixed content in the body array, recursing into inner
      // array serching for non-array types.
        $strHtml .= $this->_elementToHtml($this->_body);

      // Close tag
        $strHtml .= $tabs . '</body>' . $lnEnd;

      // See above comment for frameset usage
        if (isset($this->_doctype['variant'])
        && $this->_doctype['variant'] == 'frameset'
        ) {
            $this->_tabOffset--;
            $strHtml .= $this->_getTabs() . '</noframes>' . $lnEnd;
            $this->_tabOffset--;
        }

      // Let's roll!
        return $strHtml;
    } // end func toHtml

  /**
   * Iterates through an array, returning an HTML string
   *
   * <p>It also handles objects, calling the toHTML or toString methods
   * and propagating the line endings and tabs for objects that
   * extend HTML_Common.</p>
   *
   * <p>For more details read the well-documented source.</p>
   *
   * @param mixed $element The element to be processed
   *
   * @return string
   */
    protected function _elementToHtml(&$element)
    {

      // element is passed as a reference just to save some memory.
      // get the special formatting settings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        $tabs = $this->_getTabs();
        $offset = $this->getTabOffset() + 1;

      // initialize the variable that will collect our generated HTML
        $strHtml = '';

      // Attempt to generate HTML code for what is passed
        if (is_object($element)) {
          // If this is an object, attempt to generate the appropriate HTML
          // code.

            if (is_subclass_of($element, 'html_common')) {
                // For this special case, we set the appropriate indentation
                // and line end styles. That way uniform HTML is generated.

                // The reason this does not check for each method individually
                // is that it could be that setTab, for example, could
                // possibly refer to setTable, etc. And such ambiguity could
                // create a big mess. So this will simply bias  the HTML_Page
                // class family toward other HTML_Common-based classes.

                // Of course, these features are not necessarily implemented
                // in all HTML_Common-based packages. But at least this makes
                // it possible to propagate the settings.
                $element->setTabOffset($offset);
                $element->setTab($tab);
                $element->setLineEnd($lnEnd);
            }

          // Attempt to generate code using first toHtml and then toString
          // methods. The result is not parsed with _elementToHtml because
          // it would improperly add one tab indentation to the initial line
          // of each object's output.
            if (method_exists($element, 'toHtml')) {
                $strHtml .= $element->toHtml() . $lnEnd;
            } elseif (method_exists($element, 'toString')) {
                $strHtml .= $element->toString() . $lnEnd;
            } else {
              // If the class does not have an appropriate method, an error
              // should be returned rather than simply dying or outputting
              // the difficult to troubleshoot 'Object' output.
                $class = get_class($element);
                PEAR::raiseError(
                    "Error: Content object (class $class) " .
                    'does not support  methods toHtml() or ' .
                    'toString().',
                    0,
                    PEAR_ERROR_TRIGGER
                );
            }
        } elseif (is_array($element)) {
            foreach ($element as $item) {
              // Parse each element individually
                $strHtml .= $this->_elementToHtml($item);
            }
        } else {
          // If we don't have an object or array, we can simply output
          // the element after indenting it and properly ending the line.
            $strHtml .= $tabs . $tab . $element . $lnEnd;
        }

        return $strHtml;
    } // end func toFile

  /**
   * Outputs the HTML content to the browser
   *
   * <p>This method outputs to the default display device. Normally that
   * will be the browser.</p>
   *
   * <p>If caching is turned off, which is the default case, this generates
   * the appropriate headers:</p>
   *
   * <code>
   * header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
   * header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
   * header("Cache-Control: no-cache");
   * header("Pragma: no-cache");
   * </code>
   *
   * <p>This functionality can be disabled:</p>
   *
   * <code>
   * $page->setCache('true');
   * </code>
   *
   * @return void
   */
    public function display()
    {
        $this->addCachingHeaders();
        echo $this->toHTML();
    }


    public function addCachingHeaders()
    {
      // If caching is to be implemented, this bit of code will need to be
      // replaced with a private function. Else it may be possible to
      // borrow from Cache or Cache_Lite.
        if (!$this->_cache === false) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }

      // Set mime type and character encoding
        header('Content-Type: ' . $this->_mime . '; charset=' . $this->_charset);
    }


  /**
   * Adds raw data to the head of the document
   *
   * <p>Use this function to add raw data strings to the header.</p>
   *
   * @param string $content Raw data to be added.
   *
   * @return void
   */
    public function addRawHeaderData($content)
    {
        $this->_rawHeaderData[] = $content;
    }

  /**
   * Get/Return the Body Content.
   *
   * @return string
   */
    public function getBodyContent()
    {
        return $this->_elementToHtml($this->_body);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
