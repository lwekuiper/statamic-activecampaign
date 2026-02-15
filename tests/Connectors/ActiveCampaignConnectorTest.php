<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lwekuiper\StatamicActivecampaign\Connectors\ActiveCampaignConnector;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;

class ActiveCampaignConnectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'statamic.activecampaign.api_url' => 'https://test.api-us1.com',
            'statamic.activecampaign.api_key' => 'test-api-key',
        ]);

        Blink::flush();
    }

    #[Test]
    public function it_syncs_a_contact()
    {
        Http::fake([
            'test.api-us1.com/api/3/contact/sync' => Http::response([
                'contact' => ['id' => 1, 'email' => 'john@example.com'],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->syncContact('john@example.com', ['firstName' => 'John']);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['contact']['id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test.api-us1.com/api/3/contact/sync'
                && $request->header('Api-Token')[0] === 'test-api-key'
                && $request['contact']['email'] === 'john@example.com'
                && $request['contact']['firstName'] === 'John';
        });
    }

    #[Test]
    public function it_returns_null_and_logs_on_failed_sync_contact()
    {
        Http::fake([
            'test.api-us1.com/api/3/contact/sync' => Http::response(
                ['error' => 'Something went wrong'],
                500
            ),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to sync contact');

        $connector = new ActiveCampaignConnector();
        $result = $connector->syncContact('john@example.com', []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_updates_list_status()
    {
        Http::fake([
            'test.api-us1.com/api/3/contactLists' => Http::response([
                'contactList' => ['contact' => 1, 'list' => 5, 'status' => 1],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->updateListStatus(1, 5);

        $this->assertNotNull($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test.api-us1.com/api/3/contactLists'
                && $request['contactList']['list'] === 5
                && $request['contactList']['contact'] === 1
                && $request['contactList']['status'] === 1;
        });
    }

    #[Test]
    public function it_returns_null_on_failed_list_status_update()
    {
        Http::fake([
            'test.api-us1.com/api/3/contactLists' => Http::response([], 422),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to update list status');

        $connector = new ActiveCampaignConnector();
        $result = $connector->updateListStatus(1, 5);

        $this->assertNull($result);
    }

    #[Test]
    public function it_adds_a_tag_to_contact()
    {
        Http::fake([
            'test.api-us1.com/api/3/contactTags' => Http::response([
                'contactTag' => ['contact' => 1, 'tag' => 10],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->addTagToContact(1, 10);

        $this->assertNotNull($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test.api-us1.com/api/3/contactTags'
                && $request['contactTag']['contact'] === 1
                && $request['contactTag']['tag'] === 10;
        });
    }

    #[Test]
    public function it_returns_null_on_failed_tag_addition()
    {
        Http::fake([
            'test.api-us1.com/api/3/contactTags' => Http::response([], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to add tag to contact');

        $connector = new ActiveCampaignConnector();
        $result = $connector->addTagToContact(1, 10);

        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_lists()
    {
        Http::fake([
            'test.api-us1.com/api/3/lists*' => Http::response([
                'lists' => [
                    ['id' => 1, 'name' => 'Newsletter'],
                    ['id' => 2, 'name' => 'Customers'],
                ],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->getLists();

        $this->assertNotNull($result);
        $this->assertCount(2, $result['lists']);
    }

    #[Test]
    public function it_caches_lists_via_blink()
    {
        Http::fake([
            'test.api-us1.com/api/3/lists*' => Http::response([
                'lists' => [['id' => 1, 'name' => 'Newsletter']],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $connector->getLists();
        $connector->getLists();

        Http::assertSentCount(1);
    }

    #[Test]
    public function it_gets_a_single_list()
    {
        Http::fake([
            'test.api-us1.com/api/3/lists/5' => Http::response([
                'list' => ['id' => 5, 'name' => 'VIP List'],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->getList(5);

        $this->assertNotNull($result);
        $this->assertEquals('VIP List', $result['list']['name']);
    }

    #[Test]
    public function it_returns_null_on_failed_get_list()
    {
        Http::fake([
            'test.api-us1.com/api/3/lists/999' => Http::response([], 404),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to get list');

        $connector = new ActiveCampaignConnector();
        $result = $connector->getList(999);

        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_tags()
    {
        Http::fake([
            'test.api-us1.com/api/3/tags*' => Http::response([
                'tags' => [
                    ['id' => 1, 'tag' => 'VIP'],
                    ['id' => 2, 'tag' => 'Lead'],
                ],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->getTags();

        $this->assertNotNull($result);
        $this->assertCount(2, $result['tags']);
    }

    #[Test]
    public function it_caches_tags_via_blink()
    {
        Http::fake([
            'test.api-us1.com/api/3/tags*' => Http::response([
                'tags' => [['id' => 1, 'tag' => 'VIP']],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $connector->getTags();
        $connector->getTags();

        Http::assertSentCount(1);
    }

    #[Test]
    public function it_gets_a_single_tag()
    {
        Http::fake([
            'test.api-us1.com/api/3/tags/3' => Http::response([
                'tag' => ['id' => 3, 'tag' => 'Premium'],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->getTag(3);

        $this->assertNotNull($result);
        $this->assertEquals('Premium', $result['tag']['tag']);
    }

    #[Test]
    public function it_returns_null_on_failed_get_tag()
    {
        Http::fake([
            'test.api-us1.com/api/3/tags/999' => Http::response([], 404),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to get tag');

        $connector = new ActiveCampaignConnector();
        $result = $connector->getTag(999);

        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_custom_fields()
    {
        Http::fake([
            'test.api-us1.com/api/3/fields' => Http::response([
                'fields' => [
                    ['id' => 1, 'title' => 'Company'],
                    ['id' => 2, 'title' => 'Website'],
                ],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $result = $connector->getCustomFields();

        $this->assertNotNull($result);
        $this->assertCount(2, $result['fields']);
    }

    #[Test]
    public function it_caches_custom_fields_via_blink()
    {
        Http::fake([
            'test.api-us1.com/api/3/fields' => Http::response([
                'fields' => [['id' => 1, 'title' => 'Company']],
            ]),
        ]);

        $connector = new ActiveCampaignConnector();
        $connector->getCustomFields();
        $connector->getCustomFields();

        Http::assertSentCount(1);
    }

    #[Test]
    public function it_returns_null_on_failed_get_custom_fields()
    {
        Http::fake([
            'test.api-us1.com/api/3/fields' => Http::response([], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Failed to get custom fields');

        $connector = new ActiveCampaignConnector();
        $result = $connector->getCustomFields();

        $this->assertNull($result);
    }
}
