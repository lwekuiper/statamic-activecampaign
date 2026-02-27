<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Statamic\Fields\Field;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\Controller;

class GetFormFieldOptionsController extends Controller
{
    private const OPTION_FIELD_TYPES = ['select', 'radio', 'checkboxes', 'button_group'];

    public function __invoke(Form $form): array
    {
        return $form->fields()
            ->filter(fn (Field $field) => in_array($field->type(), self::OPTION_FIELD_TYPES))
            ->map(fn (Field $field) => $field->fieldtype()->preload()['options'] ?? [])
            ->filter(fn (array $options) => ! empty($options))
            ->map(fn (array $options) => collect($options)
                ->map(fn (array $option) => [
                    'id' => (string) $option['value'],
                    'label' => $option['label'],
                ])
                ->all())
            ->all();
    }
}
