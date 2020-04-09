<h1 align="center"> laravel-admin-menus </h1>

<p align="center"> laravel-admin 左侧菜单栏扩展包，基于 config/menus.php 配置文件生成左侧菜单。</p>

![StyleCI build status](https://github.styleci.io/repos/254318217/shield) 

## 安装

```shell
$ composer require an5dy/laravel-admin-menus -vvv
```

## 使用

### 在 laravel 中使用
#### 导出 lravel-admin 左侧菜单，生成 config/menus.php 配置文件。
```
$ php artisan admin:menus:export
// 生产环境
$ php artisan admin:menus:export --force
```
#### 读取 config/menus.php 配置文件，生成 laravel-admin 左侧菜单。
```
$ php artisan admin:menus:import
// 生产环境
$ php artisan admin:menus:import --force
```
## License

MIT