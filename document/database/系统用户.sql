-- 系统用户表
CREATE TABLE
IF NOT EXISTS `system_user` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `account` VARCHAR ( 60 ) NOT NULL UNIQUE COMMENT '账号',
    `password` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '密码',
    `mobile` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '电话',
    `email` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '邮箱',
    `nick_name` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '昵称',
    `avatar` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '头像',
    `birthday` DATE NOT NULL DEFAULT '0000-00-00' COMMENT '出生日期',
    `role_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '角色ID',
    `gender` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '性别 1 男 2 女 3 未知',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 正常 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT '系统用户';

-- 系统用户角色表
CREATE TABLE
IF NOT EXISTS `system_role` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `role_name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '角色名称',
    `role_desc` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '角色描述',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '角色状态 1 正常 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT charset = utf8 COMMENT '系统角色';

-- 系统用户角色表
CREATE TABLE
IF NOT EXISTS `system_role_menu` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_menu_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `role_id` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `menu_id` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `menu_code` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '系统角色菜单';

-- 系统菜单
CREATE TABLE
IF NOT EXISTS `system_menu` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `menu_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `menu_code` VARCHAR ( 60 ) NOT NULL COMMENT '菜单唯一码' UNIQUE,
    `menu_name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '菜单名称',
    `full_menu_name` VARCHAR ( 200 ) NOT NULL DEFAULT '',
    `icon` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '菜单图标',
    `link` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '链接地址',
    `parent_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '上级菜单ID',
    `sort_weight` INT (11) NOT NULL DEFAULT '50' COMMENT '排序',
    `module` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '模块名',
    `controller` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '控制器',
    `action` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '方法名',
    `is_auth` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否需要权限认证 0 否 1 是',
    `route_name` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT 'vue 路由名称',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '系统菜单';