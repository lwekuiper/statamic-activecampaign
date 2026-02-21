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
                    '' => 'None',
                    'email' => 'Email',
                    'toggle' => 'Toggle',
                ],
                'default' => '',
            ],
            'show_field_type' => [
                'display' => 'Show Field Type',
                'instructions' => 'Show the field type next to the field label.',
                'type' => 'toggle',
                'default' => false,
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
            'fieldFilter' => $this->config('field_filter') ?: null,
            'showFieldType' => $this->config('show_field_type', false),
        ];
    }
}
