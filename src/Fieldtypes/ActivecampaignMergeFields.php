<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ActiveCampaignMergeFields extends Fieldtype
{
    protected $component = 'activecampaign_merge_fields';

    public static function handle()
    {
        return 'activecampaign_merge_fields';
    }
}
