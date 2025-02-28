# afdian-sponsor-wall
使用爱发电API自动整理展示赞赏者的页面

## 环境要求

php 7.4+

## 效果展示

[点此访问](https://gz-dp.foxdice.cn/)

## 部署方法

文件放置在web服务器中，设置步骤略。

在 `config/config.php` 中，填写从 [爱发电开发者页面](https://afdian.com/dashboard/dev) 获取的 `user_id` 和 `API Token` 。

为了确保安全，建议设置文件权限为 600，并在Nginx里设置禁止外部访问 `config` 目录。

```
location ~ /config\.php$ {
    deny all;
    return 403;
}
```
