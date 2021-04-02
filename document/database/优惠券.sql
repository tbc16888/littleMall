-- 优惠券
CREATE TABLE
IF NOT EXISTS `coupon` (
    `id` INT ( 11 ) UNSIGNED AUTO_INCREMENT,
    `coupon_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `coupon_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '优惠券类型',
    `coupon_name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '优惠券名称',
    `coupon_total` INT NOT NULL DEFAULT '0' COMMENT '优惠券总数',
    `coupon_desc` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '描述',
    `minimum` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '最低消费',
    `discount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '抵扣金额',
    `use_scope` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '使用范围 1 不限 2 部分商品 3 部分不可用',
    `effective_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '有效期类型 1 指定日期 2 指定领取后多少内有效',
    `effective_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
    `effective_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `effective_days` SMALLINT NOT NULL DEFAULT '0' COMMENT '有效天数',
    `quota` INT NOT NULL DEFAULT '1' COMMENT '限额',
    `distribution_method` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '发放方式 1 手动领取 2 注册赠送',
    `release_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发行开始时间',
    `release_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '状态 1 正常 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '优惠券';

-- 优惠券关联商品
CREATE TABLE
IF NOT EXISTS `coupon_goods` (
    `id` INT ( 11 ) UNSIGNED AUTO_INCREMENT,
    `coupon_goods_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '关类型 1 可用 2 不可以',
    `coupon_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '优惠券ID',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '优惠券商品';


-- 用户优惠券
CREATE TABLE
IF NOT EXISTS `coupon_user` (
    `id` INT ( 11 ) UNSIGNED AUTO_INCREMENT,
    `user_coupon_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `coupon_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '优惠券ID',
    `order_sn` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '订单编号',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 正常 2 已使用 3 已过期',
    `use_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '使用时间',
    `effective_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
    `effective_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '用户优惠券';
