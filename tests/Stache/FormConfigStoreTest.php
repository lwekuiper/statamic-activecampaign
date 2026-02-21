<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Stache;

use Lwekuiper\StatamicActivecampaign\Data\FormConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Symfony\Component\Finder\SplFileInfo;

class FormConfigStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('form-configs');
    }

    #[Test]
    public function it_makes_form_config_instances_from_files()
    {
        $contents = "email_field: email\nlist_ids:\n  - 1";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('test_form::default', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_makes_form_config_instances_from_files_when_using_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $contents = "email_field: email\nlist_ids:\n  - 1";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('test_form::nl', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_uses_the_form_handle_and_locale_as_the_item_key()
    {
        $this->assertEquals(
            'test_form::default',
            $this->store->getItemKey(FormConfigFacade::make()->form('test_form')->locale('default'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $formConfig = FormConfigFacade::make()->form('test_form')
            ->emailField('email')
            ->listIds([1]);

        $this->store->save($formConfig);

        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/test_form.yaml'), $formConfig->fileContents());
    }

    #[Test]
    public function it_saves_to_disk_with_multiple_sites()
    {
        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $enFormConfig = FormConfigFacade::make()->form('test_form')->locale('en')->emailField('email')->listIds([1]);
        $nlFormConfig = FormConfigFacade::make()->form('test_form')->locale('nl')->emailField('email')->listIds([2]);

        $this->store->save($enFormConfig);
        $this->store->save($nlFormConfig);

        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/en/test_form.yaml'), $enFormConfig->fileContents());
        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $nlFormConfig->fileContents());
    }

    #[Test]
    public function it_excludes_config_yaml_from_item_filter()
    {
        $directory = Path::tidy($this->store->directory());

        $configFile = new SplFileInfo(
            $directory.'/config.yaml',
            '',
            'config.yaml'
        );
        $this->assertFalse($this->store->getItemFilter($configFile));

        $nestedConfigFile = new SplFileInfo(
            $directory.'/en/config.yaml',
            'en',
            'en/config.yaml'
        );
        $this->assertFalse($this->store->getItemFilter($nestedConfigFile));
    }

    #[Test]
    public function it_includes_yaml_form_config_files_in_item_filter()
    {
        $directory = Path::tidy($this->store->directory());

        $formFile = new SplFileInfo(
            $directory.'/contact_us.yaml',
            '',
            'contact_us.yaml'
        );
        $this->assertTrue($this->store->getItemFilter($formFile));

        $nestedFormFile = new SplFileInfo(
            $directory.'/en/contact_us.yaml',
            'en',
            'en/contact_us.yaml'
        );
        $this->assertTrue($this->store->getItemFilter($nestedFormFile));
    }

    #[Test]
    public function it_excludes_non_yaml_files_from_item_filter()
    {
        $directory = Path::tidy($this->store->directory());

        $nonYamlFile = new SplFileInfo(
            $directory.'/contact_us.txt',
            '',
            'contact_us.txt'
        );
        $this->assertFalse($this->store->getItemFilter($nonYamlFile));
    }

    #[Test]
    public function it_migrates_legacy_list_id_to_list_ids()
    {
        $contents = "email_field: email\nlist_id: 5";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/legacy_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([5], $item->listIds());
    }

    #[Test]
    public function it_migrates_legacy_tag_id_to_tag_ids()
    {
        $contents = "email_field: email\ntag_id: 3";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/legacy_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([3], $item->tagIds());
    }

    #[Test]
    public function it_does_not_overwrite_list_ids_with_legacy_list_id()
    {
        $contents = "email_field: email\nlist_ids:\n  - 10\nlist_id: 5";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/legacy_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([10], $item->listIds());
    }

    #[Test]
    public function it_does_not_overwrite_tag_ids_with_legacy_tag_id()
    {
        $contents = "email_field: email\ntag_ids:\n  - 20\ntag_id: 3";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/legacy_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([20], $item->tagIds());
    }

    #[Test]
    public function it_defaults_list_mode_to_fixed_for_existing_configs()
    {
        $contents = "email_field: email\nlist_ids:\n  - 1";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/test_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('fixed', $item->listMode());
        $this->assertEquals([], $item->listFields());
    }

    #[Test]
    public function it_reads_list_mode_and_list_fields_from_file()
    {
        $contents = "email_field: email\nlist_mode: dynamic\nlist_fields:\n  -\n    type: list_mapping\n    enabled: true\n    subscription_field: subscribe_weekly\n    list_mappings:\n      -\n        activecampaign_list_id: '10'";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/dynamic_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('dynamic', $item->listMode());
        $this->assertCount(1, $item->listFields());
        $this->assertEquals('list_mapping', $item->listFields()[0]['type']);
        $this->assertEquals('subscribe_weekly', $item->listFields()[0]['subscription_field']);
        $this->assertCount(1, $item->listFields()[0]['list_mappings']);
        $this->assertEquals('10', $item->listFields()[0]['list_mappings'][0]['activecampaign_list_id']);
    }

    #[Test]
    public function it_migrates_flat_list_fields_to_replicator_format()
    {
        $contents = "email_field: email\nlist_mode: dynamic\nlist_fields:\n  -\n    subscription_field: interests\n    activecampaign_list_id: '10'\n    subscription_value: tech\n  -\n    subscription_field: interests\n    activecampaign_list_id: '20'\n    subscription_value: sports\n  -\n    subscription_field: subscribe_weekly\n    activecampaign_list_id: '30'\n    subscription_value: ''";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/migration_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertCount(2, $item->listFields());

        // First set: interests field with two value mappings
        $this->assertEquals('list_mapping', $item->listFields()[0]['type']);
        $this->assertEquals('interests', $item->listFields()[0]['subscription_field']);
        $this->assertCount(2, $item->listFields()[0]['list_mappings']);
        $this->assertEquals('tech', $item->listFields()[0]['list_mappings'][0]['subscription_value']);
        $this->assertEquals('10', $item->listFields()[0]['list_mappings'][0]['activecampaign_list_id']);
        $this->assertEquals('sports', $item->listFields()[0]['list_mappings'][1]['subscription_value']);
        $this->assertEquals('20', $item->listFields()[0]['list_mappings'][1]['activecampaign_list_id']);

        // Second set: subscribe_weekly toggle field
        $this->assertEquals('list_mapping', $item->listFields()[1]['type']);
        $this->assertEquals('subscribe_weekly', $item->listFields()[1]['subscription_field']);
        $this->assertCount(1, $item->listFields()[1]['list_mappings']);
        $this->assertEquals('30', $item->listFields()[1]['list_mappings'][0]['activecampaign_list_id']);
        $this->assertArrayNotHasKey('subscription_value', $item->listFields()[1]['list_mappings'][0]);
    }

    #[Test]
    public function it_does_not_migrate_list_fields_already_in_replicator_format()
    {
        $contents = "email_field: email\nlist_mode: dynamic\nlist_fields:\n  -\n    type: list_mapping\n    enabled: true\n    subscription_field: interests\n    list_mappings:\n      -\n        subscription_value: tech\n        activecampaign_list_id: '10'";
        $item = $this->store->makeItemFromFile(
            Path::tidy($this->store->directory().'/replicator_form.yaml'),
            $contents
        );

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertCount(1, $item->listFields());
        $this->assertEquals('list_mapping', $item->listFields()[0]['type']);
        $this->assertEquals('interests', $item->listFields()[0]['subscription_field']);
        $this->assertCount(1, $item->listFields()[0]['list_mappings']);
    }
}
