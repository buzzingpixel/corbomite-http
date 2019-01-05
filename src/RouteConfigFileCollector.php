<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use LogicException;
use Composer\Package\PackageInterface;

class RouteConfigFileCollector
{
    private $composerPackages;

    /**
     * @param PackageInterface[] $composerPackages
     */
    public function __construct(array $composerPackages)
    {
        $this->composerPackages = $composerPackages;
    }

    public function __invoke(): array
    {
        if (! defined('APP_BASE_PATH')) {
            throw new LogicException('APP_BASE_PATH must be defined');
        }

        $routeConfigFiles = [];

        $appJsonPath = APP_BASE_PATH . '/composer.json';

        if (file_exists($appJsonPath)) {
            $appJson = json_decode(file_get_contents($appJsonPath), true);

            $filePath = isset($appJson['extra']['httpRouteConfigFilePath']) ?
                $appJson['extra']['httpRouteConfigFilePath'] :
                'asdf';

            $configFilePath = APP_BASE_PATH . '/' . $filePath;

            if (file_exists($configFilePath)) {
                $routeConfigFiles[] = $configFilePath;
            }
        }

        foreach ($this->composerPackages as $package) {
            $extra = $package->getExtra();

            $filePath = isset($extra['httpRouteConfigFilePath']) ?
                $extra['httpRouteConfigFilePath'] :
                'asdf';

            $configFilePath = APP_BASE_PATH .
                '/vendor/' .
                $package->getName() .
                '/' .
                $filePath;

            if (file_exists($configFilePath)) {
                $routeConfigFiles = $configFilePath;
            }
        }

        return $routeConfigFiles;
    }
}
