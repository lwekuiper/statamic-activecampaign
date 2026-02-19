<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\AddonConfigController;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\FormConfigController;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\GetFormFieldsController;
use Lwekuiper\StatamicActivecampaign\Http\Controllers\GetMergeFieldsController;

Route::name('activecampaign.')->prefix('activecampaign')->group(function () {
    Route::get('/', [FormConfigController::class, 'index'])->name('index');

    Route::get('/edit', [AddonConfigController::class, 'edit'])->name('edit');
    Route::patch('/edit', [AddonConfigController::class, 'update'])->name('update');

    Route::name('form-config.')->group(function () {
        Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit');
        Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update');
        Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy');
    });

    Route::get('form-fields/{form}', [GetFormFieldsController::class, '__invoke'])->name('form-fields');
    Route::get('merge-fields', [GetMergeFieldsController::class, '__invoke'])->name('merge-fields');
});
