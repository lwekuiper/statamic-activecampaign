<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EditFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_edit_form_config_page()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent')->tagIds([1]);
        $formConfig->save();

        Http::fake([
            '*/api/3/lists*' => Http::response(['lists' => []], 200),
            '*/api/3/tags*' => Http::response(['tags' => []], 200),
            '*/api/3/fields*' => Http::response(['fields' => []], 200),
            '*' => Http::response([], 200),
        ]);

        $this->actingAs($user)
            ->get($formConfig->editUrl())
            ->assertOk()
            ->assertViewHas('values', collect([
                'email_field' => 'email',
                'list_ids' => [1],
                'consent_field' => 'consent',
                'tag_ids' => [1],
                'merge_fields' => [],
            ]));
    }
}
