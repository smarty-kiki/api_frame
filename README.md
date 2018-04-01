# api_frame
由 [frame](https://github.com/smarty-kiki/frame#frame) 框架衍生的单层 API 框架, 供快速开发使用

## 目录结构及文件说明

```
.  
├── command (命令行命令文件目录)  
│   ├── migration (数据库迁移文件目录)  
│   ├── queue_job (队列 job 文件目录)  
│   │   └── load.php (队列 job 加载文件)  
│   ├── controller.php (controller 命令，可以快速的基于表结构生成 restful 接口)  
│   ├── entity.php (entity 命令)  
│   ├── migrate.php (migrate 命令)  
│   └── queue.php (queue 命令)  
├── config (配置文件目录)  
│   ├── development (开发环境配置覆盖目录)                               
│   ├── production (线上愿景配置覆盖目录)  
│   ├── beanstalk.php (队列 beanstalk 配置文件)  
│   ├── mysql.php (数据库 mysql 配置文件)  
│   └── redis.php (存储 redis 配置文件)  
├── controller (控制器文件目录)  
│   └── index.php (helloworld 控制器)  
├── domain (领域层目录)  
│   ├── dao (DAO 层文件目录)  
│   ├── entity (实体层文件目录)  
│   ├── knowledge (知识层文件目录)  
│   └── load.php (领域层加载文件)  
├── frame (frame 框架目录，[点此查看明细说明](https://github.com/smarty-kiki/frame#目录结果及文件说明))   
├── interceptor (拦截器目录)  
├── project (项目相关文件目录)  
│   ├── config (配置文件目录)  
│   │   ├── development (开发环境)  
│   │   │   ├── nginx (nginx 配置)  
│   │   │   │   └── api_frame.conf (框架推荐的 nginx 配置)  
│   │   │   └── supervisor  (supervisor 配置)  
│   │   │       └── queue_worker.conf (worker 的管理配置)  
│   │   └── production (线上环境)   
│   │       ├── nginx  
│   │       │   └── api_frame.conf  
│   │       └── supervisor  
│   │           └── queue_worker.conf  
│   ├── tool (工具脚本目录)  
│   │   ├── classmap.sh (生成 ORM load 文件)  
│   │   ├── naming_project.sh (快速修改本项目中的 nginx、supervisor 等配置中与项目目录有关的项目名称方便创建新项目使用)  
│   │   └── start_dev_server.sh (快速启动开发环境的脚本，基于 docker)  
├── public (入口文件目录)  
│   ├── cli.php (命令行入口文件)  
│   └── index.php (web 请求入口文件)  
├── util (工具类文件目录)  
│   └── load.php (工具类加载文件)  
├── LICENSE  
├── README.md  
└── bootstrap.php (框架通用加载文件)  
```
## 10 秒看到 helloworld

1. 先将代码 clone 或者下载到本地
2. 确保机器上有 docker 环境
3. 执行代码中的脚本快速启动环境 sh project/tool/start_dev_server.sh  
4. 输入当前用户密码。此处是为了开发方便映射了 80 和 3306 端口，若不允许使用 80 可以手动修改第三条提到的脚本更换端口

