-- 用户表
CREATE TABLE
IF NOT EXISTS `user` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `account` VARCHAR ( 60 ) NOT NULL UNIQUE COMMENT '账号',
    `password` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '密码',
    `mobile` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '电话',
    `email` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '邮箱',
    `balance` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
    `frozen` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `integral` INT ( 11 ) NOT NULL DEFAULT '0' COMMENT '积分',
    `nick_name` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '昵称',
    `avatar` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '头像',
    `birthday` DATE NOT NULL DEFAULT '0000-00-00' COMMENT '出生日期',
    `gender` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '性别 1 男 2 女 3 未知',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 正常 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `delete_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT '用户';

-- 用户绑定表
CREATE TABLE
IF NOT EXISTS `user_bind` (
    `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bind_id` VARCHAR (128) NOT NULL UNIQUE,
    `user_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '用户ID',
    `platform` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '绑定平台',
    `open_id` VARCHAR (200) NOT NULL DEFAULT '',
    `union_id` VARCHAR (200) NOT NULL DEFAULT '',
    `bind_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '绑定时间',
    `nick_name` VARCHAR (60) NOT NULL DEFAULT '' COMMENT '第三方平台昵称',
    `is_delete` TINYINT (1) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '用户绑定';

-- 用户地址
CREATE TABLE
IF NOT EXISTS `user_address` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`address_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
	`user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
	`contact` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '联系人',
	`contact_number` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '联系电话',
	`gender` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '性别  1 男 2 女 3 保密',
	`province` VARCHAR ( 12 ) NOT NULL DEFAULT '' COMMENT '省份区域代码',
	`city` VARCHAR ( 12 ) NOT NULL DEFAULT '' COMMENT '城市区域代码',
	`district` VARCHAR ( 12 ) NOT NULL DEFAULT '' COMMENT '地区区域代码',
	`street` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '街道区域代码',
	`address` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '详细地址',
	`house_number` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '门牌号',
	`longitude` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '经度',
	`latitude` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '纬度',
	`is_default` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否默认地址',
	`status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 使用 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '用户地址';