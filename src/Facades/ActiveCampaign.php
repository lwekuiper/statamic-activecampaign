<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicActivecampaign\Connectors\ActiveCampaignConnector;

class ActiveCampaign extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActiveCampaignConnector::class;
    }
}
