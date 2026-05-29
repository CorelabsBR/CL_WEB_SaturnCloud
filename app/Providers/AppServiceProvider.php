<?php

namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\Provider as DiscordProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', DiscordProvider::class);
        });

        if (!$this->app->runningInConsole()) {
            User::query()
                ->whereNull('email_verified_at')
                ->where('criado_em', '<=', now()->subDay())
                ->delete();
        }
    }
}
