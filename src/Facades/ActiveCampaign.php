<?php

namespace Lwekuiper\StatamicActivecampaign\Facades;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method PendingRequest makeRequest()
 */
class ActiveCampaign extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lwekuiper\StatamicActivecampaign\Services\ActiveCampaignService::class;
    }
}
