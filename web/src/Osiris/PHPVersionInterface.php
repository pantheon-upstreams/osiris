<?php

namespace Pantheon\Osiris;

/**
 * Interface PHPVersionInterface
 * @package Pantheon\Osiris
 */
interface PHPVersionInterface
{

  /**
   * @return bool
   */
    public function valid();


  /**
   * @return string
   */
    public function getUserVersion();

  /**
   * @param string $version
   * @return void
   */
    public function setFromUserVersion($version);

  /**
   * @return string
   */
    public function getShortVersion();

  /**
   * @param string $version
   * @return void
   */
    public function setFromShortVersion($version);

  /**
   * @return string
   */
    public function getMultiDevName();

  /**
   * @param string $version
   * @return void
   */
    public function setFromMultiDevName($version);
}
