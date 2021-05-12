<?php


namespace Pantheon\Osiris;

use Composer\Json\JsonFile;

/**
 * Class ComposerFile
 *
 * @package Pantheon\Osiris
 */
class ComposerFile extends JsonFile
{

  /**
   * ComposerFile constructor.
   */
    public function __construct()
    {
        parent::__construct(\Composer\Factory::getComposerFile());
    }

  /**
   * @return array
   * @throws \Seld\JsonLint\ParsingException
   */
    public static function getExtraValues()
    {
        $toReturn = new static();
        return $toReturn->read()['extra']['osiris'] ?? null;
    }
}
