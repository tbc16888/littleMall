-- 手机验证码
CREATE TABLE
IF NOT EXISTS `validate_mobile_code` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code_id` VARCHAR (128) NOT NULL UNIQUE,
    `mobile` VARCHAR (40) NOT NULL DEFAULT '' COMMENT '手机号',
    `action` VARCHAR (40) NOT NULL DEFAULT '' COMMENT '操作',
    `code` VARCHAR (40) NOT NULL DEFAULT '' COMMENT '手机验证码',
    `send_time` INT (11) NOT NULL DEFAULT '0' COMMENT '发送时间',
    `expire_time` INT (11) NOT NULL DEFAULT '0' COMMENT '过期时间',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime not null default current_timestamp,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '手机验证码';

-- 简单session表
CREATE TABLE
IF NOT EXISTS `session` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `session_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `data` VARCHAR ( 2000 ) NOT NULL DEFAULT '',
    `expire` INT ( 11 ) NOT NULL DEFAULT '0',
    PRIMARY KEY ( `id` )
) ENGINE = MEMORY DEFAULT CHARSET = utf8 COMMENT '数据库版本 session';