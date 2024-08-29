<?php

namespace Lwekuiper\StatamicActiveCampaign\Tests;

use Statamic\Testing\AddonTestCase;
use Lwekuiper\StatamicActivecampaign\ServiceProvider;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;
}
