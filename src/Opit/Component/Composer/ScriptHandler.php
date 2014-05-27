<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Component\Composer;

use Composer\Script\CommandEvent;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Description of ScriptHandler class
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class ScriptHandler
{
    public static function updateAppVersion(CommandEvent $event)
    {
        $configs = self::getConfigs($event);
        $yamlParser = new Parser();

        foreach ($configs as $config) {
            $config = self::processConfig($config);
            $realFile = $config['file'];
            $parameterKey = $config['parameter-key'];
            $versionKey = $config['app-version-key'];

            try {
                $distValues = $yamlParser->parse(file_get_contents($config['dist-file']));
                $actualValues = $yamlParser->parse(file_get_contents($realFile));

                // Checking on dist file is enough as Incenteev's parameter builder runs first
                if (array_key_exists($versionKey, $distValues[$parameterKey])) {
                    $currentVersion = $actualValues[$parameterKey][$versionKey];
                    $newVersion = $distValues[$parameterKey][$versionKey];

                    // Replace the current version with the updated from the dist file if changed
                    if ($currentVersion != $newVersion) {
                        $actualValues[$parameterKey][$versionKey] = $newVersion;

                        file_put_contents($realFile, Yaml::dump($actualValues, 99));

                        $event->getIO()->write(sprintf('<info>App version updated to "%s"</info>', $newVersion));
                    }
                }

            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
            }
        }
    }

    protected static function getConfigs(CommandEvent $event)
    {
        $extras = array_merge(array(
                'app-version-key' => 'version_number'
            ),
            $event->getComposer()->getPackage()->getExtra()
        );

        if (!isset($extras['incenteev-parameters'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.incenteev-parameters setting.');
        }

        $configs = $extras['incenteev-parameters'];

        return $configs;
    }

    protected static function processConfig(array $config)
    {
        if (empty($config['dist-file'])) {
            $config['dist-file'] = $config['file'].'.dist';
        }

        if (empty($config['parameter-key'])) {
            $config['parameter-key'] = 'parameters';
        }

        if (empty($config['app-version-key'])) {
            $config['app-version-key'] = 'version_number';
        }

        return $config;
    }
}
