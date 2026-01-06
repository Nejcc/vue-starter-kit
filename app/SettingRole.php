<?php

declare(strict_types=1);

namespace App;

enum SettingRole: string
{
    case System = 'system';
    case User = 'user';
    case Plugin = 'plugin';
}
