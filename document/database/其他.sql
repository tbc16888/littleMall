CREATE TABLE `storage` (
   `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
   `storage_id` varchar(128) NOT NULL UNIQUE,
   `key` varchar(128) NOT NULL,
   `data` varchar(2000) NOT NULL DEFAULT '' COMMENT '数据',
   `expire` int NOT NULL DEFAULT '0' COMMENT '有效到期时间',
   `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
   `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
   `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT '临时数据存储';