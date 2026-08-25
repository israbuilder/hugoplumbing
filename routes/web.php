<?php

use App\Http\Controllers\Integrations\GoogleSearchConsoleController;
use App\Http\Controllers\Integrations\GoogleAnalyticsController;
use App\Http\Controllers\Integrations\GoogleBusinessProfileController;
use App\Http\Controllers\Integrations\GoogleAdsController;
use App\Http\Controllers\Integrations\MetaController;
use App\Livewire\Tv\TvDashboard;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth',])->prefix('integrations/google')->name('integrations.google.search-console.')
    ->group(function () {
        Route::get('/connect',[GoogleSearchConsoleController::class,'redirect',])->name('connect');
        Route::get('/callback',[GoogleSearchConsoleController::class, 'callback',])->name('callback');
        Route::delete('/{account}',[GoogleSearchConsoleController::class,'disconnect',])->name('disconnect');
    });

Route::middleware(['auth',])->prefix('integrations/analytics')->name('integrations.google.analytics.')
    ->group(function () {
            Route::get('/connect',[GoogleAnalyticsController::class,'redirect',])->name('connect');
            Route::get('/callback',[GoogleAnalyticsController::class,'callback',])->name('callback');
            Route::delete('/{account}',[GoogleAnalyticsController::class,'disconnect',])->name('disconnect');
        });

Route::middleware(['auth',])->prefix('integrations/business')->name('integrations.google.business-profile.')
    ->group(function () {
            Route::get('/connect',[GoogleBusinessProfileController::class,'redirect',])->name('connect');
            Route::get('/callback',[GoogleBusinessProfileController::class,'callback',])->name('callback');
            Route::delete('/{account}',[ GoogleBusinessProfileController::class,'disconnect',])->name('disconnect');
      });

Route::middleware(['auth'])->prefix('integrations/ads')->name('integrations.google.google-ads.')
    ->group(function () {
        Route::get('/connect',[GoogleAdsController::class,'redirect',])->name('connect');
        Route::get('/callback',[GoogleAdsController::class,'callback',])->name('callback');
        Route::delete('/{account}',[GoogleAdsController::class,'disconnect',])->name('disconnect');
    });


Route::middleware(['auth'])->prefix('integrations/meta')->name('integrations.meta.')
    ->group(function () {
        Route::get('/connect',[MetaController::class, 'connect'])->name('connect');
        Route::get('/callback',[MetaController::class, 'callback'])->name('callback');
    });

Route::get('/tv/{dashboard:slug}/{token}',TvDashboard::class)->name('tv.dashboard');



