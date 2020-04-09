<?php

/*
 * This file is part of the an5dy/laravel-admin-menus.
 *
 * (c) an5dy <846562014@qq.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace An5dy\LaravelAdminMenus\Console;

use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class ImportMenusCommand extends Command
{
    use ConfirmableTrait;

    public $signature = 'admin:menus:import {--force}';

    protected $description = '导入 laravel-admin 菜单';

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $config = $this->getConfig();
        if ($config) {
            $this->createPermissions($config['permissions']);
            $this->createRoles($config['roles']);
            $this->createMenus($config['menus']);

            $this->info('laravel-admin 左侧菜单生成成功。');
        } else {
            $this->error('laravel-admin 左侧菜单配置文件 config/menus.php 不存在，请先设置配置文件。');
        }
    }

    protected function createPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            Permission::updateOrCreate([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
            ], [
                'http_method' => implode(',', $permission['http_method']),
                'http_path' => implode("\n", $permission['http_path']),
            ]);
        }
    }

    protected function createRoles(array $roles)
    {
        foreach ($roles as $role) {
            $roleObj = Role::updateOrCreate([
                'name' => $role['name'],
                'slug' => $role['slug'],
            ]);
            $permissionIds = Permission::whereIn('slug', $role['permissions'])->pluck('id');

            $roleObj->permissions()->sync($permissionIds);
        }
    }

    protected function createMenus(array $menus)
    {
        foreach ($menus as $menu) {
            $menuObj = Menu::updateOrCreate([
                'uri' => $menu['uri'],
            ], [
                'title' => $menu['title'],
                'order' => (int) $menu['order'],
                'icon' => $menu['icon'],
                'permission' => $menu['permission'],
            ]);

            $this->createChildrenMenus($menuObj, $menu);
            $this->createMenuRoles($menuObj, $menu);
        }
    }

    protected function createChildrenMenus(Menu $menuObj, array $menu)
    {
        $children = $menu['children'] ?? null;
        if ($children) {
            foreach ($children as $child) {
                $childMenu = Menu::updateOrCreate([
                    'parent_id' => $menuObj->id,
                    'uri' => $child['uri'],
                ], [
                    'title' => $child['title'],
                    'icon' => $child['icon'],
                    'order' => $child['order'],
                    'permission' => $menu['permission'],
                ]);

                $this->createChildrenMenus($childMenu, $child);
                $this->createMenuRoles($childMenu, $child);
            }
        }
    }

    protected function createMenuRoles(Menu $menuObj, array $menu): void
    {
        $roles = $menu['roles'] ?? null;
        if ($roles) {
            $roleIds = Role::whereIn('slug', $roles)->pluck('id');

            $menuObj->roles()->sync($roleIds);
        }
    }

    protected function getConfig(): array
    {
        return config('menus', []);
    }
}
