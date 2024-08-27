<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\ActiveCampaignController;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\GetFormFieldsController;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\GetMergeFieldsController;

Route::name('activecampaign.')->prefix('activecampaign')->group(function () {
    Route::get('edit', [ActiveCampaignController::class, 'edit'])->name('edit');
    Route::patch('update', [ActiveCampaignController::class, 'update'])->name('update');

    Route::get('form-fields/{form}', [GetFormFieldsController::class, '__invoke'])->name('form-fields');
    Route::get('merge-fields', [GetMergeFieldsController::class, '__invoke'])->name('merge-fields');
});
