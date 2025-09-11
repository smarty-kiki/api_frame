# 框架介绍
这是一个基于 PHP 作为后端语言，HTML、CSS、JavaScript 作为前端语言的 API 框架项目，使用 frame 框架编写。 
1. 使用 frame 框架编写
2. 使用 PHP 作为后端语言
3. 使用 HTML、CSS、JavaScript 作为前端语言

# 编码规范

## 命名规范
1. 变量命名：使用小写和下划线风格（snake_case）
2. 实体变量：变量若是保存的实体，则命名尽量使用实体名字和实体属性名字
3. 单复数区分：变量名要基于保存的数据是一个数据还是一个数据数组区分单数和复数
4. 关联数据命名：
   - 单条关联数据：实体名 + `_info`
   - 多条关联数据：实体名 + `_infos`
5. 时间变量命名：
   - 具体到秒的：使用 `time`
   - 只精确到天的：使用 `date`
6. 类名也都遵循小写和下划线风格来命名：
   - dao 类名：实体名 + `_dao`
   - entity 类名：实体名

## 代码格式规范
1. 大括号风格：function 后的大括号要换行开始，数组的中括号紧接着代码开始
2. 盘古之白：显示给使用者的文案，需要有中英文数字间的空格
3. 代码分段：controller 中的代码要适当添加空行做段落区分
   - return 前最好空一行
   - 获取输入和断言与业务逻辑之间最好空一行

