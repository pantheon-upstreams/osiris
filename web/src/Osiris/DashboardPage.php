<?php

namespace Pantheon\Osiris;

use Pantheon\HTML\BootstrapPage;

/**
 * Class DashboardPage
 */
class DashboardPage extends BootstrapPage
{

    public static $GRID_SCRIPTS = [
    "smart.element.js",
    "smart.button.js",
    "smart.checkbox.js",
    "smart.calendar.js",
    "smart.complex.js",
    "smart.data.js",
    "smart.date.js",
    "smart.datetimepicker.js",
    "smart.draw.js",
    "smart.dropdownlist.js",
    "smart.combobox.js",
    "smart.filter.js",
    "smart.filterbuilder.js",
    "smart.filterpanel.js",
    "smart.input.js",
    "smart.listbox.js",
    "smart.math.js",
    "smart.menu.js",
    "smart.tree.js",
    "smart.numeric.js",
    "smart.numerictextbox.js",
    "smart.scrollbar.js",
    "smart.tooltip.js",
    "smart.timepicker.js",
    "smart.window.js",
    "smart.grid.js",
    ];

  /**
   * DashboardPage constructor.
   */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle("Pantheon PHP Availability of Features Dashboard");
        $this->build();
    }



  /**
   *
   */
    public function build()
    {
        $this->addScript("https://rawgit.com/Microsoft/TypeScript/master/lib/typescriptServices.js", "module");
        $this->addScript("https://rawgit.com/basarat/typescript-script/master/transpiler.js", "module");
        $this->addStyleSheet('/libraries/smart-webcomponents/source/styles/smart.default.css');
        $this->addScriptsForDashboard();
      // Dashboard page loads a window.onload that adds the tag to the body
        $this->addScript("/js/dashboard-page.js");
        $this->addBodyContent('<script type="application/json" data-settings="dashboard-settings">'.json_encode([
        "versions" => $this->getPHPVersions(),
        'modules' => $this->getModules(),
        ], JSON_UNESCAPED_LINE_TERMINATORS && JSON_UNESCAPED_SLASHES && JSON_UNESCAPED_UNICODE) .
          '</script>', 'application.json');
        $this->addBodyContent("<smart-grid id='grid'></smart-grid>");
        $this->addStyleDeclaration("smart-grid { width: auto; height: auto;}");
    }

    protected function addScriptsForDashboard()
    {
        foreach (static::$GRID_SCRIPTS as $script) {
            $this->addScript('/libraries/smart-webcomponents/source/' . $script, 'text/javascript');
        }
    }

  /**
   * @return array
   */
    protected function getPHPVersions()
    {
        $versionList = [];
        if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && substr($_SERVER['PANTHEON_ENVIRONMENT'], 0, 1) === "v") {
            [$major, $minor] = explode(".", PHP_VERSION);
            $versionList["v" . $major . $minor] = $_SERVER['HTTP_HOST'];
        } else {
            $composer = json_decode(file_get_contents(getcwd() . "/../composer.json"), true);
            $vids = $composer['extra']['osiris']['supported_versions'];
            foreach ($vids as $vid) {
                $versionList[$vid] = (
                  isset($_SERVER['PANTHEON_ENVIRONMENT']) ?
                    str_replace($_SERVER['PANTHEON_ENVIRONMENT'], $vid, $_SERVER['HTTP_HOST']) :
                    $_SERVER['HTTP_HOST'] )
                  . "/info.php?version=" . $vid ;
            }
        }
        return $versionList;
    }

  /**
   * @return array|void
   */
    protected function getModules()
    {
        $info = new \Pantheon\Osiris\PHPInfo();
        return $info->getModuleList();
    }
}
