-- 系统配置
CREATE TABLE
IF NOT EXISTS `system_config` (
     `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
     `config_id` varchar(128) NOT NULL UNIQUE,
     `label` varchar(40) NOT NULL DEFAULT '' COMMENT '标签名',
     `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
     `code` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项代码',
     `value` mediumtext NOT NULL COMMENT '配置值',
     `style` varchar(1000) NOT NULL DEFAULT '' COMMENT '样式属性',
     `parent_id` varchar(128) NOT NULL DEFAULT '' COMMENT '上级ID',
     `sort_weight` int NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠后',
     `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
     `options` text NOT NULL,
     `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
     `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '系统配置';