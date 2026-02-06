<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Statamic\Support\Arr;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
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

        $this->actingAs($user)
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

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();
        $form_two = tap(Form::make('form_two')->title('Form Two'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('default'));
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(2, 'formConfigs')
            ->assertJson(['formConfigs' => [
                ['title' => 'Form One', 'edit_url' => url('/cp/activecampaign/form_one/edit'), 'list_id' => 1, 'tag_id' => 1],
                ['title' => 'Form Two', 'edit_url' => url('/cp/activecampaign/form_two/edit')],
            ]])
            ->assertJsonMissing(['formConfigs' => [1 => ['list_id' => 1]]]);
    }

    #[Test]
    public function it_lists_form_configs_with_multi_site()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        Site::setSelected('nl');

        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();
        $form_two = tap(Form::make('form_two')->title('Form Two'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('nl'));
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(2, 'formConfigs')
            ->assertJson(['formConfigs' => [
                ['title' => 'Form One', 'edit_url' => url('/cp/activecampaign/form_one/edit?site=nl'), 'list_id' => 1, 'tag_id' => 1],
                ['title' => 'Form Two', 'edit_url' => url('/cp/activecampaign/form_two/edit?site=nl')],
            ]])
            ->assertJsonMissing(['formConfigs' => [1 => ['list_id' => 1]]]);
    }
}
