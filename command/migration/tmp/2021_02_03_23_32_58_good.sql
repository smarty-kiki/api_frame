# up
create table if not exists `good` (
    `id` bigint(20) unsigned not null,
    `version` int(11) not null,
    `create_time` datetime default null,
    `update_time` datetime default null,
    `delete_time` datetime default null,
    `name` varchar(30) default null,
    primary key (`id`)
) engine=innodb default charset=utf8;

# down
drop table `good`;
