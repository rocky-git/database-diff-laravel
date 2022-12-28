### Usage
生成差异sql文件，对比源 connection 连接名称 deploy
```
php artisan database:diff deploy
```

执行差异sql文件
```
php artisan database:diff-run
```
执行差异sql文件，指定连接名称 deploy
```
php artisan database:diff-run --connection=deploy
```
