<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class DestroyFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_deletes_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent')->tagIds([1]);
        $formConfig->save();

        $this->assertCount(1, FormConfig::all());

        $this->actingAs($user)
            ->delete($formConfig->deleteUrl())
            ->assertNoContent();

        $this->assertCount(0, FormConfig::all());
    }
}
