<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Fieldtypes;

use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActiveCampaignSites;
use Lwekuiper\StatamicActivecampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ActiveCampaignSitesTest extends TestCase
{
    private function rules(): array
    {
        return (new ActiveCampaignSites())->rules();
    }

    private function cannotAllHaveOriginsRule()
    {
        return $this->rules()[0];
    }

    private function originsMustBeEnabledRule()
    {
        return $this->rules()[1];
    }

    #[Test]
    public function it_passes_when_at_least_one_enabled_site_has_no_origin()
    {
        $value = [
            ['handle' => 'en', 'enabled' => true, 'origin' => null],
            ['handle' => 'nl', 'enabled' => true, 'origin' => 'en'],
        ];

        $this->assertTrue($this->cannotAllHaveOriginsRule()->passes('sites', $value));
    }

    #[Test]
    public function it_fails_when_all_enabled_sites_have_origins()
    {
        $value = [
            ['handle' => 'en', 'enabled' => true, 'origin' => 'nl'],
            ['handle' => 'nl', 'enabled' => true, 'origin' => 'en'],
        ];

        $this->assertFalse($this->cannotAllHaveOriginsRule()->passes('sites', $value));
    }

    #[Test]
    public function it_ignores_disabled_sites_in_origin_count()
    {
        $value = [
            ['handle' => 'en', 'enabled' => true, 'origin' => null],
            ['handle' => 'nl', 'enabled' => false, 'origin' => 'en'],
        ];

        $this->assertTrue($this->cannotAllHaveOriginsRule()->passes('sites', $value));
    }

    #[Test]
    public function it_passes_when_origin_sites_are_enabled()
    {
        $value = [
            ['handle' => 'en', 'enabled' => true, 'origin' => null],
            ['handle' => 'nl', 'enabled' => true, 'origin' => 'en'],
        ];

        $this->assertTrue($this->originsMustBeEnabledRule()->passes('sites', $value));
    }

    #[Test]
    public function it_fails_when_origin_site_is_not_enabled()
    {
        $value = [
            ['handle' => 'en', 'enabled' => false, 'origin' => null],
            ['handle' => 'nl', 'enabled' => true, 'origin' => 'en'],
        ];

        $this->assertFalse($this->originsMustBeEnabledRule()->passes('sites', $value));
    }

    #[Test]
    public function it_passes_when_no_sites_have_origins()
    {
        $value = [
            ['handle' => 'en', 'enabled' => true, 'origin' => null],
            ['handle' => 'nl', 'enabled' => true, 'origin' => null],
        ];

        $this->assertTrue($this->originsMustBeEnabledRule()->passes('sites', $value));
    }

    #[Test]
    public function it_returns_two_validation_rules()
    {
        $rules = $this->rules();

        $this->assertCount(2, $rules);
    }
}
