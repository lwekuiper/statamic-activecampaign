<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Listeners;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EnsureFormConfigLocalizationsExistTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_creates_localizations_when_form_is_saved_in_pro_edition()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('sites')->andReturn(collect(['en' => null, 'nl' => 'en']));
        });

        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();

        $this->assertNotNull(FormConfig::find('contact_us', 'en'));
        $this->assertNotNull(FormConfig::find('contact_us', 'nl'));
    }

    #[Test]
    public function it_does_not_create_localizations_in_lite_edition()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();

        $this->assertNull(FormConfig::find('contact_us', 'default'));
    }

    #[Test]
    public function it_does_not_duplicate_existing_localizations()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('sites')->andReturn(collect(['en' => null, 'nl' => 'en']));
        });

        $form = Form::make('contact_us')->title('Contact Us');

        // Pre-create a config with data
        $config = FormConfig::make()->form($form)->locale('en');
        $config->emailField('email')->listIds([1]);
        $config->save();

        // Save the form, triggering the listener
        $form->save();

        // Existing config should not be overwritten
        $enConfig = FormConfig::find('contact_us', 'en');
        $this->assertEquals('email', $enConfig->emailField());
        $this->assertEquals([1], $enConfig->listIds());

        // New localization should be created
        $this->assertNotNull(FormConfig::find('contact_us', 'nl'));
    }
}
