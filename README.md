# web-utils

轻量级 Web 工具箱，无数据库，无框架依赖。

## 示例站点

- [https://apps.opentdp.org](https://apps.opentdp.org)

## Nginx 伪静态

```nginx
if (!-e $request_filename) {
    rewrite ^(.*)$ /index.php?s=/$1 last;
}
```
