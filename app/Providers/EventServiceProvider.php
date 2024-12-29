<?php

namespace App\Providers;

use App\Events\TransaksiPinjamanCreated;
use App\Events\TransaksiPinjamanDeleted;
use App\Events\TransaksiPinjamanUpdated;
use App\Events\TransaksiTabunganCreated;
use App\Events\TransaksiTabunganDeleted;
use App\Events\TransaksiTabunganUpdated;
use App\Listeners\SendTransaksiPinjamanCreateWebhook;
use App\Listeners\SendTransaksiPinjamanDeleteWebhook;
use App\Listeners\SendTransaksiPinjamanUpdateWebhook;
use App\Listeners\SendTransaksiTabunganCreateWebhook;
use App\Listeners\SendTransaksiTabunganDeleteWebhook;
use App\Listeners\SendTransaksiTabunganUpdateWebhook;
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

        TransaksiTabunganCreated::class => [
            SendTransaksiTabunganCreateWebhook::class,
        ],
        TransaksiTabunganDeleted::class => [
            SendTransaksiTabunganDeleteWebhook::class,
        ],
        TransaksiTabunganUpdated::class => [
            SendTransaksiTabunganUpdateWebhook::class,
        ],

        TransaksiPinjamanCreated::class => [
            SendTransaksiPinjamanCreateWebhook::class,
        ],
        TransaksiPinjamanDeleted::class => [
            SendTransaksiPinjamanDeleteWebhook::class,
        ],
        TransaksiPinjamanUpdated::class => [
            SendTransaksiPinjamanUpdateWebhook::class,
        ],
    ];
}
