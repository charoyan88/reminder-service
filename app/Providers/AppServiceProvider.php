<?php

namespace App\Providers;

use App\Repositories\EmailTemplateRepository;
use App\Repositories\ReminderIntervalRepository;
use App\Repositories\ReminderRepository;
use App\Services\Interfaces\ReminderServiceInterface;
use App\Services\Mail\ReminderMailService;
use App\Services\ReminderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register repositories
        $this->app->singleton(ReminderRepository::class);
        $this->app->singleton(EmailTemplateRepository::class);
        $this->app->singleton(ReminderIntervalRepository::class);
        
        // Register services
        $this->app->singleton(ReminderMailService::class);
        
        // Bind interfaces to implementations
        $this->app->bind(ReminderServiceInterface::class, ReminderService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
