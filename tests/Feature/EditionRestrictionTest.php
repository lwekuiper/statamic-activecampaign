<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EditionRestrictionTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.forms.forms', __DIR__.'/../__fixtures__/dev-null/resources/forms');
    }

    private function setUpMultiSite(): void
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);
    }

    private function createUserWithPermissions(): \Statamic\Contracts\Auth\User
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);

        return User::make()->assignRole('test')->save();
    }

    private function createUserWithSingleSitePermissions(): \Statamic\Contracts\Auth\User
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'configure forms',
        ]]);

        return User::make()->assignRole('test')->save();
    }

    #[Test]
    public function lite_edition_uses_default_site_for_index(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        Site::setSelected('nl');

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfigDefault = FormConfig::make()->form($form)->locale('en');
        $formConfigDefault->emailField('email')->listIds([1]);
        $formConfigDefault->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index', ['site' => 'nl']))
            ->assertOk()
            ->assertJson(['formConfigs' => [
                ['title' => 'Test Form', 'lists' => 1],
            ]]);
    }

    #[Test]
    public function lite_edition_uses_default_site_for_edit(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfigDefault = FormConfig::make()->form($form)->locale('en');
        $formConfigDefault->emailField('email')->listIds([1]);
        $formConfigDefault->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form', 'site' => 'nl']))
            ->assertOk()
            ->assertJson(['values' => [
                'list_ids' => [1],
            ]]);
    }

    #[Test]
    public function lite_edition_does_not_include_localizations_in_index(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJsonMissingPath('localizations')
            ->assertJsonMissingPath('locale');
    }

    #[Test]
    public function lite_edition_does_not_include_localizations_in_edit(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form']))
            ->assertOk()
            ->assertJsonMissingPath('localizations')
            ->assertJsonMissingPath('locale');
    }

    #[Test]
    public function pro_edition_respects_site_param_for_index(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfigDefault = FormConfig::make()->form($form)->locale('en');
        $formConfigDefault->emailField('email')->listIds([1]);
        $formConfigDefault->save();

        $formConfigNl = FormConfig::make()->form($form)->locale('nl');
        $formConfigNl->emailField('email')->listIds([2]);
        $formConfigNl->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index', ['site' => 'nl']))
            ->assertOk()
            ->assertJson(['formConfigs' => [
                ['title' => 'Test Form', 'lists' => 1],
            ]]);

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index', ['site' => 'en']))
            ->assertOk()
            ->assertJson(['formConfigs' => [
                ['title' => 'Test Form', 'lists' => 1],
            ]]);
    }

    #[Test]
    public function pro_edition_respects_site_param_for_edit(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfigDefault = FormConfig::make()->form($form)->locale('en');
        $formConfigDefault->emailField('email')->listIds([1]);
        $formConfigDefault->save();

        $formConfigNl = FormConfig::make()->form($form)->locale('nl');
        $formConfigNl->emailField('email')->listIds([2]);
        $formConfigNl->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form', 'site' => 'nl']))
            ->assertOk()
            ->assertJson(['values' => [
                'list_ids' => [2],
            ]]);

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form', 'site' => 'en']))
            ->assertOk()
            ->assertJson(['values' => [
                'list_ids' => [1],
            ]]);
    }

    #[Test]
    public function pro_edition_includes_localizations_in_index(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index', ['site' => 'nl']))
            ->assertOk()
            ->assertJsonPath('locale', 'nl')
            ->assertJsonCount(2, 'localizations')
            ->assertJson(['localizations' => [
                ['handle' => 'en', 'name' => 'English', 'active' => false],
                ['handle' => 'nl', 'name' => 'Dutch', 'active' => true],
            ]]);
    }

    #[Test]
    public function pro_edition_includes_localizations_in_edit(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form', 'site' => 'en']))
            ->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonCount(2, 'localizations')
            ->assertJson(['localizations' => [
                ['handle' => 'en', 'name' => 'English', 'active' => true],
                ['handle' => 'nl', 'name' => 'Dutch', 'active' => false],
            ]]);
    }

    #[Test]
    public function lite_edition_uses_default_site_for_form_config_lookup(): void
    {
        $this->setUpMultiSite();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfigDefault = FormConfig::make()->form($form)->locale('en');
        $formConfigDefault->emailField('email')->listIds([1]);
        $formConfigDefault->save();

        $formConfigNl = FormConfig::make()->form($form)->locale('nl');
        $formConfigNl->emailField('email')->listIds([2]);
        $formConfigNl->save();

        $listener = new \Lwekuiper\StatamicActivecampaign\Listeners\AddFromSubmission();
        $submission = \Mockery::mock(\Statamic\Forms\Submission::class);
        $submission->shouldReceive('form')->andReturn($form);
        $submission->shouldReceive('data')->andReturn(['email' => 'test@example.com']);

        $result = $listener->hasFormConfig($submission);

        $this->assertTrue($result);
        $this->assertEquals('test@example.com', $listener->getEmail());
    }

    #[Test]
    public function pro_edition_uses_url_based_site_for_form_config_lookup(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        // Only create config for nl site, not default en
        $formConfigNl = FormConfig::make()->form($form)->locale('nl');
        $formConfigNl->emailField('email_address')->listIds([2]);
        $formConfigNl->save();

        // Set the previous URL to the Dutch site
        request()->headers->set('referer', 'http://localhost/nl/contact');

        $listener = new \Lwekuiper\StatamicActivecampaign\Listeners\AddFromSubmission();
        $submission = \Mockery::mock(\Statamic\Forms\Submission::class);
        $submission->shouldReceive('form')->andReturn($form);
        $submission->shouldReceive('data')->andReturn(['email_address' => 'test@example.com']);

        $result = $listener->hasFormConfig($submission);

        // Pro edition should find the nl config via URL detection
        $this->assertTrue($result);
        $this->assertEquals('test@example.com', $listener->getEmail());
    }

    // Single-site tests (no multisite configured)

    #[Test]
    public function lite_edition_works_with_single_site_index(): void
    {
        $user = $this->createUserWithSingleSitePermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJson(['formConfigs' => [
                ['title' => 'Test Form', 'lists' => 1],
            ]])
            ->assertJsonMissingPath('localizations')
            ->assertJsonMissingPath('locale');
    }

    #[Test]
    public function lite_edition_works_with_single_site_edit(): void
    {
        $user = $this->createUserWithSingleSitePermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form']))
            ->assertOk()
            ->assertJson(['values' => [
                'list_ids' => [1],
            ]])
            ->assertJsonMissingPath('localizations')
            ->assertJsonMissingPath('locale');
    }

    #[Test]
    public function pro_edition_includes_localizations_with_single_site_index(): void
    {
        $this->setProEdition();
        $user = $this->createUserWithSingleSitePermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.index'))
            ->assertOk()
            ->assertJson(['formConfigs' => [
                ['title' => 'Test Form', 'lists' => 1],
            ]])
            ->assertJsonPath('locale', 'default')
            ->assertJsonCount(1, 'localizations')
            ->assertJson(['localizations' => [
                ['handle' => 'default', 'active' => true],
            ]]);
    }

    #[Test]
    public function pro_edition_includes_localizations_with_single_site_edit(): void
    {
        $this->setProEdition();
        $user = $this->createUserWithSingleSitePermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-config.edit',['form' => 'test_form']))
            ->assertOk()
            ->assertJson(['values' => [
                'list_ids' => [1],
            ]])
            ->assertJsonPath('locale', 'default')
            ->assertJsonCount(1, 'localizations')
            ->assertJson(['localizations' => [
                ['handle' => 'default', 'active' => true],
            ]]);
    }

    // Update/Destroy edition restriction tests

    #[Test]
    public function lite_edition_ignores_site_param_on_update(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        // POST to update with ?site=nl, but lite should save to default site (en)
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.form-config.update',['form' => 'test_form', 'site' => 'nl']), [
                'email_field' => 'email',
                'list_ids' => [1],
                'consent_field' => null,
                'tag_ids' => [],
                'merge_fields' => [],
            ])
            ->assertSuccessful();

        // Config should be saved to default site (en), not nl
        $this->assertNotNull(FormConfig::find('test_form', 'en'));
        $this->assertNull(FormConfig::find('test_form', 'nl'));
    }

    #[Test]
    public function lite_edition_ignores_site_param_on_destroy(): void
    {
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        // Create config for default site
        $formConfig = FormConfig::make()->form($form)->locale('en');
        $formConfig->emailField('email')->listIds([1]);
        $formConfig->save();

        // DELETE with ?site=nl, but lite should delete from default site (en)
        $this->actingAs($user)
            ->delete(cp_route('activecampaign.form-config.destroy',['form' => 'test_form', 'site' => 'nl']))
            ->assertNoContent();

        // Default site config should be deleted (lite ignores nl param)
        $this->assertNull(FormConfig::find('test_form', 'en'));
    }

    #[Test]
    public function pro_edition_respects_site_param_on_update(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        // POST to update with ?site=nl
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.form-config.update',['form' => 'test_form', 'site' => 'nl']), [
                'email_field' => 'email',
                'list_ids' => [2],
                'consent_field' => null,
                'tag_ids' => [],
                'merge_fields' => [],
            ])
            ->assertSuccessful();

        // Config should be saved to nl site
        $formConfig = FormConfig::find('test_form', 'nl');
        $this->assertNotNull($formConfig);
        $this->assertEquals([2], $formConfig->listIds());

        // en site should have an auto-created empty config
        $enConfig = FormConfig::find('test_form', 'en');
        $this->assertNotNull($enConfig);
        $this->assertTrue($enConfig->data()->isEmpty());
    }

    #[Test]
    public function pro_edition_respects_site_param_on_destroy(): void
    {
        $this->setProEdition();
        $this->setUpMultiSite();
        $user = $this->createUserWithPermissions();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        // Create configs for both sites
        $formConfigEn = FormConfig::make()->form($form)->locale('en');
        $formConfigEn->emailField('email')->listIds([1]);
        $formConfigEn->save();

        $formConfigNl = FormConfig::make()->form($form)->locale('nl');
        $formConfigNl->emailField('email')->listIds([2]);
        $formConfigNl->save();

        // DELETE with ?site=nl
        $this->actingAs($user)
            ->delete(cp_route('activecampaign.form-config.destroy',['form' => 'test_form', 'site' => 'nl']))
            ->assertNoContent();

        // Only nl config should be deleted, en should remain
        $this->assertNull(FormConfig::find('test_form', 'nl'));
        $this->assertNotNull(FormConfig::find('test_form', 'en'));
    }
}
