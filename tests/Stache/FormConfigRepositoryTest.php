<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Stache;

use Lwekuiper\StatamicActivecampaign\Data\FormConfig;
use Lwekuiper\StatamicActivecampaign\Data\FormConfigCollection;
use Lwekuiper\StatamicActivecampaign\Exceptions\FormConfigNotFoundException;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicActivecampaign\Stache\FormConfigRepository;
use Lwekuiper\StatamicActivecampaign\Stache\FormConfigStore;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\Stache;

class FormConfigRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    private function setUpSingleSite()
    {
        $stache = (new Stache)->sites(['default']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/__fixtures__/resources/activecampaign';
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);
    }

    private function setUpMultiSite()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'nl' => ['url' => '/nl/'],
        ]);

        $stache = (new Stache)->sites(['en', 'nl']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/__fixtures__/resources/activecampaign-multisite';
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);
    }

    #[Test]
    public function it_gets_all_form_configs_with_single_site()
    {
        $this->setUpSingleSite();

        $formConfigs = $this->repo->all();

        $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
        $this->assertCount(2, $formConfigs);
        $this->assertEveryItemIsInstanceOf(FormConfig::class, $formConfigs);

        $ordered = $formConfigs->sortBy->path()->values();
        $this->assertEquals(['contact_us::default', 'sign_up::default'], $ordered->map->id()->all());
        $this->assertEquals(['contact_us', 'sign_up'], $ordered->map->handle()->all());
    }

    #[Test]
    public function it_gets_all_form_configs_with_multi_site()
    {
        $this->setUpMultiSite();

        $formConfigs = $this->repo->all();

        $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
        $this->assertCount(4, $formConfigs);
        $this->assertEveryItemIsInstanceOf(FormConfig::class, $formConfigs);

        $ordered = $formConfigs->sortBy->path()->values();
        $this->assertEquals(['contact_us::en', 'sign_up::en', 'contact_us::nl', 'sign_up::nl'], $ordered->map->id()->all());
        $this->assertEquals(['contact_us', 'sign_up', 'contact_us', 'sign_up'], $ordered->map->handle()->all());
    }

    #[Test]
    public function it_gets_a_form_config_by_id_with_single_site()
    {
        $this->setUpSingleSite();

        tap($this->repo->find('contact_us', 'default'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('contact_us::default', $formConfig->id());
            $this->assertEquals('contact_us', $formConfig->handle());
        });

        tap($this->repo->find('sign_up', 'default'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('sign_up::default', $formConfig->id());
            $this->assertEquals('sign_up', $formConfig->handle());
        });

        $this->assertNull($this->repo->find('unknown', 'default'));
    }

    #[Test]
    public function it_gets_a_form_config_by_id_with_multi_site()
    {
        $this->setUpMultiSite();

        tap($this->repo->find('contact_us', 'en'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('contact_us::en', $formConfig->id());
            $this->assertEquals('contact_us', $formConfig->handle());
        });

        tap($this->repo->find('contact_us', 'nl'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('contact_us::nl', $formConfig->id());
            $this->assertEquals('contact_us', $formConfig->handle());
        });

        $this->assertNull($this->repo->find('contact_us', 'be'));

        tap($this->repo->find('sign_up', 'en'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('sign_up::en', $formConfig->id());
            $this->assertEquals('sign_up', $formConfig->handle());
        });

        tap($this->repo->find('sign_up', 'nl'), function ($formConfig) {
            $this->assertInstanceOf(FormConfig::class, $formConfig);
            $this->assertEquals('sign_up::nl', $formConfig->id());
            $this->assertEquals('sign_up', $formConfig->handle());
        });

        $this->assertNull($this->repo->find('sign_up', 'be'));

        $this->assertNull($this->repo->find('unknown', 'default'));
    }

    #[Test]
    public function it_gets_form_configs_by_form_handle_with_single_site()
    {
        $this->setUpSingleSite();

        tap($this->repo->whereForm('contact_us'), function ($formConfigs) {
            $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
            $first = $formConfigs->first();
            $this->assertEquals('contact_us::default', $first->id());
            $this->assertEquals('contact_us', $first->handle());
        });

        tap($this->repo->whereForm('sign_up'), function ($formConfigs) {
            $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
            $first = $formConfigs->first();
            $this->assertEquals('sign_up::default', $first->id());
            $this->assertEquals('sign_up', $first->handle());
        });

        $this->assertCount(0, $this->repo->whereForm('unknown'));
    }

    #[Test]
    public function it_gets_form_configs_by_form_handle_with_multi_site()
    {
        $this->setUpMultiSite();

        tap($this->repo->whereForm('contact_us'), function ($formConfigs) {
            $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
            $ordered = $formConfigs->sortBy->path()->values();
            $this->assertEquals(['contact_us::en',  'contact_us::nl'], $ordered->map->id()->all());
        });

        tap($this->repo->whereForm('sign_up'), function ($formConfigs) {
            $this->assertInstanceOf(FormConfigCollection::class, $formConfigs);
            $ordered = $formConfigs->sortBy->path()->values();
            $this->assertEquals(['sign_up::en', 'sign_up::nl'], $ordered->map->id()->all());
        });

        $this->assertCount(0, $this->repo->whereForm('unknown'));
    }

    #[Test]
    public function it_saves_a_form_config_to_the_stache_and_to_a_file_with_single_site()
    {
        $this->setUpSingleSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('default');

        $formConfig->emailField('email')->listId(1);

        $this->assertNull($this->repo->find('new', 'default'));

        @unlink($this->directory.'/new.yaml');

        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'default'));
        $this->assertEquals(['email_field' => 'email', 'list_id' => 1], [
            'email_field' => $item->emailField(),
            'list_id' => $item->listId(),
        ]);
        $this->assertFileExists($this->directory.'/new.yaml');
        $this->assertFileDoesNotExist($this->directory.'/default/new.yaml');

        $contents = "email_field: email\nlist_id: 1\n";
        $this->assertEquals($contents, file_get_contents($this->directory.'/new.yaml'));

        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function it_saves_a_form_config_to_the_stache_and_to_a_file_with_multi_site()
    {
        $this->setUpMultiSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('en');

        $formConfig->emailField('email')->listId(1);

        $this->assertNull($this->repo->find('new', 'en'));

        @unlink($this->directory.'/en/new.yaml');

        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'en'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals(1, $item->listId());
        $this->assertFileDoesNotExist($this->directory.'/new.yaml');
        $this->assertFileExists($this->directory.'/en/new.yaml');

        @unlink($this->directory.'/en/new.yaml');
    }

    #[Test]
    public function it_deletes_a_form_config_from_the_stache_and_file_with_single_site()
    {
        $this->setUpSingleSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('default');
        $formConfig->emailField('email')->listId(1);
        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'default'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals(1, $item->listId());
        $this->assertFileExists($this->directory.'/new.yaml');
        $contents = "email_field: email\nlist_id: 1\n";
        $this->assertEquals($contents, file_get_contents($this->directory.'/new.yaml'));

        $this->repo->delete($item);

        $this->assertNull($this->repo->find('new', 'default'));
        $this->assertFileDoesNotExist($this->directory.'/new.yaml');

        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function it_deletes_a_global_from_the_stache_and_file_with_multi_site()
    {
        $this->setUpMultiSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('en');
        $formConfig->emailField('email')->listId(1);
        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'en'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals(1, $item->listId());

        $this->repo->delete($item);

        $this->assertNull($this->repo->find('new', 'en'));
        $this->assertFileDoesNotExist($this->directory.'/en/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function it_can_access_form()
    {
        $this->setUpSingleSite();

        $formConfig = $this->repo->findOrFail('contact_us', 'default');

        $this->assertInstanceOf(FormConfig::class, $formConfig);
        $this->assertEquals('Contact Us', $formConfig->title());
    }

    #[Test]
    public function it_throws_exception_when_form_does_not_exist()
    {
        $this->setUpSingleSite();

        $this->expectException(FormConfigNotFoundException::class);
        $this->expectExceptionMessage('Form Config [does-not-exist::default] not found');

        $this->repo->findOrFail('does-not-exist', 'default');
    }
}
