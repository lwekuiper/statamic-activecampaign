<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Statamic\Facades\Form;
use Statamic\Facades\Stache;
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
                'list_mode' => 'always',
                'list_ids' => [2],
                'consent_field' => 'consent',
                'tag_ids' => [2]
            ])
            ->assertSuccessful();

        $this->assertCount(1, FormConfig::all());
        $formConfig = FormConfig::find('test_form', 'default');
        $this->assertEquals([2], $formConfig->listIds());
    }

    #[Test]
    public function it_rejects_a_site_that_does_not_exist_instead_of_writing_outside_the_store()
    {
        $this->setProEdition();
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $user = tap(User::make()->makeSuper())->save();
        tap(Form::make('test_form')->title('Test Form'))->save();

        $escapedPath = Stache::store('activecampaign-form-configs')->directory().'/../../evil/test_form.yaml';

        $this->actingAs($user)
            ->patchJson(cp_route('activecampaign.form-config.update', ['form' => 'test_form', 'site' => '../../evil']), [
                'email_field' => 'email',
                'list_mode' => 'always',
                'list_ids' => [2],
            ])
            ->assertNotFound();

        $this->assertNull(FormConfig::find('test_form', '../../evil'));
        $this->assertFileDoesNotExist($escapedPath);
    }
}
