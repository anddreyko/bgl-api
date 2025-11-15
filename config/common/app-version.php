<?php

declare(strict_types=1);

use Bgl\Core\AppVersion;
use Composer\InstalledVersions;

return [
    AppVersion::class => static fn(): AppVersion => new AppVersion(
        InstalledVersions::getRootPackage()['pretty_version']
    ),
];
