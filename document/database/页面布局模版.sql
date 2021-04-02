-- 应用布局模版
CREATE TABLE
IF NOT EXISTS `app_layout_template` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `template_name` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '模版名称',
    `page` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '页面路径',
    `config` VARCHAR (2000) NOT NULL DEFAULT '' COMMENT '页面配置',
    `module` TEXT NOT NULL COMMENT '模块',
    `sketch` VARCHAR ( 400 ) NOT NULL DEFAULT '' comment '效果图',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 启用 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT,
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;
alter table `app_layout_template` add column `config` VARCHAR ( 2000 ) NOT NULL DEFAULT '' after `page`;