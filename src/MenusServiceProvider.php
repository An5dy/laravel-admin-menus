<?php

/*
 * This file is part of the an5dy/laravel-admin-menus.
 *
 * (c) an5dy <846562014@qq.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace An5dy\LaravelAdminMenus;

use Illuminate\Support\ServiceProvider;

class MenusServiceProvider extends ServiceProvider
{
    protected $commands = [
        Console\ExportMenusCommand::class,
        Console\ImportMenusCommand::class,
    ];

    public function register()
    {
        $this->commands($this->commands);
    }
}
