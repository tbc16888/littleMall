CREATE TABLE IF NOT EXISTS `files` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_id` varchar(128) NOT NULL UNIQUE,
    `file_title` varchar(40) NOT NULL DEFAULT '',
    `file_cover` varchar(200) NOT NULL DEFAULT '' COMMENT '文件封面',
    `file_type` varchar(40) NOT NULL DEFAULT '' COMMENT '文件类型',
    `file_mime` varchar(200) NOT NULL DEFAULT '' COMMENT '文件MIME',
    `file_size` varchar(60) NOT NULL DEFAULT '0' COMMENT '文件大小',
    `file_url` varchar(200) NOT NULL DEFAULT '' COMMENT '文件地址',
    `directory` varchar(128) NOT NULL DEFAULT '' COMMENT '所在目录',
    `is_directory` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是目录 0 否 1 是',
    `sort_val` int(11) NOT NULL DEFAULT '50' COMMENT '排序',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1 禁用 2 使用',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '文件';

