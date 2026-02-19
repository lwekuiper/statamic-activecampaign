<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Stache;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Data\FormConfig;
use Lwekuiper\StatamicActivecampaign\Data\FormConfigCollection;
use Lwekuiper\StatamicActivecampaign\Exceptions\FormConfigNotFoundException;
use Lwekuiper\StatamicActivecampaign\Facades\AddonConfig as AddonConfigFacade;
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

        $formConfig->emailField('email')->listIds([1]);

        $this->assertNull($this->repo->find('new', 'default'));

        @unlink($this->directory.'/new.yaml');

        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'default'));
        $this->assertEquals(['email_field' => 'email', 'list_ids' => [1]], [
            'email_field' => $item->emailField(),
            'list_ids' => $item->listIds(),
        ]);
        $this->assertFileExists($this->directory.'/new.yaml');
        $this->assertFileDoesNotExist($this->directory.'/default/new.yaml');

        $contents = "email_field: email\nlist_ids:\n  - 1\n";
        $this->assertEquals($contents, file_get_contents($this->directory.'/new.yaml'));

        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function it_saves_a_form_config_to_the_stache_and_to_a_file_with_multi_site()
    {
        $this->setUpMultiSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('en');

        $formConfig->emailField('email')->listIds([1]);

        $this->assertNull($this->repo->find('new', 'en'));

        @unlink($this->directory.'/en/new.yaml');

        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'en'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals([1], $item->listIds());
        $this->assertFileDoesNotExist($this->directory.'/new.yaml');
        $this->assertFileExists($this->directory.'/en/new.yaml');

        @unlink($this->directory.'/en/new.yaml');
    }

    #[Test]
    public function it_deletes_a_form_config_from_the_stache_and_file_with_single_site()
    {
        $this->setUpSingleSite();

        $formConfig = FormConfigFacade::make()->form('new')->locale('default');
        $formConfig->emailField('email')->listIds([1]);
        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'default'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals([1], $item->listIds());
        $this->assertFileExists($this->directory.'/new.yaml');
        $contents = "email_field: email\nlist_ids:\n  - 1\n";
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
        $formConfig->emailField('email')->listIds([1]);
        $this->repo->save($formConfig);

        $this->assertNotNull($item = $this->repo->find('new', 'en'));
        $this->assertEquals('email', $item->emailField());
        $this->assertEquals([1], $item->listIds());

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

    #[Test]
    public function find_resolved_returns_config_directly_when_it_exists()
    {
        $this->setUpMultiSite();

        // Both 'en' and 'nl' have configs in fixtures, so direct lookup should work
        $config = $this->repo->findResolved('contact_us', 'en');

        $this->assertInstanceOf(FormConfig::class, $config);
        $this->assertEquals('contact_us::en', $config->id());
    }

    #[Test]
    public function find_resolved_follows_origin_chain_when_config_is_missing()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'nl' => ['url' => '/nl/'],
            'be' => ['url' => '/be/'],
        ]);

        // Configure AddonConfig: en is root, nl inherits from en, be inherits from nl
        $configPath = app(AddonConfig::class)->path();
        $directory = dirname($configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en', 'be' => 'nl']));
        app(AddonConfig::class)->fresh();

        $stache = (new Stache)->sites(['en', 'nl', 'be']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/__fixtures__/resources/activecampaign-multisite';
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);

        // 'en' and 'nl' have fixture configs; 'be' does not.
        // findResolved('contact_us', 'be') should follow: be -> nl (found!)
        $config = $this->repo->findResolved('contact_us', 'be');

        $this->assertInstanceOf(FormConfig::class, $config);
        $this->assertEquals('contact_us::nl', $config->id());

        @unlink($configPath);
    }

    #[Test]
    public function find_resolved_returns_null_when_no_config_exists_in_chain()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'nl' => ['url' => '/nl/'],
        ]);

        // Configure AddonConfig: en is root, nl inherits from en
        $configPath = app(AddonConfig::class)->path();
        $directory = dirname($configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        $stache = (new Stache)->sites(['en', 'nl']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/__fixtures__/resources/activecampaign-multisite';
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);

        // 'nonexistent' form doesn't exist on any site
        $config = $this->repo->findResolved('nonexistent', 'nl');

        $this->assertNull($config);

        @unlink($configPath);
    }

    #[Test]
    public function ensure_localizations_exist_creates_configs_for_enabled_sites()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'nl' => ['url' => '/nl/'],
        ]);

        $configPath = app(AddonConfig::class)->path();
        $directory = dirname($configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        $stache = (new Stache)->sites(['en', 'nl']);
        $this->app->instance(Stache::class, $stache);
        // Use a temp directory so we don't pollute fixtures
        $this->directory = $this->app->basePath('resources/activecampaign');
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);

        $this->assertNull($this->repo->find('new_form', 'en'));
        $this->assertNull($this->repo->find('new_form', 'nl'));

        $this->repo->ensureLocalizationsExist('new_form');

        $this->assertNotNull($this->repo->find('new_form', 'en'));
        $this->assertNotNull($this->repo->find('new_form', 'nl'));

        // Clean up
        @unlink($this->directory.'/en/new_form.yaml');
        @unlink($this->directory.'/nl/new_form.yaml');
        @rmdir($this->directory.'/en');
        @rmdir($this->directory.'/nl');
        @unlink($configPath);
    }

    #[Test]
    public function ensure_localizations_exist_does_not_duplicate_existing_configs()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'nl' => ['url' => '/nl/'],
        ]);

        $configPath = app(AddonConfig::class)->path();
        $directory = dirname($configPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        AddonConfigFacade::save(collect(['en' => null, 'nl' => 'en']));
        app(AddonConfig::class)->fresh();

        $stache = (new Stache)->sites(['en', 'nl']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = $this->app->basePath('resources/activecampaign');
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
        $stache->registerStore((new FormConfigStore($stache, app('files')))->directory($this->directory));

        $this->repo = new FormConfigRepository($stache);

        // Create an existing config for 'en' with data
        $existing = $this->repo->make()->form('new_form')->locale('en');
        $existing->emailField('email')->listIds([1]);
        $this->repo->save($existing);

        $this->assertNotNull($this->repo->find('new_form', 'en'));
        $this->assertNull($this->repo->find('new_form', 'nl'));

        $this->repo->ensureLocalizationsExist('new_form');

        // 'en' config should remain unchanged
        $enConfig = $this->repo->find('new_form', 'en');
        $this->assertEquals('email', $enConfig->emailField());
        $this->assertEquals([1], $enConfig->listIds());

        // 'nl' config should be created
        $this->assertNotNull($this->repo->find('new_form', 'nl'));

        // Clean up
        @unlink($this->directory.'/en/new_form.yaml');
        @unlink($this->directory.'/nl/new_form.yaml');
        @rmdir($this->directory.'/en');
        @rmdir($this->directory.'/nl');
        @unlink($configPath);
    }
}
