-- 商品模型
CREATE TABLE
IF NOT EXISTS `goods_model` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `model_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `model_name` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '类型名称',
    `sort_weight` INT(11) NOT NULL DEFAULT '50' COMMENT '排序权重',
    `icon` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '图标',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 使用 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品模型';

-- 商品模型属性
CREATE TABLE
IF NOT EXISTS `goods_model_attribute` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `attr_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `model_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '类型ID',
    `attr_name` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '属性名称',
    `attr_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '属性类型 1 基础 2 规格',
    `options` text NOT NULL COMMENT '可选值，一行一个',
    `status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '状态 1 使用 2 禁用',
    `sort_weight` INT NOT NULL DEFAULT '50' COMMENT '排序权重',
    `input_type` VARCHAR ( 40 ) NOT NULL DEFAULT '' COMMENT '操作类型',
    `unit` VARCHAR ( 20 ) NOT NULL DEFAULT '' COMMENT '单位',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品模型属性';

-- 商品分类
CREATE TABLE
IF NOT EXISTS `goods_category` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_id` VARCHAR (128) NOT NULL UNIQUE,
	`model_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '模型ID',
	`cat_name` VARCHAR (60) NOT NULL DEFAULT '' COMMENT '分类名称',
	`parent_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '上级ID',
	`cat_path` VARCHAR(2000) NOT NULL COMMENT '分类路径,英文逗号隔开的ID',
	`icon` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '图标',
	`sort_weight` INT NOT NULL DEFAULT '50' COMMENT '排序值,越大越前',
    `children` INT(11) NOT NULL DEFAULT '0' COMMENT '子节点数量',
    `goods_sn_prefix` VARCHAR (40) NOT NULL DEFAULT '' COMMENT '商品货号前缀',
	`status` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '状态 1 使用 2 禁用',
	`is_delete` TINYINT (1) NOT NULL DEFAULT '0',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品分类';

-- 商品属性
CREATE TABLE
IF NOT EXISTS `goods_attr` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `goods_attr_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `goods_version` INT ( 11 ) NOT NULL DEFAULT '1' COMMENT '商品版本',
    `attr_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '属性ID',
    `attr_value` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '属性值',
    `attr_value_label` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '属性值标签',
    `remark` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '备注',
    `image` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '图片',
    `attr_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '属性类型 1 基础 2 规格',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品属性';

-- 商品评价
CREATE TABLE
IF NOT EXISTS `goods_comment` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `comment_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `order_goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '订单商品ID',
    `score` tinyint ( 1 ) NOT NULL DEFAULT '5' COMMENT '评分',
    `service`  tinyint ( 1 ) NOT NULL DEFAULT '5' COMMENT '服务评分',
    `text` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '描述',
    `images` VARCHAR ( 2000 ) NOT NULL DEFAULT '' COMMENT '图片',
    `video` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '视频',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品评价';

-- 商品收藏
CREATE TABLE
IF NOT EXISTS `goods_collect` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `collect_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime not null default current_timestamp,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品收藏';

-- 商品浏览
CREATE TABLE
IF NOT EXISTS `goods_browse` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `browse_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime not null default current_timestamp,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品浏览';

-- 商品评论
CREATE TABLE
IF NOT EXISTS `goods_comment` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `comment_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `user_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '用户ID',
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `order_goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '订单商品ID',
    `star` tinyint (1) not null default '5' comment '评分',
    `text` varchar (400) not null default '',
    `images` varchar (2000) not null default '[]' comment '图片',
    `audit_status` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '审核状态 1 默认 2 待审 3 通过 4 拒绝',
    `audit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
    `audit_user` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '审核用户',
    `audit_remark` VARCHAR ( 500 ) NOT NULL DEFAULT '' COMMENT '审核说明',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime not null default current_timestamp,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品评论';


-- 商品品牌
CREATE TABLE
IF NOT EXISTS `goods_brand` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `brand_id` VARCHAR (128) NOT NULL UNIQUE,
    `brand_name` VARCHAR (40) NOT NULL DEFAULT '' COMMENT '品牌名称',
    `brand_logo` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '品牌LOGO',
    `brand_site` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '官网',
    `brand_desc` VARCHAR (2000) NOT NULL DEFAULT '',
    `first_letter` VARCHAR (2) NOT NULL DEFAULT '' COMMENT '首字母',
    `sort_weight` INT (11) NOT NULL DEFAULT '50' COMMENT '排序',
    `status` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '状态 1 使用 2 禁用',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` datetime not null default current_timestamp,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品品牌';


