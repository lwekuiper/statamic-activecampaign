<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\AddonConfig as AddonConfigFacade;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddonConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    private string $configPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->configPath = app(AddonConfig::class)->path();

        // Ensure the directory exists for writing config files in tests
        $directory = dirname($this->configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function tearDown(): void
    {
        // Clean up config file after each test
        if (file_exists($this->configPath)) {
            @unlink($this->configPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_redirects_to_index_on_single_site()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.edit'))
            ->assertRedirect(cp_route('activecampaign.index'));
    }

    #[Test]
    public function it_redirects_to_index_when_edition_is_lite_with_multi_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.edit'))
            ->assertRedirect(cp_route('activecampaign.index'));
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.edit'))
            ->assertUnauthorized();
    }

    #[Test]
    public function it_returns_site_data_in_values()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.edit'))
            ->assertOk();

        $values = $response->json('values');

        $this->assertArrayHasKey('sites', $values);
        $this->assertIsArray($values['sites']);

        $enSite = collect($values['sites'])->firstWhere('handle', 'en');
        $this->assertNotNull($enSite);
        $this->assertEquals('en', $enSite['handle']);
    }

    #[Test]
    public function it_shows_sites_section_on_multi_site_pro()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.edit'))
            ->assertOk();

        $values = $response->json('values');
        $this->assertArrayHasKey('sites', $values);

        $blueprint = $response->json('blueprint');
        $fields = collect($blueprint['tabs'])->flatMap(fn ($tab) => collect($tab['sections'])->flatMap(fn ($section) => collect($section['fields'])));
        $this->assertNotNull($fields->firstWhere('handle', 'sites'));
    }

    #[Test]
    public function it_auto_enables_default_site_on_single_site_update()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [])
            ->assertNoContent();

        app(\Lwekuiper\StatamicActivecampaign\Data\AddonConfig::class)->fresh();

        $this->assertTrue(AddonConfigFacade::isEnabled('default'));
    }

    #[Test]
    public function it_returns_multi_site_configuration()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        // Save config with en enabled and nl inheriting from en
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.edit'))
            ->assertOk();

        $values = $response->json('values');
        $sites = collect($values['sites']);

        $enSite = $sites->firstWhere('handle', 'en');
        $nlSite = $sites->firstWhere('handle', 'nl');

        $this->assertTrue($enSite['enabled']);
        $this->assertNull($enSite['origin']);
        $this->assertTrue($nlSite['enabled']);
        $this->assertEquals('en', $nlSite['origin']);
    }

    #[Test]
    public function it_denies_update_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [],
            ])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_saves_site_configuration()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'name' => 'English', 'enabled' => true, 'origin' => null],
                    ['handle' => 'nl', 'name' => 'Dutch', 'enabled' => true, 'origin' => 'en'],
                ],
            ])
            ->assertNoContent();

        app(AddonConfig::class)->fresh();

        $this->assertTrue(AddonConfigFacade::isEnabled('en'));
        $this->assertTrue(AddonConfigFacade::isEnabled('nl'));
        $this->assertNull(AddonConfigFacade::originFor('en'));
        $this->assertEquals('en', AddonConfigFacade::originFor('nl'));
    }

    #[Test]
    public function it_creates_form_configs_for_newly_enabled_sites()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        // Start with only 'en' enabled
        AddonConfigFacade::save(collect(['en' => null]));
        app(AddonConfig::class)->fresh();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();
        FormConfig::make()->form($form)->locale('en')->emailField('email')->save();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        // Enable 'nl' site
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'name' => 'English', 'enabled' => true, 'origin' => null],
                    ['handle' => 'nl', 'name' => 'Dutch', 'enabled' => true, 'origin' => 'en'],
                ],
            ])
            ->assertNoContent();

        // FormConfig should now exist for 'nl'
        $this->assertNotNull(FormConfig::find('test_form', 'nl'));
    }

    #[Test]
    public function it_deletes_form_configs_for_disabled_sites()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        // Start with both sites enabled
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();
        FormConfig::make()->form($form)->locale('en')->emailField('email')->save();
        FormConfig::make()->form($form)->locale('nl')->emailField('email')->save();

        $this->assertNotNull(FormConfig::find('test_form', 'nl'));

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        // Disable 'nl' site
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'name' => 'English', 'enabled' => true, 'origin' => null],
                    ['handle' => 'nl', 'name' => 'Dutch', 'enabled' => false, 'origin' => null],
                ],
            ])
            ->assertNoContent();

        // FormConfig for 'nl' should be deleted
        $this->assertNull(FormConfig::find('test_form', 'nl'));
        // FormConfig for 'en' should still exist
        $this->assertNotNull(FormConfig::find('test_form', 'en'));
    }

    #[Test]
    public function super_user_can_access_edit_page()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.edit'))
            ->assertOk();
    }

    #[Test]
    public function super_user_can_update_config()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [])
            ->assertNoContent();

        app(\Lwekuiper\StatamicActivecampaign\Data\AddonConfig::class)->fresh();

        $this->assertTrue(AddonConfigFacade::isEnabled('default'));
    }
}
