<?php

namespace Pantheon\Osiris;

use Composer\IO\IOInterface;
use Composer\Script\Event;

/**
 * Class BuildScript
 *
 * @package Pantheon\Osiris
 */
class BuildScript
{

  /**
   * UUID Pattern Regex.
   *
   * @var string
   */
    public static $PATTERN = '/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/m';

  /**
   * Post Install Hook.
   */
    public static function postInstall($event = null)
    {
        $projectRoot = getcwd();
        copy($projectRoot . "/web/info.php", $projectRoot . "/web/index.php");
        echo "POST INSTALL COMMAND EXECUTION: " . static::getSiteID() . PHP_EOL;
    }

  /**
   * @return mixed
   * @throws \Exception
   */
    public static function getSiteID()
    {
        if ($_SERVER['argv']) {
            return (string) end($_SERVER['argv']);
        }
        throw new \Exception("Cannot determine the Site ID");
    }

  /**
   * Post Update Hook
   */
    public static function postUpdate($event = null)
    {
        $projectRoot = getcwd();
        copy($projectRoot . "/web/info.php", $projectRoot . "/web/index.php");
        echo "POST UPDATE COMMAND EXECUTION - " . static::getSiteID() . PHP_EOL;
    }

  /**
   * @usage composer ensure {PANTHEON_SITE_ID}
   *
   * @throws \Exception
   */
    public static function ensureVersionEnvironments(Event $event)
    {
        $io = $event->getIO();
        $siteID = static::getSiteID();
        $cwd = getcwd();
        if (static::siteExists($siteID, $io) === false) {
            static::createSite($siteID, $io);
        }
        $environments = [];
        $supportedVersions = static::getSupportedVersions($event);
        foreach ($supportedVersions as $version) {
            try {
                $environments[$version] = new VersionInstance(
                    $version,
                    static::getSiteID()
                );
                $environments[$version]->create();
            } catch (\Exception $e) {
                $environments[$version] = sprintf("Exception %s", $e->getMessage());
            } catch (\Throwable $t) {
                $environments[$version] = sprintf("THROWABLE %s", $t->getMessage());
            }
        }
      // YES, we want to do this separately.
      // At the time of inception, there are still several
      // processes that have not run and when terminus
      // releases you from the create process, the environment
      // isn't quite ready. Doing it this way will allow for time
      // for the environment to complete buildout.
        exec("git remote show github | grep Fetch", $output, $status);
        if (strpos(join("", $output), "github.com")) {
          // github - being run from the upstream clone
            static::ensureClonedLocally($siteID, $cwd);
            foreach ($environments as $environment) {
                $environment->update($cwd . "/" . $siteID);
            }
        }
      // inside cloned pantheon instance
        exit("inside a cloned instance");
    }

  /**
   * @param string $siteID
   * @param IOInterface $io
   *
   * @return bool
   * @throws \Exception
   */
    public static function siteExists(string $siteID, IOInterface $io)
    {

        $io->info(sprintf("checking if site exists: %s", $siteID));
        exec(
            sprintf('terminus site:info %s --format=json', $siteID),
            $output,
            $status
        );
        return ($status !== 1) || (count($output) > 0);
    }

  /**
   * @param string $siteID
   * @param IOInterface $io
   *
   * @return bool
   * @throws \Exception
   */
    public static function createSite(string $siteID, IOInterface $io)
    {

        $io->write(sprintf("Site does not exist... Creating %s.", $siteID));
        $command = sprintf(
            "terminus site:create %s %s osiris --org=%s",
            $siteID,
            $siteID,
            ComposerFile::getExtraValues()['organization']
        );
        if ($io->isVerbose()) {
            $io->write($command);
        }
        exec($command, $output, $status);
        if ($status !== 0) {
            $io->alert(join(PHP_EOL, $output));
        }
        return true;
    }

  /**
   * Get supported versions of PHP.
   *
   * List in the composer file is canonical.
   * hard coded array is fallback.
   * The reason this exists is so that tests can override the canonical list
   * with and only test one version by temporarily changing the
   * composer file.
   *
   *
   * @return mixed|string[]
   */
    public static function getSupportedVersions($event = null)
    {
        try {
            if ($event instanceof Event) {
                return static::getConfig($event)['supported_versions'] ?? null;
            }
            if (isset($_SERVER['PANTHEON_ENVIRONMENT'])
            && substr($_SERVER['PANTHEON_ENVIRONMENT'] ?? "", 0, 1) === "v"
            ) {
                [$major, $minor] = explode(".", PHP_VERSION);
                return json_encode(["v" . $major . $minor]);
            } else {
                $composer = json_decode(
                    file_get_contents(getcwd() . "/../composer.json"),
                    true
                );
                return $composer['extra']['osiris']['supported_versions'] ?? [];
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        } catch (\Throwable $t) {
            exit($t->getMessage());
        }
    }

  /**
   * @param $event
   *
   * @return mixed|null
   */
    public static function getConfig($event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();
        return $extras['osiris'] ?? null;
    }

  /**
   * @param $siteID
   * @param $cwd
   *
   * @return bool
   */
    public static function ensureClonedLocally($siteID, $cwd)
    {
        $connectionInfo = static::getSiteConnectionInfo($siteID);
        chdir($cwd);
        $siteFolder = $cwd . "/" . $siteID;
      // if the folder exists, assume it's dirty and you need a clean copy
        if (is_dir($siteFolder)) {
            exec("rm -Rf $siteFolder");
        }
        exec($connectionInfo['git_command'], $output, $status);
        if ($status === 0) {
            return true;
        }
        throw new Exception("Error while cloning locally." . join(
            PHP_EOL,
            $output
        ));
        return true;
    }

  /**
   * @param $siteID
   *
   * @return mixed
   */
    public static function getSiteConnectionInfo($siteID)
    {
        $command = sprintf(
            "terminus connection:info %s.dev --format=json",
            $siteID
        );
        exec($command, $output, $status);
        return json_decode(join("", $output), true);
    }

  /**
   * @param $event
   *
   * @throws \Exception
   */
    public static function updateExampleResponses($event)
    {
        $siteID = static::getSiteID();
        $versions = static::getSupportedVersions($event);
        foreach ($versions as $versionID) {
            $command = sprintf(
                "terminus env:info %s.%s --field=domain",
                $siteID,
                $versionID
            );
            exec($command, $output, $status);
            if ($status === 0) {
                  $url = "https://" . join("", $output) . "/info.php";
                  echo print_r(get_defined_vars());
                  exit();
                  $response = $client->get(
                      $url,
                      ['query' => ['cacheBuster' => uniqid("cache_")]]
                  );
                  echo (string)$response->getBody();
            }
        }
    }
}
