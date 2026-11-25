<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Tests\Feature;

use Lwekuiper\StatamicActivecampaign\Data\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class UpdateAddonConfigTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_rejects_a_site_handle_that_does_not_exist()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        $this->partialMock(AddonConfig::class, function ($mock) {
            $mock->shouldReceive('sites')->andReturn(collect(['en' => null]));
            $mock->shouldNotReceive('save');
        });

        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        FormConfig::make()->form($form)->locale('en')->emailField('email')->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('activecampaign.update'), [
                'sites' => [
                    ['handle' => 'en', 'name' => 'English', 'enabled' => true, 'origin' => null],
                    ['handle' => '../escaped', 'name' => 'Escaped', 'enabled' => true, 'origin' => 'en'],
                ],
            ])
            ->assertUnprocessable();

        $this->assertNull(FormConfig::find('contact_us', '../escaped'));
        $this->assertCount(1, FormConfig::all());
    }
}
