<?php


namespace Pantheon\HTML;

/**
 * Class BootstrapPage
 * @package Pantheon\HTML
 */
class BootstrapPage extends Page2
{


  /**
   * BootstrapPage constructor.
   *
   * @example new BootstrapPage()->display();
   *
   */
    public function __construct()
    {
        parent::__construct();
        $this->addScriptDeclaration(file_get_contents("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"));
        $this->addStyleDeclaration(file_get_contents("../vendor/twbs/bootstrap/dist/css/bootstrap.min.css"));
    }
}
