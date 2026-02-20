<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Fields\Fieldtype;

class SubscriptionValue extends Fieldtype
{
    protected $component = 'subscription_value';

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
        ];
    }
}
