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
            ->filter(fn (Field $field) => in_array($field->type(), self::OPTION_FIELD_TYPES)
                && ! empty($field->config()['options'] ?? []))
            ->map(fn (Field $field) => collect($field->config()['options'])
                ->map(fn ($label, $value) => [
                    'id' => (string) $value,
                    'label' => $label,
                ])
                ->values()
                ->all())
            ->all();
    }
}
