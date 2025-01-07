<?php

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Statamic\Fields\Field;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\Controller;

class GetFormFieldsController extends Controller
{
    public function __invoke(Form $form): array
    {
        return $form->fields()
            ->map(fn (Field $field, string $handle) => [
                'id' => $handle,
                'label' => $field->display()
            ])
            ->values()
            ->all();
    }
}
