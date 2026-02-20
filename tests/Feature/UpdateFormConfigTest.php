<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Statamic\Facades\Form;
use Statamic\Facades\User;
use PHPUnit\Framework\Attributes\Test;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class UpdateFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_updates_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test')->makeSuper())->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent')->tagIds([1]);
        $formConfig->save();

        $this
            ->from('/here')
            ->actingAs($user)
            ->patchJson($formConfig->updateUrl(), [
                'email_field' => 'email',
                'list_mode' => 'fixed',
                'list_ids' => [2],
                'consent_field' => 'consent',
                'tag_ids' => [2]
            ])
            ->assertSuccessful();

        $this->assertCount(1, FormConfig::all());
        $formConfig = FormConfig::find('test_form', 'default');
        $this->assertEquals([2], $formConfig->listIds());
    }
}
