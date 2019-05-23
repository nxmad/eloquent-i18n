<?php

namespace Nxmad\EloquentI18n;

use Illuminate\Support\ServiceProvider;

class EloquentI18nProvider extends ServiceProvider
{
    /**
     * Register Service Provider.
     *
     * @return void
     */
    public function register()
    {
        // ...
    }

    /**
     * Boot Service Provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../files/database' => database_path(),
        ]);
    }
}
