<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AuthorizeConfigureTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.forms.forms', __DIR__.'/../__fixtures__/dev-null/resources/forms');
    }

    private function makeFormWithConfig(): void
    {
        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        FormConfig::make()->form($form)->locale('default')->emailField('email')->listIds([1])->save();
    }

    private function sitesPayload(): array
    {
        return ['sites' => [
            ['name' => 'Default', 'handle' => 'default', 'enabled' => true, 'origin' => null],
        ]];
    }

    #[Test]
    public function it_forbids_every_route_without_the_configure_forms_permission(): void
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this->makeFormWithConfig();

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldNotReceive('save');
        });

        Http::fake();

        $requests = [
            ['GET', cp_route('activecampaign.index'), []],
            ['GET', cp_route('activecampaign.edit'), []],
            ['PATCH', cp_route('activecampaign.update'), $this->sitesPayload()],
            ['GET', cp_route('activecampaign.form-config.edit', 'test_form'), []],
            ['PATCH', cp_route('activecampaign.form-config.update', 'test_form'), ['email_field' => 'email', 'list_ids' => [2]]],
            ['DELETE', cp_route('activecampaign.form-config.destroy', 'test_form'), []],
            ['GET', cp_route('activecampaign.form-fields', 'test_form'), []],
            ['GET', cp_route('activecampaign.merge-fields'), []],
        ];

        foreach ($requests as [$method, $url, $data]) {
            $this->actingAs($user)->json($method, $url, $data)->assertForbidden();
        }

        $this->assertEquals([1], FormConfig::find('test_form', 'default')->listIds());
        Http::assertNothingSent();
    }

    #[Test]
    public function it_allows_every_route_for_a_super_user(): void
    {
        $user = tap(User::make()->makeSuper())->save();

        $this->makeFormWithConfig();

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('save')->once();
        });

        Http::fake();

        $this->actingAs($user)->getJson(cp_route('activecampaign.index'))->assertOk();
        $this->actingAs($user)->getJson(cp_route('activecampaign.edit'))->assertOk();
        $this->actingAs($user)->patchJson(cp_route('activecampaign.update'), $this->sitesPayload())->assertNoContent();
        $this->actingAs($user)->getJson(cp_route('activecampaign.form-config.edit', 'test_form'))->assertOk();
        $this->actingAs($user)->patchJson(cp_route('activecampaign.form-config.update', 'test_form'), ['email_field' => 'email', 'list_ids' => [2]])->assertOk();
        $this->actingAs($user)->getJson(cp_route('activecampaign.form-fields', 'test_form'))->assertOk();
        $this->actingAs($user)->getJson(cp_route('activecampaign.merge-fields'))->assertOk();
        $this->actingAs($user)->deleteJson(cp_route('activecampaign.form-config.destroy', 'test_form'))->assertNoContent();
    }
}
