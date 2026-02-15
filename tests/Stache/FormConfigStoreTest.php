<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Stache;

use Lwekuiper\StatamicActivecampaign\Data\FormConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

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

        $enFormConfig = FormConfigFacade::make()->form('test_form')->locale('en')->emailField('email')->listIds(['1']);
        $nlFormConfig = FormConfigFacade::make()->form('test_form')->locale('nl')->emailField('email')->listIds(['2']);

        $this->store->save($enFormConfig);
        $this->store->save($nlFormConfig);

        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/en/test_form.yaml'), $enFormConfig->fileContents());
        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $nlFormConfig->fileContents());
    }

    #[Test]
    public function it_migrates_legacy_list_id_to_list_ids()
    {
        $contents = "email_field: email\nlist_id: 1";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([1], $item->listIds());
    }

    #[Test]
    public function it_migrates_legacy_tag_id_to_tag_ids()
    {
        $contents = "email_field: email\ntag_id: 5";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals([5], $item->tagIds());
    }

    #[Test]
    public function it_prefers_new_keys_over_legacy_keys()
    {
        $contents = "email_field: email\nlist_id: 1\nlist_ids:\n  - 2\n  - 3\ntag_id: 5\ntag_ids:\n  - 6\n  - 7";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertEquals([2, 3], $item->listIds());
        $this->assertEquals([6, 7], $item->tagIds());
    }
}
