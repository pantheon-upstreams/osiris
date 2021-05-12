<?php

namespace Pantheon\Osiris;

/**
 * Class PHPVersion
 * @package Pantheon\Osiris
 */
class PHPVersion implements PHPVersionInterface
{

  /**
   * PHPVersion constructor.
   * @param string $version
   */
    public function __construct($version)
    {
        if (substr($version, 0, 1) === "v") {
            $this->setFromMultiDevName($version);
        } elseif (strpos($version, ".") !== false) {
            $this->setFromUserVersion($version);
        } elseif (strlen($version) === 2) {
            $this->setFromShortVersion($version);
        }
    }

  /**
   * @var string
   */
    protected $userVersion = null;
  /**
   * @var string
   */
    protected $shortVersion = null;
  /**
   * @var string
   */
    protected $multiDev = null;

  /**
   * @return string
   */
    public function getUserVersion()
    {
        return $this->userVersion;
    }

  /**
   * @param string $version
   */
    public function setFromUserVersion($version)
    {
        $this->userVersion = $version;
        $version_parts = explode('.', $version);
        $this->shortVersion = $version_parts[0] . $version_parts[1];
        $this->multiDev = "v{$this->shortVersion}";
    }

  /**
   * @return string|null
   */
    public function getShortVersion()
    {
        $this->shortVersion;
    }

  /**
   * @param string $version
   */
    public function setFromShortVersion($version)
    {
        $this->shortVersion = $version;
        $this->userVersion = substr($version, 0, 1) . "." . substr($version, 1, 1);
        $this->multiDev = "v$version";
    }

  /**
   * @return string|null
   */
    public function getMultiDevName()
    {
        return $this->multiDev;
    }

  /**
   * @param string $version
   */
    public function setFromMultiDevName($version)
    {
        $this->multiDev = $version;
        $this->shortVersion = str_replace("v", "", $version);
        $this->userVersion = substr($this->shortVersion, 0, 1) . "." . substr($this->shortVersion, 1, 1);
    }

  /**
   * @return bool
   */
    public function valid()
    {
        return ($this->userVersion !== null && $this->shortVersion !== null && $this->multiDev !== null);
    }
}
