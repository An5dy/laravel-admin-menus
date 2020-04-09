<?php

/*
 * This file is part of the an5dy/laravel-admin-menus.
 *
 * (c) an5dy <846562014@qq.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace An5dy\LaravelAdminMenus\Console;

use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;

class ExportMenusCommand extends Command
{
    use ConfirmableTrait;

    public $signature = 'admin:menus:export {--force}';

    protected $description = '导出 laravel-admin 菜单';

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $stub = $this->getStub();
        $spaces = $this->getSpaces();
        $stub = $this->setPermissions($stub, $spaces);
        $stub = $this->setRoles($stub, $spaces);
        $stub = $this->setMenus($stub, $spaces);
        $this->laravel['files']->put(config_path().DIRECTORY_SEPARATOR.'menus.php', $stub);

        $this->info('菜单导出成功，文件路径为 config/menus.php。');

        return 0;
    }

    protected function getStub(): string
    {
        return $this->laravel['files']->get(__DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'menus.stub');
    }

    protected function setPermissions(string $stub, string $spaces): string
    {
        $permission = Permission::get()
            ->map(function (Permission $permission) use ($spaces) {
                $permission = [
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'http_method' => $permission->http_method,
                    'http_path' => explode("\n", $permission->http_path),
                ];

                return $this->exportArray($permission, $spaces);
            })->implode(','.PHP_EOL);

        return str_replace('DummyPermissions', $permission, $stub);
    }

    protected function setRoles(string $stub, string $spaces): string
    {
        $roles = Role::with('permissions')
            ->get()
            ->map(function (Role $role) use ($spaces) {
                $role = [
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'permissions' => $role->permissions->pluck('slug')->all(),
                ];

                return $this->exportArray($role, $spaces);
            })->implode(','.PHP_EOL);

        return str_replace('DummyRoles', $roles, $stub);
    }

    protected function setMenus(string $stub, string $spaces): string
    {
        $menus = Collection::make(Admin::menu())
            ->map(function ($menu) use ($spaces) {
                return $this->generateMenuTree($menu, $spaces);
            })->implode(','.PHP_EOL);

        return str_replace('DummyMenus', $menus, $stub);
    }

    protected function exportArray(array $data, string $spaces): string
    {
        $str = str_repeat($spaces, 2).'['.PHP_EOL;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $value = '[]';
                } else {
                    if ('children' === $key) {
                        $value = '['.implode('\', \'', $value).']';
                    } else {
                        $value = '[\''.implode('\', \'', $value).'\']';
                    }
                }
                $str .= str_repeat($spaces, 3).'\''.$key.'\' => '.$value.','.PHP_EOL;
            } else {
                $str .= str_repeat($spaces, 3).'\''.$key.'\' => \''.$value.'\','.PHP_EOL;
            }
        }

        $str .= str_repeat($spaces, 2).']';

        return $str;
    }

    protected function generateMenuTree(array $data, string $spaces)
    {
        $menu = [
            'order' => $data['order'],
            'title' => $data['title'],
            'icon' => $data['icon'],
            'uri' => $data['uri'],
            'permission' => $data['permission'],
            'roles' => Collection::make($data['roles'])->pluck('slug')->toArray(),
        ];

        if (isset($data['children']) && $children = $data['children']) {
            $tmp = '';
            foreach ($children as $child) {
                $menuTree = $this->generateMenuTree($child, $spaces);
                $tmp .= $menuTree.','.PHP_EOL;
            }
            $menu['children'] = [PHP_EOL.$tmp];
        } else {
            $menu['children'] = [];
        }

        return $this->exportArray($menu, $spaces);
    }

    protected function getSpaces(int $length = 4): string
    {
        return str_pad('', $length, ' ', STR_PAD_RIGHT);
    }
}
