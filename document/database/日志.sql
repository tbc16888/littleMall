-- 操作日志
CREATE TABLE
IF NOT EXISTS `system_operation_log` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `log_id` VARCHAR ( 128 ) NOT NULL,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '管理员ID',
    `module` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '模块名',
    `controller` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '控制器',
    `action` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '方法名',
    `ipv4` VARCHAR ( 60 ) NOT NULL COMMENT '操作IP',
    `desc` VARCHAR ( 200 ) NOT NULL COMMENT '操作描述',
    `params` text NOT NULL COMMENT '请求参数',
    `code` INT NOT NULL DEFAULT '0' COMMENT '请求业务码',
    `message` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '消息',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '删除 0 否 1 是',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
    PRIMARY KEY ( `id` ),
    UNIQUE KEY `log_id` ( `log_id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '系统操作记录';