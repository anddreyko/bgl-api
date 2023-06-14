<?php

declare(strict_types=1);

use Doctrine\Common\Proxy\AbstractProxyFactory;

return [
    'doctrine' => [
        'proxy_generate' => AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED,
    ],
];
