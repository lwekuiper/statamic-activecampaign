<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Statamic\Support\Arr;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Addon;
use PHPUnit\Framework\Attributes\Test;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class ViewFormConfigListingTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.forms.forms', __DIR__.'/../__fixtures__/dev-null/resources/forms');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        tap(Form::make('test'))->save();
        $this->assertCount(1, Form::all());

        $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.index'))
            ->assertUnauthorized();

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function it_lists_form_configs()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $this->assertCount(0, Form::all());

        $one = tap(Form::make('test_one'))->save();
        $two = tap(Form::make('test_two'))->save();

        $formConfig = tap(FormConfig::make()->form($one)->locale('default'));
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertViewHas('forms', function ($forms) {
                return $forms->count() === 2;
            })
            ->assertViewHas('forms', function ($forms) {
                return Arr::get($forms, '0.id') === 'test_one'
                    && Arr::get($forms, '0.edit_url') === url('/cp/activecampaign/test_one/edit')
                    && Arr::get($forms, '0.list_id') === 1
                    && Arr::get($forms, '0.tag_id') === 1
                    && Arr::get($forms, '1.id') === 'test_two'
                    && Arr::get($forms, '1.edit_url') === url('/cp/activecampaign/test_two/edit');
            });
    }

    #[Test]
    public function it_lists_form_configs_with_multi_site()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);
        $user = User::make()->assignRole('test')->save();

        Site::setSelected('nl');

        $one = tap(Form::make('test_one'))->save();
        $two = tap(Form::make('test_two'))->save();

        $formConfig = tap(FormConfig::make()->form($one)->locale('nl'));
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertViewHas('forms', fn ($forms) => $forms->count() === 2)
            ->assertViewHas('forms', function ($forms) {
                return Arr::get($forms, '0.id') === 'test_one'
                    && Arr::get($forms, '0.edit_url') === url('/cp/activecampaign/test_one/edit?site=nl')
                    && Arr::get($forms, '0.list_id') === 1
                    && Arr::get($forms, '0.tag_id') === 1
                    && Arr::get($forms, '1.id') === 'test_two'
                    && Arr::get($forms, '1.edit_url') === url('/cp/activecampaign/test_two/edit?site=nl')
                    && Arr::get($forms, '1.list_id') === null
                    && Arr::get($forms, '1.tag_id') === null;
            });
    }
}
