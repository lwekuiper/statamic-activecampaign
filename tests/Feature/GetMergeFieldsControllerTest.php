<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class GetMergeFieldsControllerTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_standard_fields_and_custom_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        ActiveCampaign::shouldReceive('getCustomFields')->once()->andReturn([
            'fields' => [
                ['id' => '1', 'title' => 'Company'],
                ['id' => '2', 'title' => 'Website'],
            ],
        ]);

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.merge-fields'))
            ->assertOk();

        $data = $response->json();

        // 4 standard fields + 2 custom fields
        $this->assertCount(6, $data);

        // Standard fields
        $this->assertEquals(['id' => 'email', 'label' => 'Email'], $data[0]);
        $this->assertEquals(['id' => 'firstName', 'label' => 'First Name'], $data[1]);
        $this->assertEquals(['id' => 'lastName', 'label' => 'Last Name'], $data[2]);
        $this->assertEquals(['id' => 'phone', 'label' => 'Phone'], $data[3]);

        // Custom fields
        $this->assertEquals(['id' => '1', 'label' => 'Company'], $data[4]);
        $this->assertEquals(['id' => '2', 'label' => 'Website'], $data[5]);
    }

    #[Test]
    public function it_returns_only_standard_fields_when_no_custom_fields_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        ActiveCampaign::shouldReceive('getCustomFields')->once()->andReturn([
            'fields' => [],
        ]);

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.merge-fields'))
            ->assertOk();

        $data = $response->json();

        $this->assertCount(4, $data);
        $this->assertEquals('email', $data[0]['id']);
        $this->assertEquals('phone', $data[3]['id']);
    }

    #[Test]
    public function it_handles_null_api_response_for_custom_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        ActiveCampaign::shouldReceive('getCustomFields')->once()->andReturn(null);

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.merge-fields'))
            ->assertOk();

        $data = $response->json();

        // Only the 4 standard fields
        $this->assertCount(4, $data);
    }
}
