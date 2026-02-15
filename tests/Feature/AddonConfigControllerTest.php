<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddonConfigControllerTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    protected function tearDown(): void
    {
        $addonConfig = app(AddonConfig::class);
        $path = $addonConfig->path();

        if (file_exists($path)) {
            unlink($path);
        }

        $addonConfig->fresh();

        parent::tearDown();
    }

    #[Test]
    public function it_denies_edit_access_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.edit'))
            ->assertUnauthorized();
    }

    #[Test]
    public function it_allows_edit_access_with_configure_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.edit'))
            ->assertOk();
    }

    #[Test]
    public function it_allows_edit_access_for_super_users()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)
            ->get(cp_route('activecampaign.edit'))
            ->assertOk();
    }

    #[Test]
    public function it_denies_update_access_without_permission()
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
    public function it_creates_form_configs_for_newly_enabled_sites()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr', 'name' => 'French'],
        ]);

        // Start with en and nl enabled
        $addonConfig = app(AddonConfig::class);
        $addonConfig->save(collect(['en' => null, 'nl' => 'en']));

        $form = tap(Form::make('contact')->title('Contact'))->save();

        $enConfig = FormConfig::make()->form($form)->locale('en');
        $enConfig->emailField('email');
        $enConfig->save();

        $nlConfig = FormConfig::make()->form($form)->locale('nl');
        $nlConfig->save();

        $user = tap(User::make()->makeSuper())->save();

        // Enable fr site
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'enabled' => true, 'origin' => null],
                    ['handle' => 'nl', 'enabled' => true, 'origin' => 'en'],
                    ['handle' => 'fr', 'enabled' => true, 'origin' => 'en'],
                ],
            ])
            ->assertNoContent();

        // Verify fr form config was created
        $frConfig = FormConfig::find('contact', 'fr');
        $this->assertNotNull($frConfig);
    }

    #[Test]
    public function it_deletes_form_configs_for_disabled_sites()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        // Start with both enabled
        $addonConfig = app(AddonConfig::class);
        $addonConfig->save(collect(['en' => null, 'nl' => 'en']));

        $form = tap(Form::make('contact')->title('Contact'))->save();

        FormConfig::make()->form($form)->locale('en')->emailField('email')->save();
        FormConfig::make()->form($form)->locale('nl')->save();

        $this->assertNotNull(FormConfig::find('contact', 'nl'));

        $user = tap(User::make()->makeSuper())->save();

        // Disable nl site
        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'enabled' => true, 'origin' => null],
                    ['handle' => 'nl', 'enabled' => false, 'origin' => null],
                ],
            ])
            ->assertNoContent();

        // Verify nl form config was deleted
        $this->assertNull(FormConfig::find('contact', 'nl'));
    }
}
