<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ActiveCampaignListSelect extends Fieldtype
{
    protected $component = 'activecampaign_list_select';

    public static function handle()
    {
        return 'activecampaign_list_select';
    }
}
