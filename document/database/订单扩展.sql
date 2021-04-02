-- 订单操作记录
CREATE TABLE
IF NOT EXISTS `order_operation` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `operation_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `order_sn` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `system_user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '系统用户ID',
    `operate` INT(11) NOT NULL DEFAULT '0' COMMENT '操作',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '订单操作记录';

-- 订单退款
CREATE TABLE
IF NOT EXISTS `order_refund` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `refund_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `order_sn` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `order_goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `refund_sn` varchar(128) NOT NULL DEFAULT '' COMMENT '退款编号，多次退款依据',
    `refund_amount` decimal(11, 2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
    `reason` varchar (200) not null default '' comment '退款原因',
    `description` varchar (400) not null default '' comment '退款描述',
    `images` varchar (2000) not null default '[]' comment '退款凭证',
    `order_status` int(11) NOT NULL DEFAULT '3' COMMENT '申请时订单状态',
    `audit_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1 待审核 2 通过 3 拒绝',
    `audit_user_id` varchar(128) NOT NULL DEFAULT '' COMMENT '审核用户',
    `audit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
    `audit_remark` varchar(200) NOT NULL DEFAULT '' COMMENT '审核说明',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '订单退款';

alter table `order_refund` add column `order_goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' after `order_sn`;
alter table `order_refund` add column `reason` varchar (200) not null default '' comment '退款原因' after `refund_amount`;
alter table `order_refund` add column `description` varchar (400) not null default '' comment '退款描述' after `reason`;
alter table `order_refund` add column `images` varchar (2000) not null default '[]' comment '退款凭证' after `description`;