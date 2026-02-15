<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class GetFormFieldsControllerTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_form_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('contact')->title('Contact'))->save();

        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Full Name']],
                                ['handle' => 'email', 'field' => ['type' => 'text', 'display' => 'Email Address']],
                                ['handle' => 'message', 'field' => ['type' => 'textarea', 'display' => 'Message']],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $response = $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-fields', ['form' => $form->handle()]))
            ->assertOk();

        $data = $response->json();

        $this->assertCount(3, $data);
        $this->assertEquals('name', $data[0]['id']);
        $this->assertEquals('Full Name', $data[0]['label']);
        $this->assertEquals('email', $data[1]['id']);
        $this->assertEquals('Email Address', $data[1]['label']);
        $this->assertEquals('message', $data[2]['id']);
        $this->assertEquals('Message', $data[2]['label']);
    }

    #[Test]
    public function it_returns_empty_array_for_form_with_no_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('empty_form')->title('Empty Form'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('activecampaign.form-fields', ['form' => $form->handle()]))
            ->assertOk()
            ->assertJson([]);
    }
}
