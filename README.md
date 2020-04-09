<h1 align="center"> laravel-admin-menus </h1>

<p align="center"> laravel-admin 左侧菜单栏扩展包，基于 config/menus.php 配置文件生成左侧菜单。</p>


## 安装

```shell
$ composer require an5dy/laravel-admin-menus -vvv
```

## 使用

### 在 laravel 中使用
#### 导出 lravel-admin 左侧菜单，生成 config/menus.php 配置文件。
```
php artisan admin:menus:export
```
#### 读取 config/menus.php 配置文件，生成 laravel-admin 左侧菜单。
```
php artisan admin:menus:import
```
## License

MIT