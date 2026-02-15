<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Fieldtypes;

use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActiveCampaignList;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;

class ActiveCampaignListTest extends TestCase
{
    private function invokeToItemArray(ActiveCampaignList $fieldtype, $id): array
    {
        $method = new ReflectionMethod(ActiveCampaignList::class, 'toItemArray');

        return $method->invoke($fieldtype, $id);
    }
    #[Test]
    public function it_returns_the_correct_handle()
    {
        $this->assertEquals('activecampaign_list', ActiveCampaignList::handle());
    }

    #[Test]
    public function it_gets_index_items_from_api()
    {
        ActiveCampaign::shouldReceive('getLists')->once()->andReturn([
            'lists' => [
                ['id' => '1', 'name' => 'Newsletter'],
                ['id' => '2', 'name' => 'Customers'],
            ],
        ]);

        $fieldtype = new ActiveCampaignList();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([
            ['id' => '1', 'title' => 'Newsletter'],
            ['id' => '2', 'title' => 'Customers'],
        ], $items);
    }

    #[Test]
    public function it_returns_empty_array_when_api_returns_no_lists()
    {
        ActiveCampaign::shouldReceive('getLists')->once()->andReturn([
            'lists' => [],
        ]);

        $fieldtype = new ActiveCampaignList();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([], $items);
    }

    #[Test]
    public function it_handles_null_api_response_for_index_items()
    {
        ActiveCampaign::shouldReceive('getLists')->once()->andReturn(null);

        $fieldtype = new ActiveCampaignList();
        $items = $fieldtype->getIndexItems(request());

        $this->assertEquals([], $items);
    }

    #[Test]
    public function it_converts_id_to_item_array()
    {
        ActiveCampaign::shouldReceive('getLists')->once()->andReturn([
            'lists' => [
                ['id' => '1', 'name' => 'Newsletter'],
                ['id' => '2', 'name' => 'Customers'],
            ],
        ]);

        $fieldtype = new ActiveCampaignList();
        $item = $this->invokeToItemArray($fieldtype, '2');

        $this->assertEquals(['id' => '2', 'title' => 'Customers'], $item);
    }

    #[Test]
    public function it_returns_empty_array_for_null_id()
    {
        $fieldtype = new ActiveCampaignList();
        $item = $this->invokeToItemArray($fieldtype, null);

        $this->assertEquals([], $item);
    }

    #[Test]
    public function it_returns_empty_array_for_nonexistent_id()
    {
        ActiveCampaign::shouldReceive('getLists')->once()->andReturn([
            'lists' => [
                ['id' => '1', 'name' => 'Newsletter'],
            ],
        ]);

        $fieldtype = new ActiveCampaignList();
        $item = $this->invokeToItemArray($fieldtype, '999');

        $this->assertEquals([], $item);
    }
}
