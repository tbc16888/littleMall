-- 用户余额
CREATE TABLE
IF NOT EXISTS `user_balance` (
    `id` INT ( 11 ) UNSIGNED AUTO_INCREMENT,
    `log_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `change_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '改变金额',
    `change_type` INT ( 11 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '改变类型',
    `change_desc` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '改变描述',
    `change_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '改变时间',
    `after_change` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '改变后数量',
    `audit_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '2 待审 3 通过 4 拒绝',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '用户余额变动记录';

-- 用户积分日志
CREATE TABLE
IF NOT EXISTS `user_integral` (
    `id` INT ( 11 ) UNSIGNED auto_increment,
    `log_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `change_amount` INT ( 11 ) NOT NULL DEFAULT '0' COMMENT '改变额度',
    `change_type` INT ( 11 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '改变类型',
    `change_desc` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '改变描述',
    `change_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '改变时间',
    `after_change` INT ( 11 ) NOT NULL DEFAULT '0' COMMENT '改变后数量',
    `audit_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '2 待审 3 通过 4 拒绝',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '用户积分变动记录';