<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, class-string|string>
     */
    protected $listen = [
        // ... event lainnya ...

        \App\Events\TransaksiTabunganCreated::class => [
            \App\Listeners\SendTransaksiTabunganWebhook::class,
        ],

        \App\Events\TransaksiPinjamanCreated::class => [
            \App\Listeners\SendTransaksiPinjamanWebhook::class,
        ],
    ];
}
