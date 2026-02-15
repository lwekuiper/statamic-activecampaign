<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Fieldtypes;

use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActiveCampaignTag;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;

class ActiveCampaignTagTest extends TestCase
{
    private function invokeToItemArray(ActiveCampaignTag $fieldtype, $id): array
    {
        $method = new ReflectionMethod(ActiveCampaignTag::class, 'toItemArray');

        return $method->invoke($fieldtype, $id);
    }
    #[Test]
    public function it_returns_the_correct_handle()
    {
        $this->assertEquals('activecampaign_tag', ActiveCampaignTag::handle());
    }

    #[Test]
    public function it_gets_index_items_from_api()
    {
        ActiveCampaign::shouldReceive('getTags')->once()->andReturn([
            'tags' => [
                ['id' => '1', 'tag' => 'VIP'],
                ['id' => '2', 'tag' => 'Lead'],
            ],
        ]);

        $fieldtype = new ActiveCampaignTag();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([
            ['id' => '1', 'title' => 'VIP'],
            ['id' => '2', 'title' => 'Lead'],
        ], $items);
    }

    #[Test]
    public function it_returns_empty_array_when_api_returns_no_tags()
    {
        ActiveCampaign::shouldReceive('getTags')->once()->andReturn([
            'tags' => [],
        ]);

        $fieldtype = new ActiveCampaignTag();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([], $items);
    }

    #[Test]
    public function it_handles_null_api_response_for_index_items()
    {
        ActiveCampaign::shouldReceive('getTags')->once()->andReturn(null);

        $fieldtype = new ActiveCampaignTag();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([], $items);
    }

    #[Test]
    public function it_converts_id_to_item_array()
    {
        ActiveCampaign::shouldReceive('getTags')->once()->andReturn([
            'tags' => [
                ['id' => '1', 'tag' => 'VIP'],
                ['id' => '2', 'tag' => 'Lead'],
            ],
        ]);

        $fieldtype = new ActiveCampaignTag();
        $item = $this->invokeToItemArray($fieldtype, '2');

        $this->assertEquals(['id' => '2', 'title' => 'Lead'], $item);
    }

    #[Test]
    public function it_returns_empty_array_for_null_id()
    {
        $fieldtype = new ActiveCampaignTag();
        $item = $this->invokeToItemArray($fieldtype, null);

        $this->assertEquals([], $item);
    }

    #[Test]
    public function it_returns_empty_array_for_nonexistent_id()
    {
        ActiveCampaign::shouldReceive('getTags')->once()->andReturn([
            'tags' => [
                ['id' => '1', 'tag' => 'VIP'],
            ],
        ]);

        $fieldtype = new ActiveCampaignTag();
        $item = $this->invokeToItemArray($fieldtype, '999');

        $this->assertEquals([], $item);
    }
}
