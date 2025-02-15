<?php

declare(strict_types=1);

use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StringifyDefineRector::class);
};
