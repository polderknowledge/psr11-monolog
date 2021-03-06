<?php
declare(strict_types=1);

namespace WShafer\PSR11MonoLog\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use WShafer\PSR11MonoLog\ContainerAwareInterface;
use WShafer\PSR11MonoLog\ContainerTrait;
use WShafer\PSR11MonoLog\FactoryInterface;

class StreamHandlerFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    /** @var ContainerInterface */
    protected $container;

    public function __invoke(array $options)
    {
        $stream         = $this->getStream($options['stream'] ?? null);

        $level          = (int)     ($options['level']          ?? Logger::DEBUG);
        $bubble         = (boolean) ($options['bubble']         ?? true);
        $filePermission = (int)     ($options['filePermission'] ?? 0644);
        $useLocking     = (boolean) ($options['useLocking']     ?? true);

        return new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);
    }

    protected function getStream($stream)
    {
        if ($this->container->has($stream)) {
            return $this->container->get($stream);
        }

        if (is_resource($stream)) {
            return $stream;
        }

        return (string) $stream;
    }
}
