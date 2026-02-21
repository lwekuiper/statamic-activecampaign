<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ListFieldMappings extends Fieldtype
{
    protected $component = 'list_field_mappings';

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
        ];
    }

    public function defaultValue()
    {
        return [];
    }
}
