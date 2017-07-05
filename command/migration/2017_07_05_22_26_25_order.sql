# up
CREATE TABLE `order` (
      `id` bigint(20) NOT NULL,
      `version` int(11) NOT NULL,
      `create_time` datetime DEFAULT NULL,
      `update_time` datetime DEFAULT NULL,
      `delete_time` datetime DEFAULT NULL,
      `customer_id` bigint(20) NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# down
drop table `order`;
