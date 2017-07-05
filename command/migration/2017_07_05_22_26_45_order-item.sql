# up
CREATE TABLE `order_item` (
      `id` bigint(20) NOT NULL,
      `version` int(11) NOT NULL,
      `create_time` datetime DEFAULT NULL,
      `update_time` datetime DEFAULT NULL,
      `delete_time` datetime DEFAULT NULL,
      `order_id` bigint(20) NOT NULL,
      `good_id` bigint(20) NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# down
drop table `order_item`;
