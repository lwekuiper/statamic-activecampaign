<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Connectors;

use Lwekuiper\StatamicActivecampaign\Connectors\ActiveCampaignConnector;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ActiveCampaignConnectorTest extends TestCase
{
    #[Test]
    public function it_returns_false_when_api_url_is_missing()
    {
        config()->set('statamic.activecampaign.api_url', null);
        config()->set('statamic.activecampaign.api_key', 'test-key');

        $connector = new ActiveCampaignConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_false_when_api_key_is_missing()
    {
        config()->set('statamic.activecampaign.api_url', 'https://example.api-us1.com');
        config()->set('statamic.activecampaign.api_key', null);

        $connector = new ActiveCampaignConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_false_when_both_credentials_are_missing()
    {
        config()->set('statamic.activecampaign.api_url', null);
        config()->set('statamic.activecampaign.api_key', null);

        $connector = new ActiveCampaignConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_false_when_credentials_are_empty_strings()
    {
        config()->set('statamic.activecampaign.api_url', '');
        config()->set('statamic.activecampaign.api_key', '');

        $connector = new ActiveCampaignConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_true_when_both_credentials_are_set()
    {
        config()->set('statamic.activecampaign.api_url', 'https://example.api-us1.com');
        config()->set('statamic.activecampaign.api_key', 'test-key');

        $connector = new ActiveCampaignConnector;

        $this->assertTrue($connector->isConfigured());
    }
}
