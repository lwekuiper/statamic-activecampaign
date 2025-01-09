<?php

namespace Lwekuiper\StatamicActivecampaign\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicActivecampaign\Stache\FormConfigRepository;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormConfigRepository::class;
    }
}
