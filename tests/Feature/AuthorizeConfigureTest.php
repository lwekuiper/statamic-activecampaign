<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\FakesRoles;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AuthorizeConfigureTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        FormConfig::make()->form($form)->locale('default')->emailField('email')->listIds([1])->save();

        Http::fake(['*' => Http::response([], 200)]);
    }

    public static function routes(): array
    {
        $formConfig = ['email_field' => 'email', 'list_mode' => 'always', 'list_ids' => [2]];

        return [
            'listing' => ['get', 'activecampaign.index', [], [], 200],
            'addon config edit' => ['get', 'activecampaign.edit', [], [], 302],
            'addon config update' => ['patch', 'activecampaign.update', [], [], 204],
            'form config edit' => ['get', 'activecampaign.form-config.edit', ['test_form'], [], 200],
            'form config update' => ['patch', 'activecampaign.form-config.update', ['test_form'], $formConfig, 200],
            'form config destroy' => ['delete', 'activecampaign.form-config.destroy', ['test_form'], [], 204],
            'form fields' => ['get', 'activecampaign.form-fields', ['test_form'], [], 200],
            'form field options' => ['get', 'activecampaign.form-field-options', ['test_form'], [], 200],
            'merge fields' => ['get', 'activecampaign.merge-fields', [], [], 200],
        ];
    }

    #[Test]
    #[DataProvider('routes')]
    public function it_denies_a_cp_user_without_the_configure_forms_permission(string $method, string $route, array $params, array $data, int $status)
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user)
            ->json($method, cp_route($route, $params), $data)
            ->assertForbidden();

        $this->assertNotNull(FormConfig::find('test_form', 'default'));
    }

    #[Test]
    #[DataProvider('routes')]
    public function it_allows_a_super_user(string $method, string $route, array $params, array $data, int $status)
    {
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)
            ->json($method, cp_route($route, $params), $data)
            ->assertStatus($status);
    }
}