# 文件结构
```
.  
├── command (命令行命令文件目录)  
│   ├── migration (数据库迁移文件目录)  
│   ├── queue_job (队列 job 文件目录)  
│   │   └── load.php (队列 job 加载文件)    
│   ├── entity.php (entity 命令)  
│   ├── migrate.php (migrate 命令)  
│   └── queue.php (queue 命令)  
├── config (配置文件目录)  
│   ├── development (开发环境配置覆盖目录)                               
│   ├── production (线上愿景配置覆盖目录)  
│   ├── beanstalk.php (队列 beanstalk 配置文件)  
│   ├── mongodb.php （mongodb 配置文件)  
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
│   │   │   │   └── team_manage_system.conf (框架推荐的 nginx 配置)  
│   │   │   └── supervisor  (supervisor 配置)  
│   │   │       └── queue_worker.conf (worker 的管理配置)  
│   │   └── production (线上环境)   
│   │       ├── nginx  
│   │       │   └── team_manage_system.conf  
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

# 框架使用的规范 controller 相关
1. 请求中绝大多数输入验证要在 controller 中完成，输入验证要通过框架提供的断言函数来完成异常的抛出，断言用 otherwise_error_code 函数来完成，注意 otherwise_error_code 函数中，错误码要使用常量来定义，并且函数的第一个参数判断为 false 时，才会抛出异常
2. 在 controller 中若要获取 url 中路径中的参数，在 function 的形参中获取即可
3. 在 controller 中获取 get 或者 post 参数，通过框架的 input 系列函数来获取
4. 在 controller 中，若要获取当前登录用户，通过框架的 get_logined_account 函数来获取
5. 在 controller 中，若要获取一个实体，通过 dao 层的 find 系列函数来获取，通过 dao() 来单例使用 dao
6. 在 controller 中调用实体创建，需要静态调用，如 xxx::create
7. 在 controller 中，若要返回 json 数据，通过框架的 json 系列函数来返回
8. controller 文件中对于操作动作的用词和实体不一样，如 controller 中用 add、detail、update、delete，实体中用 create、find、update、delete
9. 新实现的 controller 文件要添加到 public/index.php 中注册路由
10. 框架中在 controller 中创建实体时不需要调用 save 会经由工作单元自动判断是否需要保存


# 框架使用的规范 dao 相关
1. 新生成的 dao 等要添加在 domain/autoload.php 中，加入在 $class_maps 中，无需在 domain/load.php 中添加
2. dao 文件中要声明表名 protected $table_name = 'xxx';
3. dao 文件中要声明数据库配置 protected $db_config_key = 'default', 默认使用 default
4. dao 无需生成 find_by_id 方法，框架中已经实现
5. 查询的 SQL 语句需要美化格式，如 select 一行，join 一行，where 一行，order by 一行，limit 一行，SQL 前的引号可以跟在上一行最后，SQL 后的引号可以放在下一行开始，方便复制 SQL 语句时不带引号，但是要保持缩进
6. 使用相关有 binds 参数的 find 方法时，注意 binds 的使用方式，是类似如 ['name' => 'kiki'] 这样，在 SQL 中用 ‘:name’ 作占位符

# 框架使用的规范 entity 相关
1. 实现实体时要注意实现 create 方法来实现实体的创建
2. 新生成的 dao 等要添加在 domain/autoload.php 中，加入在 $class_maps 中，无需在 domain/load.php 中添加
3. 实体中 id、version、create_time、update_time、delete_time 属性已经在框架中实现，不需要具体的实体再实现
4. 实体中没有 struct_length 属性不需要实现
5. 实体中没有 struct_default 属性不需要实现

# 框架使用的规范 migration 相关
1. 实现实体时要注意同步实现对应的 dao、以及 migration 文件
2. migration 文件中的内容是以 sql 语句来实现，up 注释为迁移的描述，down 注释为迁移的回滚描述
3. 生成实体 migration 文件时，注意 time 类字段用 datetime 类型来存储，不需要创建关联关系的外键，关联关系需要创建索引，要与 delete_time 字段一起创建
4. 生成 migration 文件时，注意 SQL 关键词用小写，索引名称用 idx_ 开头，拼接索引字段名字而命名，delete_time 不用在命名中体现，主键放到最后来声明
5. 生成 migration 文件时，表名要用单数
6. migration 文件的名字要用生成那刻的时间来命名
7. migration 文件中 id 用 bigint(20) unsigned not null，不需要 auto_increment，外键字段也同理
8. migration 文件中 version 用 int(11) not null
9. migration 文件中，创建表需要判断 not exists

# 框架使用的规范，对框架函数使用的提醒
1. 框架 function.php 中有很多提升可读性的判断，如 not_empty、not_null、all_empty、all_not_empty、has_empty、has_null、all_not_empty、all_not_null 等，在实现时可以参考使用
2. 时间处理逻辑可以用 datetime 函数而非使用 PHP 原始的方法，datetime 函数使用示例：datetime('now')

# 配置文件相关
1. 新加的错误码要定义在 config/error_code.php 文件中，数组的键名是错误码常量，值是报错的提示文字
2. 新实现的功能要在 config/ability.php 中添加对应的权限，在 config/request_ability.php 中声明对应的请求路由对应的权限项，在 config/admin_ability.php 中添加对应的管理员权限，管理员所有权限都有
3. 新实现的功能要在 view/index/index.php 中添加对应的菜单，并且判断对应的权限项来控制菜单展示

# 框架使用中特殊字段类型在各处的规范，枚举模式
1. dao 中 SQL 中如果判断枚举模式的字段，要用实体中的常量来写 binds，如 ['status' => xxx::STATUS_COMPLETED'] 这样，在 SQL 中用 ‘:status’ 作占位符
2. 对 entity 中枚举模式字段，在 view 中获取描述时，用 get_xxx_description 方法，在 view 中设置时，用 set_xxx_xxx 方法，判断枚举模式字段时，用 is_xxx_xxx 方法
3. 对 entity 中枚举模式字段，在 controller 中获取描述时，用 get_xxx_description 方法，在 controller 中设置时，用 set_xxx_xxx 方法，判断枚举模式字段时，用 is_xxx_xxx 方法
4. 实体中如果有字段是枚举模式的，需要定义一系列常量的实现，如 
```
    public static $struct_data_types = [
        'is_admin' => 'enum',
    ];

    const IS_ADMIN_YES = 'YES';
    const IS_ADMIN_NO = 'NO';

    const IS_ADMIN_MAPS = [
        self::IS_ADMIN_YES => '是',
        self::IS_ADMIN_NO => '否',
    ];

    public static function struct_validators($property)
    {
        $validators = [
            'is_admin' => self::IS_ADMIN_MAPS,
        ];

        return $validators[$property] ?? false;
    }

    public function get_is_admin_description(): string
    {
        return self::IS_ADMIN_MAPS[$this->is_admin];
    }

    public function is_admin_is_yes(): bool
    {
        return $this->is_admin === self::IS_ADMIN_YES;
    }

    public function set_is_admin_yes(): string
    {
        return $this->is_admin = self::IS_ADMIN_YES;
    }

    public function is_admin_is_no(): bool
    {
        return $this->is_admin === self::IS_ADMIN_NO;
    }

    public function set_is_admin_no(): string
    {
        return $this->is_admin = self::IS_ADMIN_NO;
    }
```