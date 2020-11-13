#公共依赖类库-CMS类库
1. CMS的配置封装
2. CMS的使用


## CMS 配置公共配置

## 请在项目.env配置里添加如下配置

```shell script

REDIS_CMS_IS_OPEN=1 
REDIS_CMS_HOST=127.0.0.0 #换成自己服务的redis 地址
REDIS_CMS_PASSWORD=NULL #换成自己服务的redis 免密
REDIS_CMS_PORT=6379     
REDIS_CMS_DB_NUMBER=0  # redis 数据库空间标识

```

