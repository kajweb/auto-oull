# AutoPull 自动部署工具

> 本工具由私有项目复制，去除部分私有配置。
>
> **不能完全保证代码正常运行，代码仅供参考。**
>
> 目前仅支持由git pull触发自动更新代码工作流，暂不支持其他类型

## 安装
使用本工具前使用安装`php7`、`swoole`拓展

可选配置：`Nginx`

## 使用

### 系统配置

打开`/config/config.php`，配置相应的端口和守护模式。

### git配置

在`/config/git/*.php`文件中配置相应的代码。

| 序号 |  平台  | 使用文件名 |
| :--: | :----: | :--------: |
|  1   | gitee  | gitee.php  |
|  2   | github | github.php |
|  3   | gitlab | gitlab.php |

#### 配置说明

`项目名`作为键名，对应平台的项目名，其值为数组，且内含每个项目的配置

| 序号 |  键名  |                         值说明                         |
| :--: | :----: | :----------------------------------------------------: |
|  1   |  pull  |         收到githook时，是否需要执行`git pull`          |
|  2   |  path  |                      项目绝对路径                      |
|  3   | reload | 收到请求时，是否需要重启本项目（一般用于自身更新代码） |
|  4   | script |  执行`git pull`后执行的代码，一般为自动（编译）工作流  |



配置文件示例：

```php
// This file is in the `/config/git/` directory and is named github.php
<?php
return [
    "kajweb/githook" => [
		"pull" => true,
        "reload" => true,
		"path" => "/mnt/www/kajweb/githook",
		"script" => [
			"date +'%Y/%m/%d %H:%M:%S'>./build.txt",
			"npx marked -i wiki.md -o wiki/index.html",
            "echo markBuildTime",
            "\\rm -rf ./dist/*",
            "date +'%Y-%m-%d %H:%M:%S'>./dist/build.txt",
            "echo Mission Completed"
		]
	],
```

