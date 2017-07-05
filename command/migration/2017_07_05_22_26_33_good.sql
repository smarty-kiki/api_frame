# up
CREATE TABLE `good` (
      `id` bigint(20) NOT NULL,
      `version` int(11) NOT NULL,
      `create_time` datetime DEFAULT NULL,
      `update_time` datetime DEFAULT NULL,
      `delete_time` datetime DEFAULT NULL,
      `price` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# down
drop table `good`;