-- 商品
CREATE TABLE
IF NOT EXISTS `goods` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `goods_id` VARCHAR (128) NOT NULL UNIQUE ,
    `goods_sn` VARCHAR (60) NOT NULL DEFAULT '' COMMENT '商品编码',
    `goods_name` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '商品名称',
    `goods_short_name` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '商品短名称',
    `goods_image` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '商品图片',
    `goods_version` INT(11) NOT NULL DEFAULT '1' COMMENT '商品版本',
    `goods_type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '商品类型',
    `model_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '商品模型',
    `cat_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '主分类ID',
    `brand_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '品牌ID',
    `sku_total` SMALLINT (6) NOT NULL DEFAULT '0' COMMENT 'SKU 总数',
    `stock` INT (11) NOT NULL DEFAULT '0' COMMENT '单规格的商品库存',
    `stock_warn` SMALLINT (6) NOT NULL DEFAULT '0' COMMENT '库存预警',
    `stock_total` INT (11) NOT NULL DEFAULT '0' COMMENT '总库存，所有规格',
    `shop_price` DECIMAL (11, 2) NOT NULL DEFAULT '0.00' COMMENT '销售价格',
    `market_price` DECIMAL (11, 2) NOT NULL DEFAULT '0.00' COMMENT '市场价格',
    `trade_mode` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '交易方式 1 一口价 2 预售',
    `weight` DECIMAL (11, 2) NOT NULL DEFAULT '0.00' COMMENT '商品重量，单位克',
    `video_url` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '短视频地址',
    `detail` MEDIUMTEXT NOT NULL COMMENT '商品描述',
    `detail_mobile` MEDIUMTEXT NOT NULL COMMENT '商品描述-手机端',
    `use_integral` INT (11) NOT NULL DEFAULT '0' COMMENT '可以使用的积分',
    `use_integral_type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '积分使用类型',
    `sort_weight` INT (11) UNSIGNED NOT NULL DEFAULT '50' COMMENT '排序值',
    `sale_status` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '销售状态 1 仓库 2 上架',
    `sales_volume` INT (11) NOT NULL DEFAULT '0' COMMENT '真实销量',
    `virtual_sales_volume` INT NOT NULL DEFAULT '0' COMMENT '虚拟销量',
    `is_delete` TINYINT (1) NOT NULL DEFAULT '0',
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品';

ALTER table `goods` add column `use_integral_type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '积分使用类型' after `use_integral`;

-- 商品图片
CREATE TABLE
IF NOT EXISTS `goods_image` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `image_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `goods_version` INT ( 11 ) NOT NULL DEFAULT '1' COMMENT '商品版本',
    `image_url` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '图片路径',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` ),
    KEY `goods_id` ( `goods_id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品图片';
-- 商品属性
CREATE TABLE
IF NOT EXISTS `goods_attribute` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `goods_attr_id` VARCHAR ( 128 ) NOT NULL UNIQUE,
    `goods_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '商品ID',
    `goods_version` INT ( 11 ) NOT NULL DEFAULT '1' COMMENT '商品版本',
    `attr_id` VARCHAR ( 128 ) NOT NULL DEFAULT '' COMMENT '属性ID',
    `attr_type` TINYINT ( 1 ) NOT NULL DEFAULT '1' COMMENT '属性类型 1 基础 2 规格',
    `attr_value` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '属性值',
    `attr_value_label` VARCHAR ( 200 ) NOT NULL DEFAULT '' COMMENT '属性值标签',
    `remark` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '备注',
    `image` VARCHAR ( 400 ) NOT NULL DEFAULT '' COMMENT '图片',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品属性';

-- 商品规格
CREATE TABLE
IF NOT EXISTS `goods_sku` (
    `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `goods_sku_id` VARCHAR (128) NOT NULL UNIQUE,
    `goods_sku_key` VARCHAR (128) NOT NULL DEFAULT '' COMMENT 'sku 标识',
    `goods_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '商品ID',
    `goods_version` INT NOT NULL DEFAULT '1' COMMENT '商品版本',
    `goods_attr` VARCHAR (2000) NOT NULL DEFAULT '' COMMENT '规格属性值的',
    `goods_sku_text` VARCHAR (2000) NOT NULL DEFAULT '',
    `goods_image` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '商品图片',
    `shop_price` DECIMAL (11, 2) NOT NULL DEFAULT '0.00' COMMENT '单价',
    `stock` INT (11) NOT NULL DEFAULT '0' COMMENT '库存',
    `outer_id` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '商家编码',
    `bar_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '商品条形码',
    `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0',
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '商品规格';