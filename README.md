## 介绍
全异步Swoole API开发框架，内置Promise，异步MySQL连接池，内存缓存管理，异步Task方案等


## 异步API

### 异步Task

```php
$promise = new Promise();
$promise->then(function($data) {
    var_dump($data);
});
$this->sendTask('SampleTask', 'sample_task',[
    'data' => 'Hello'
], function($result) use ($promise) {
    $promise->resolve($result['data']);
});
```

### 异步Redis

```php
$promise = new Promise();
$promise->then(function($data) {
    var_dump($data);
});
AsyncRedis::getInstance()->get("cache", $promise);
```

### 异步MySQL

```php
$promise = new Promise();
$promise->then(function($data) {
    var_dump($data);
});
MySQLStatement::prepare()
    ->select("Test", "*")->where([
        'id'    => 1
    ])->getOne($promise);

```

### 异步Http

```php
$promise = new Promise();
$promise->then(function($data) {
    var_dump($data);
});
AsyncHttpClient::get("www.baidu.com", "/" , $promise, true);

```

## 环境支持

PHP 5.5+ / PHP7 <br>
Swoole 1.8.8 以上版本 (不适用于Swoole 2.0以上版本)

## 运行

在项目目录下，执行以下命令
```bash
php start.php start
```
进入DEBUG模式。

执行以下命令
```bash
php start.php start -d
```
进入RELEASE模式。
两种模式使用不同目录下的配置文件，可分别配置。