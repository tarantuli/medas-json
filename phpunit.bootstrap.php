<?php

declare(strict_types=1);

use Medas\Json\JsonPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        JsonPackage::instance(),
    ]);

    return $config;
});
