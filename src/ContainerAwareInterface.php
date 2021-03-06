<?php
declare(strict_types=1);

namespace WShafer\PSR11MonoLog;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function getContainer() : ContainerInterface;
    public function setContainer(ContainerInterface $container);
}
