<?php

declare(strict_types=1);

namespace App\Carting;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/** Provides the standalone Symfony kernel used to build, test, and debug Carting independently. */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
