<?php

declare(strict_types=1);

namespace App\Service\Common;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContainerParametersHelperService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function getApplicationRootDir(): string
    {
        return $this->parameterBag->get('kernel.project_dir');
    }

    public function getCacheDir(): string
    {
        return $this->parameterBag->get('kernel.cache_dir');
    }
}
