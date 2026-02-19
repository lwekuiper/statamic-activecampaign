<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Listeners;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\AddonConfig as AddonConfigFacade;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Listeners\EnsureFormConfigLocalizationsExist;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSaved;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EnsureFormConfigLocalizationsExistTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private string $configPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->configPath = app(AddonConfig::class)->path();

        $directory = dirname($this->configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->configPath)) {
            @unlink($this->configPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_creates_form_configs_for_enabled_sites_in_pro_edition()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        // Create form without saving to avoid triggering the listener via FormSaved event
        $form = Form::make('contact_us')->title('Contact Us');

        $this->assertNull(FormConfig::find('contact_us', 'en'));
        $this->assertNull(FormConfig::find('contact_us', 'nl'));

        $listener = new EnsureFormConfigLocalizationsExist;
        $listener->handle(new FormSaved($form));

        $this->assertNotNull(FormConfig::find('contact_us', 'en'));
        $this->assertNotNull(FormConfig::find('contact_us', 'nl'));
    }

    #[Test]
    public function it_does_nothing_in_lite_edition()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        // Do NOT call setProEdition() — defaults to lite
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();

        $listener = new EnsureFormConfigLocalizationsExist;
        $listener->handle(new FormSaved($form));

        $this->assertNull(FormConfig::find('contact_us', 'en'));
        $this->assertNull(FormConfig::find('contact_us', 'nl'));
    }

    #[Test]
    public function it_does_not_duplicate_existing_configs()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        // Create form without saving to avoid triggering the listener via FormSaved event
        $form = Form::make('contact_us')->title('Contact Us');

        // Pre-create the English config with specific data
        FormConfig::make()->form('contact_us')->locale('en')->emailField('email')->listIds([1])->save();

        $this->assertNotNull(FormConfig::find('contact_us', 'en'));
        $this->assertNull(FormConfig::find('contact_us', 'nl'));

        $listener = new EnsureFormConfigLocalizationsExist;
        $listener->handle(new FormSaved($form));

        // The existing English config should remain unchanged
        $enConfig = FormConfig::find('contact_us', 'en');
        $this->assertEquals('email', $enConfig->emailField());
        $this->assertEquals([1], $enConfig->listIds());

        // The Dutch config should be created
        $this->assertNotNull(FormConfig::find('contact_us', 'nl'));

        // Verify total count -- should be 2, not 3
        $allConfigs = FormConfig::whereForm('contact_us');
        $this->assertCount(2, $allConfigs);
    }

    #[Test]
    public function it_only_creates_configs_for_enabled_sites()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr', 'name' => 'French'],
        ]);

        // Only enable 'en' and 'nl', not 'fr'
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        // Create form without saving to avoid triggering the listener via FormSaved event
        $form = Form::make('contact_us')->title('Contact Us');

        $listener = new EnsureFormConfigLocalizationsExist;
        $listener->handle(new FormSaved($form));

        $this->assertNotNull(FormConfig::find('contact_us', 'en'));
        $this->assertNotNull(FormConfig::find('contact_us', 'nl'));
        $this->assertNull(FormConfig::find('contact_us', 'fr'));
    }
}
