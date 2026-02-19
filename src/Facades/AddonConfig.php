<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicActivecampaign\Data\AddonConfig as AddonConfigData;

class AddonConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AddonConfigData::class;
    }
}
