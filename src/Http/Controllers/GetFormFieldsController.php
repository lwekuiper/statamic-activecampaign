<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Fields\Field;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\Controller;

class GetFormFieldsController extends Controller
{
    public function __invoke(Request $request, Form $form): array
    {
        $fields = $form->fields();

        if ($filter = $request->query('filter')) {
            $fields = $fields->filter(fn (Field $field) => match ($filter) {
                'email' => $field->type() === 'text' && $field->config()['input_type'] === 'email',
                'toggle' => $field->type() === 'toggle',
                default => true,
            });
        }

        return $fields
            ->map(fn (Field $field, string $handle) => [
                'id' => $handle,
                'label' => $field->display(),
            ])
            ->values()
            ->all();
    }
}
