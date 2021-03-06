<?php
declare(strict_types=1);

namespace WShafer\PSR11MonoLog\Config;

class HandlerConfig extends AbstractServiceConfig
{
    public function getFormatter()
    {
        return $this->config['formatter'] ?? '';
    }
}
