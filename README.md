# 介绍

本项目采用 [hyperf 3.0](https://hyperf.wiki/3.0/#/README) 框架。

## 服务器要求

- PHP >= 8.0 and <= 8.1
- 以下任一网络引擎
    - Swoole PHP 扩展 >= 4.5，并关闭了 `Short Name`
    - Swow PHP 扩展 (Beta)
- JSON PHP 扩展
- Pcntl PHP 扩展
- OpenSSL PHP 扩展（如需要使用到 HTTPS）
- PDO PHP 扩展 （如需要使用到 MySQL 客户端）
- Redis PHP 扩展 （如需要使用到 Redis 客户端）
- Protobuf PHP 扩展 （如需要使用到 gRPC 服务端或客户端）

## docker 下运行

```shell

docker run --name hyperf-project \
-v /Users/pudongping/codes/hyperf-project:/hyperf-project \
--net=alex-network \
-p 9510-9520:9510-9520 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.0-alpine-v3.16-swoole

# docker 容器内可能要添加阿里云 composer 镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer

```

## 启动项目

```shell

cd path/to/install

php bin/hyperf.php start

```

## 目录规范

1. 业务代码全部写在 `Services` 目录中
2. `Request` 和 `Response` 只能在 `Controller` 中使用
3. `response` 返回的 `code` 和 `message` 信息应全部在 `Constants` 目录下的 `ErrorCode.php` 文件中定义
4. 请求数据的验证代码逻辑应统一放在 `Request` 目录下

## 代码规范

1. **必须** 遵循 [PSR-4](https://learnku.com/docs/psr/psr-4-autoloader-meta/1610) 和 [PSR-12](https://learnku.com/docs/psr/psr-12-extended-coding-style-guide/5789) 规范
2. **建议** 遵循 ***SOLID*** 编码准则
3. 所有注释、文档书写 **建议** 遵循 [中文文案排版指北](https://github.com/sparanoid/chinese-copywriting-guidelines)

## 关于分支及标签

- [CHANGELOG](./CHANGELOG.md)

## 其他

本项目许多核心功能均来自 [pudongping/hyperf-kit](https://github.com/pudongping/hyperf-kit) 扩展包。

以下文件可能在你项目中用不上，建议拉取代码之后进行删除。

- `app/Controller/DemoController.php` 
- `test/Cases/DemoTest.php`