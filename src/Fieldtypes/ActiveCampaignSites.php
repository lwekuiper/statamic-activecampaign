<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Illuminate\Contracts\Validation\Rule as ValidationRule;
use Statamic\Fields\Fieldtype;

class ActiveCampaignSites extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'activecampaign_sites';

    public function rules(): array
    {
        return [
            $this->cannotAllHaveOriginsRule(),
            $this->originsMustBeEnabledRule(),
        ];
    }

    private function cannotAllHaveOriginsRule(): ValidationRule
    {
        return new class implements ValidationRule
        {
            public function passes($attribute, $value): bool
            {
                $enabled = collect($value)->filter->enabled;

                return $enabled->map->origin->filter()->count() !== $enabled->count();
            }

            public function message(): string
            {
                return __('At least one enabled site must not have an origin.');
            }
        };
    }

    private function originsMustBeEnabledRule(): ValidationRule
    {
        return new class implements ValidationRule
        {
            public function passes($attribute, $value): bool
            {
                $sites = collect($value)->keyBy->handle->filter->enabled;
                $origins = $sites->map->origin->filter();

                foreach ($origins as $origin) {
                    if (! $sites->has($origin)) {
                        return false;
                    }
                }

                return true;
            }

            public function message(): string
            {
                return __('An origin site must be enabled.');
            }
        };
    }
}
