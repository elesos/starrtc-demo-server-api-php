# starrtc_demo server api for PHP

starRTC demo后台api与数据库

一，自行部署
---
1，数据库脚本所在目录：sql/starRTC_demo.sql

2，配置config.php，修改appid,secret和guardToken

3，登录[starRTC后台](https://www.starrtc.com/login.html)，填上您的aec url

二，docker部署
---
```java
docker pull starrtc/starrtc_demo_server_sdk_php
docker run -d -it --name starrtc_demo_server_sdk_php -p 80:80 -p 443:443 starrtc/starrtc_demo_server_sdk_php /bin/bash
```
1，进入docker配置nginx，将域名与证书修改成自己的

2，配置config.php，修改appid,secret和guardToken

3，进入root目录，运行./run.sh启动nginx服务器，MySQL数据库和PHP

4，登录[starRTC后台](https://www.starrtc.com/login.html)，填上您的aec url