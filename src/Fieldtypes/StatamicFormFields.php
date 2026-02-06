<?php

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class StatamicFormFields extends Fieldtype
{
    protected $component = 'statamic_form_fields';

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
        ];
    }
}
