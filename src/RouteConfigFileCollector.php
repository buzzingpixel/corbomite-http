<?php
declare(strict_types=1);

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
            $configFilePath = APP_BASE_PATH .
                '/' .
                $appJson['extra']['httpRouteConfigFilePath'] ?? 'asdf';

            if (file_exists($configFilePath)) {
                $routeConfigFiles[] = $configFilePath;
            }
        }

        foreach ($this->composerPackages as $package) {
            $extra = $package->getExtra();

            $configFilePath = APP_BASE_PATH .
                '/vendor/' .
                $package->getName() .
                '/' .
                ($extra['httpRouteConfigFilePath'] ?? 'asdf');

            if (file_exists($configFilePath)) {
                $routeConfigFiles = $configFilePath;
            }
        }

        return $routeConfigFiles;
    }
}
