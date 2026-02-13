<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
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
    public function it_lists_all_forms_with_status()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();
        tap(Form::make('form_two')->title('Form Two'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('default'));
        $formConfig->emailField('email')->consentField('consent')->listIds([1])->tagIds([1]);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(2, 'formConfigs')
            ->assertJson(['formConfigs' => [
                [
                    'title' => 'Form One',
                    'edit_url' => url('/cp/activecampaign/form_one/edit'),
                    'lists' => 1,
                    'tags' => 1,
                    'status' => 'published',
                ],
                [
                    'title' => 'Form Two',
                    'edit_url' => url('/cp/activecampaign/form_two/edit'),
                    'lists' => 0,
                    'tags' => 0,
                    'status' => 'draft',
                ],
            ]]);
    }

    #[Test]
    public function it_lists_all_forms_with_multi_site()
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

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('nl'));
        $formConfig->emailField('email')->consentField('consent')->listIds([1])->tagIds([1]);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(1, 'formConfigs')
            ->assertJson(['formConfigs' => [
                [
                    'title' => 'Form One',
                    'edit_url' => url('/cp/activecampaign/form_one/edit?site=nl'),
                    'lists' => 1,
                    'tags' => 1,
                    'status' => 'published',
                ],
            ]]);
    }

    #[Test]
    public function it_shows_inherited_forms_in_child_site()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('sites')->andReturn(collect(['en' => null, 'nl' => 'en']));
        });

        Site::setSelected('nl');

        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('en'));
        $formConfig->emailField('email')->listIds([1, 2])->tagIds([3]);
        $formConfig->save();

        // Create empty config for child site (auto-created in production via ensureLocalizationsExist)
        FormConfig::make()->form($form_one)->locale('nl')->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(1, 'formConfigs')
            ->assertJson(['formConfigs' => [
                [
                    'title' => 'Form One',
                    'edit_url' => url('/cp/activecampaign/form_one/edit?site=nl'),
                    'lists' => 2,
                    'tags' => 1,
                    'status' => 'published',
                    'delete_url' => null,
                ],
            ]]);
    }

    #[Test]
    public function it_shows_customized_status_for_child_site_with_overrides()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('sites')->andReturn(collect(['en' => null, 'nl' => 'en']));
        });

        Site::setSelected('nl');

        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();

        $enConfig = tap(FormConfig::make()->form($form_one)->locale('en'));
        $enConfig->emailField('email')->listIds([1])->tagIds([3]);
        $enConfig->save();

        $nlConfig = tap(FormConfig::make()->form($form_one)->locale('nl'));
        $nlConfig->listIds([5, 6]);
        $nlConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(1, 'formConfigs')
            ->assertJson(['formConfigs' => [
                [
                    'title' => 'Form One',
                    'lists' => 2,
                    'tags' => 1,
                    'status' => 'published',
                ],
            ]]);
    }

    #[Test]
    public function it_shows_unconfigured_forms_as_draft()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        tap(Form::make('contact')->title('Contact Form'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonCount(1, 'formConfigs')
            ->assertJson(['formConfigs' => [
                [
                    'title' => 'Contact Form',
                    'lists' => 0,
                    'tags' => 0,
                    'status' => 'draft',
                    'delete_url' => null,

                ],
            ]]);
    }
}
