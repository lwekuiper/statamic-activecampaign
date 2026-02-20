<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Http\Controllers;

use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class GetFormFieldOptionsControllerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_options_for_select_fields()
    {
        $form = tap(Form::make('newsletter')->title('Newsletter'))->save();
        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'email', 'field' => ['type' => 'text']],
                                ['handle' => 'frequency', 'field' => [
                                    'type' => 'select',
                                    'options' => [
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                        'yearly' => 'Yearly',
                                    ],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.form-field-options', $form->handle()))
            ->assertOk()
            ->assertJson([
                'frequency' => [
                    ['id' => 'weekly', 'label' => 'Weekly'],
                    ['id' => 'monthly', 'label' => 'Monthly'],
                    ['id' => 'yearly', 'label' => 'Yearly'],
                ],
            ]);
    }

    #[Test]
    public function it_returns_options_for_checkboxes_fields()
    {
        $form = tap(Form::make('newsletter')->title('Newsletter'))->save();
        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'interests', 'field' => [
                                    'type' => 'checkboxes',
                                    'options' => [
                                        'tech' => 'Technology',
                                        'sports' => 'Sports',
                                    ],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.form-field-options', $form->handle()))
            ->assertOk()
            ->assertJson([
                'interests' => [
                    ['id' => 'tech', 'label' => 'Technology'],
                    ['id' => 'sports', 'label' => 'Sports'],
                ],
            ]);
    }

    #[Test]
    public function it_returns_options_for_radio_fields()
    {
        $form = tap(Form::make('newsletter')->title('Newsletter'))->save();
        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'preference', 'field' => [
                                    'type' => 'radio',
                                    'options' => [
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                    ],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.form-field-options', $form->handle()))
            ->assertOk()
            ->assertJson([
                'preference' => [
                    ['id' => 'daily', 'label' => 'Daily'],
                    ['id' => 'weekly', 'label' => 'Weekly'],
                ],
            ]);
    }

    #[Test]
    public function it_excludes_fields_without_options()
    {
        $form = tap(Form::make('newsletter')->title('Newsletter'))->save();
        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'email', 'field' => ['type' => 'text']],
                                ['handle' => 'consent', 'field' => ['type' => 'toggle']],
                                ['handle' => 'frequency', 'field' => [
                                    'type' => 'select',
                                    'options' => [
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                    ],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $user = User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.form-field-options', $form->handle()))
            ->assertOk()
            ->json();

        $this->assertArrayNotHasKey('email', $response);
        $this->assertArrayNotHasKey('consent', $response);
        $this->assertArrayHasKey('frequency', $response);
    }

    #[Test]
    public function it_returns_options_for_button_group_fields()
    {
        $form = tap(Form::make('newsletter')->title('Newsletter'))->save();
        $form->blueprint()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'format', 'field' => [
                                    'type' => 'button_group',
                                    'options' => [
                                        'html' => 'HTML',
                                        'text' => 'Plain Text',
                                    ],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ])->save();

        $user = User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->get(cp_route('activecampaign.form-field-options', $form->handle()))
            ->assertOk()
            ->assertJson([
                'format' => [
                    ['id' => 'html', 'label' => 'HTML'],
                    ['id' => 'text', 'label' => 'Plain Text'],
                ],
            ]);
    }
}
