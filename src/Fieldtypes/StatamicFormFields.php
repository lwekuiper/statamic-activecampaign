<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class StatamicFormFields extends Fieldtype
{
    protected $component = 'statamic_form_fields';

    protected function configFieldItems(): array
    {
        return [
            'field_filter' => [
                'display' => 'Field Filter',
                'instructions' => 'Filter the form fields by type.',
                'type' => 'select',
                'options' => [
                    null => 'None',
                    'email' => 'Email',
                    'toggle' => 'Toggle',
                ],
                'default' => null,
            ],
        ];
    }

    public function preload(): array
    {
        $fieldFilter = config('activecampaign.filter_form_fields', true)
            ? $this->config('field_filter')
            : null;

        return [
            'form' => request()->route('form')?->handle(),
            'fieldFilter' => $fieldFilter,
        ];
    }
}
