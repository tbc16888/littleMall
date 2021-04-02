--订单表
CREATE TABLE
IF NOT EXISTS `order` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `order_sn` VARCHAR ( 128 ) NOT NULL UNIQUE COMMENT '订单编号',
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `group_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '运营组ID',
    `warehouse_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '仓库ID',
    `goods_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '商品金额',
    `order_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
    `order_status` INT ( 11 ) NOT NULL DEFAULT '1000' COMMENT '',
    `order_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下单时间',
    `buyer_remark` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '订单备注',
    `seller_remark` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '商家备注',
    `integral` INT ( 11 ) NOT NULL DEFAULT '0' COMMENT '积分兑换',
    `integral_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '积分抵扣金额',
    `user_coupon_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户优惠券ID',
    `coupon_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '优惠券金额',
    `change_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '直接改价优惠',
    `total_discount_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '总优惠金额',
    `address_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '收货人信息ID',
    `contact` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '联系人',
    `contact_number` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '联系电话',
    `gender` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '联系人性别 1 男 2 女 3 未知',
    `province` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '省份区域代码',
    `city` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '城市区域代码',
    `district` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '地区区域代码',
    `street` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '街道区域代码',
    `address` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '详细地址',
    `house_number` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '门牌号',
    `longitude` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '经度',
    `latitude` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '纬度',
    `pay_deadline_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付有效截至时间',
    `pay_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
    `pay_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '支付类型 1 现场支付 1 支付宝 2 微信 ',
    `pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
    `pay_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '支付状态 1 未支付 2 已支付',
    `out_trade_no` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '支付流水号',
    `shipping_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '1 邮寄 2 及时送 3 自提',
    `shipping_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '运送状态 1 未发货 2 已发货 3 已收货',
    `shipping_fee` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '运费',
    `shipping_id` INT NOT NULL DEFAULT '0' COMMENT '运送公司表ID',
    `shipping_sn` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '运送单号',
    `shipping_name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '运送公司',
    `shipping_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货时间',
    `arrive_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '送达时间',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否删除 0 否 1 是',
    `is_comment` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否评论 0 否 1 是',
    `is_appeal` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否申诉 0 否  1 是 2 处理结束',
    `is_refund` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否退款 0 否 1 是',
    `is_visible` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '是否可见 0 否 1 是 （非正常订单过滤使用）',
    `cancel_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '取消时间',
    `refund_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '退款时间',
    `refund_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '0 未退款 1 部分退款 2 全部退款',
    `refund_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
    `refund_method` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '退款方式 1 原路 2 线下',
    `complete_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '完成时间',
    `delete_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '订单';
ALTER TABLE `cbt_order` ADD INDEX `user_id`(`user_id`) USING BTREE;
ALTER TABLE `cbt_order` ADD INDEX `order_time`(`order_time`) USING BTREE;
alter table `cbt_order` add column `refund_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '0 正常 1 部分退款中 2 部分已经退款 3 全部退款中 4 全部已退款' after `refund_time`;

-- 订单商品
CREATE TABLE
IF NOT EXISTS `order_goods` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_goods_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `order_sn` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `goods_sku_id` VARCHAR ( 128 ) NOT NULL DEFAULT '',
    `goods_version` INT(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '商品版本',
    `goods_price` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '单价',
    `goods_number` SMALLINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买数量',
    `goods_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '结算价格',
    `integral` INT(11) NOT NULL DEFAULT '0' COMMENT '积分',
    `activity_price` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '活动价格',
    `discount_price` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
    `is_comment` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '评论 0 否 1 是',
    `refund_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '退款状态 1 退款中 2 已退款',
    `is_release_stock` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '释放库存 0 否 1 是',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '订单商品';
ALTER TABLE `order_goods` ADD COLUMN `goods_amount` DECIMAL ( 11, 2 ) NOT NULL DEFAULT '0.00' COMMENT '结算价格' after `goods_number`;
ALTER TABLE `order_goods` ADD INDEX `order_id`(`order_id`) USING BTREE;
ALTER TABLE `order_goods` ADD INDEX `goods_id`(`goods_id`) USING BTREE;
ALTER TABLE `order_goods` ADD INDEX `create_time`(`create_time`) USING BTREE;

