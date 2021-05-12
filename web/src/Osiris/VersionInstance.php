<?php
/**
 * @phpcs:ignoreFile
 *
 * Create an instance of his Majesty's glory.
 *
 * @warning    THIS CLASS MUST WORK ON EVERY VERSION OF PHP WE CURRENTLY SUPPORT
 * @author     stovak <stovak@gmail.com>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL v2
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

//

namespace Pantheon\Osiris;

use Symfony\Component\Yaml\Yaml;

/**
 * Class VersionInstance
 *
 * @package Pantheon\Osiris
 */
class VersionInstance
{


  /**
   * @var VersionInstance
   */
  var $version;

  /**
   * @var VersionInstance
   */
  var $siteid;


  /**
   * VersionInstance constructor.
   *
   * @param $user_version
   *
   * @example
   * $user_version: "7.1"
   * $short_version: "71"
   * $multidev: "v71"
   *
   */

  public function __construct($version, $siteid = "")
  {
    $this->siteid = $siteid;
    $this->version = new PHPVersion($version);
    if ($this->version->valid() !== true) {
      throw new \Exception("Cannot determine PHP Version");
    }
  }

  /**
   * @param string $terminusSiteName
   */
  public function create()
  {

    try {
      $envName = $this->version->getMultiDevName();
      if ($this->exists($envName)) {
        echo "Environment exists - " . $envName . PHP_EOL;
      } else {
        echo "Ensuring... " . $envName . PHP_EOL;
        exec(sprintf("terminus env:create %s %s --yes --no-interaction",
          $this->siteid, $this->version->getMultiDevName()
        ), $output, $status);
      }
    } catch (\Exception $e) {
      echo $e->getMessage();
    } catch (\Throwable $t) {
      echo $t->getMessage();
    }
  }

  /**
   * @param $env_name
   *
   * @return bool
   */
  public function exists($env_name)
  {
    $command = sprintf("terminus env:list %s --format=json", $this->siteid);
    exec($command, $output, $status);
    $versions = json_decode(join(PHP_EOL, $output), true);
    return is_array($versions) && isset($versions[$env_name]);
  }

  /**
   *
   */
  public function update($path)
  {
    $this->fetchEnvironmentBranch($path);
    $this->doPassthru("git rebase origin/master");
    $yaml = yaml_parse_file("$path/pantheon.yml");
    $upstreamYaml = yaml_parse_file("$path/pantheon.upstream.yml");
    unset($upstreamYaml['database'], $upstreamYaml['drush_version'], $upstreamYaml['build_step']);
    $upstreamYaml['php_version'] = $yaml['php_version'] = (float)$this->version->getUserVersion();
    file_put_contents("$path/pantheon.yml", YAML::dump($yaml,));
    file_put_contents("$path/pantheon.upstream.yml", YAML::dump($upstreamYaml));

    echo "Changing contents of pantheon.yml" . print_r($yaml, true) . PHP_EOL;
    echo "Changing contents of pantheon.upstream.yml" . print_r($upstreamYaml,
        true) . PHP_EOL;
    $this->doPassthru("git add pantheon.yml");
    $this->doPassthru("git add pantheon.upstream.yml");
    file_put_contents("vendor/composer/platform_check.php", "<?php");
    $this->doPassthru("git add -f vendor/composer/platform_check.php");

    exec(sprintf("git commit -m 'PHP version %s'",
      $this->version->getUserVersion()), $output, $status);

    $this->doPassthru(sprintf("git push origin %s",
      $this->version->getMultiDevName()));
    exec(sprintf('terminus env:clear-cache %s.%s', $this->siteid,
      $this->version->getMultiDevName()), $output, $status);
  }

  /**
   * @return void
   */
  function fetchEnvironmentBranch($path)
  {
    chdir($path);
    // Try to fetch the environment
    while (true) {
      exec(sprintf("git checkout %s", $this->version->getMultiDevName()),
        $output, $status);
      if (is_array($output)) {
        $output = $output[0];
      }
      if (substr($output[0], 0, 10) !== "Already on" || $status === 0) {
        return;
      }
      if ($status !== 0) {
        print(sprintf("No branch exists for %s yet; will retry in a bit.\n",
          $this->version->getMultiDevName()));
      }
      sleep(20);
    }
  }

  /**
   * @param $cmd
   */
  function doPassthru($cmd)
  {
    print "### $cmd\n";
    passthru($cmd, $status);
    if ($status) {
      exit($status);
    }
  }

}

