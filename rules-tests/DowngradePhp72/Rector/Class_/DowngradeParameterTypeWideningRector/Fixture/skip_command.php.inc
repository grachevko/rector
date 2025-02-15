<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\AnyService;
use Symfony\Component\Console\Command\Command;

final class SkipCommand extends Command
{
    /**
     * @var AnyService
     */
    private $anyService;

    public function __construct(AnyService $anyService)
    {
        $this->anyService = $anyService;

        parent::__construct();
    }
}
