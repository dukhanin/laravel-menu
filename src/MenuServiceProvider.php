<?php

namespace Dukhanin\Menu;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{

    public function register()
    {
    }


    public function boot()
    {
        app()->singleton('menu', function ($app) {
            return new MenuHelper;
        });
    }
}
