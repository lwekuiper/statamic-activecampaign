<?php

namespace Lwekuiper\StatamicActivecampaign;

use Statamic\Facades\CP\Nav;
use Statamic\Events\SubmissionCreated;
use Statamic\Providers\AddonServiceProvider;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\FormFields;
use Lwekuiper\StatamicActivecampaign\Listeners\AddFromSubmission;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActivecampaignTag;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActivecampaignList;
use Lwekuiper\StatamicActivecampaign\Services\ActiveCampaignService;
use Lwekuiper\StatamicActivecampaign\Fieldtypes\ActivecampaignMergeFields;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        ActivecampaignList::class,
        ActivecampaignMergeFields::class,
        ActivecampaignTag::class,
        FormFields::class,
    ];

    protected $listen = [
        SubmissionCreated::class => [AddFromSubmission::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        Nav::extend(function ($nav) {
            $nav->create('ActiveCampaign')
                ->section('Settings')
                ->route('activecampaign.edit')
                ->icon('<svg fill="#3C4858" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 124 124"><path d="m99.5 61.8-64.8 43c-3 2-4.5 5.2-4.5 8.5V124l78.5-51.5c3.5-2.5 5.8-6.5 5.8-10.7s-2-8.3-5.8-10.8L30.2 0v10c0 3.5 1.8 6.8 4.5 8.5l64.8 43.3Z"/><path d="M60.6 65.2c3.5 2.2 8 2.2 11.4 0l5.5-3.7-40.8-27.6c-2.5-1.7-6.2 0-6.2 3.2v8.2l21.1 14.2 8.9 5.7Z"/></svg>');
        });
    }

    public function register()
    {
        $this->app->singleton(ActiveCampaignService::class, function () {
            return new ActiveCampaignService();
        });
    }
}
