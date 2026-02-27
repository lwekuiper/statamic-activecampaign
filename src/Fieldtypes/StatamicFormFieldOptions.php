<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class StatamicFormFieldOptions extends Fieldtype
{
    protected $component = 'statamic_form_field_options';

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
        ];
    }
}
