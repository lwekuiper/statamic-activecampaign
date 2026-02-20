<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Data;

use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Forms\Form;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasOrigin;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Lwekuiper\StatamicActivecampaign\Facades;

class FormConfig implements Localization
{
    use ContainsData;
    use ExistsAsFile;
    use FluentlyGetsAndSets;
    use HasOrigin;

    protected $form;
    protected $locale;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function form($form = null)
    {
        return $this->fluentlyGetOrSet('form')
            ->getter(function ($form) {
                return $form instanceof Form ? $form : FormFacade::find($form);
            })
            ->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->handle().'::'.$this->locale();
    }

    public function handle()
    {
        return $this->form instanceof Form ? $this->form->handle() : $this->form;
    }

    public function title()
    {
        return $this->form()->title();
    }

    public function emailField($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('email_field');
        }

        return $this->set('email_field', $value);
    }

    public function consentField($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('consent_field');
        }

        return $this->set('consent_field', $value);
    }

    public function listMode($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('list_mode', 'fixed');
        }

        return $this->set('list_mode', $value);
    }

    public function listIds($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('list_ids', []);
        }

        return $this->set('list_ids', $value);
    }

    public function listFields($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('list_fields', []);
        }

        return $this->set('list_fields', $value);
    }

    public function tagIds($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('tag_ids', []);
        }

        return $this->set('tag_ids', $value);
    }

    public function mergeFields($value = null)
    {
        if (func_num_args() === 0) {
            return $this->get('merge_fields', []);
        }

        return $this->set('merge_fields', $value);
    }

    public function origin($origin = null)
    {
        if (func_num_args() > 0) {
            throw new \Exception('Origin is determined by site configuration.');
        }

        if (! $this->locale()) {
            return null;
        }

        return $this->getOriginByString(
            app(AddonConfig::class)->originFor($this->locale())
        );
    }

    public function getOriginByString($origin)
    {
        if (! $origin) {
            return null;
        }

        return Facades\FormConfig::find($this->handle(), $origin);
    }

    public function path()
    {
        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store('form-configs')->directory(), '/'),
            Site::multiEnabled() ? $this->locale().'/' : '',
            $this->handle(),
            'yaml',
        ]);
    }

    public function editUrl()
    {
        return $this->cpUrl('activecampaign.form-config.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('activecampaign.form-config.update');
    }

    public function deleteUrl()
    {
        return $this->cpUrl('activecampaign.form-config.destroy');
    }

    protected function cpUrl($route)
    {
        $params = [$this->handle()];

        if (Site::multiEnabled()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
    }

    public function save()
    {
        return Facades\FormConfig::save($this);
    }

    public function delete()
    {
        return Facades\FormConfig::delete($this);
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function fileData()
    {
        return $this->data()->all();
    }

    protected function shouldRemoveNullsFromFileData()
    {
        return ! $this->hasOrigin();
    }
}
