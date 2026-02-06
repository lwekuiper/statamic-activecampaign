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
        $formConfig->emailField('email')->listId(1)->consentField('consent')->tagId(1);
        $formConfig->save();

        Http::fake(); // Fake any HTTP requests to the ActiveCampaign API.

        $this->actingAs($user)
            ->getJson($formConfig->editUrl())
            ->assertOk()
            ->assertJson(['values' => [
                'email_field' => 'email',
                'list_id' => [1],
                'consent_field' => 'consent',
                'tag_id' => [1],
                'merge_fields' => [],
            ]]);
    }
}
