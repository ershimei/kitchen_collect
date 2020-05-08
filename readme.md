#数据抓取

## 环境变量
1、laravel 5.6 + 
2、具体环境变量参考laravel https://xueyuanjun.com/post/8650.html文档

## 数据格式
1、一级目录为20200508 格式  例： /data/www/resouce/20200505
2、文件名为20200505_789798  例：/data/www/resouce/20200505/20200505_12312.txt
3、具体文件类型查看类型定义$filter_ext

## 部署命令
1、配置env 中必要的变量
2、执行php artisan migrate 
3、添加crontab php artisan collect:resource


## 本地测试
cd到根目录， 执行php artisan collect:resource , 如果文件过多的话，执行的时间会长一些， 提示complate表示执行成功。 

