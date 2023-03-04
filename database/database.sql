DROP TABLE IF EXISTS `type`;
CREATE TABLE `type` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `type_id` int NOT NULL DEFAULT '0' COMMENT '物品ID',
    `group_id` int NOT NULL DEFAULT '0' COMMENT '组ID',
    `graphic_id` int NOT NULL DEFAULT '0' COMMENT '图ID',
    `name_zh` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '中文名称',
    `name_en` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '英文名称',
    `volume` float unsigned NOT NULL DEFAULT '0.00' COMMENT '体积',
    `published` int NOT NULL DEFAULT '0' COMMENT '是否发布',
    `price_need` int NOT NULL DEFAULT '0' COMMENT '是否需要查询价格',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_type_id` (`type_id`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_name_zh` (`name_zh`),
    KEY `idx_name_en` (`name_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物品表';


DROP TABLE IF EXISTS `blueprint`;
CREATE TABLE `blueprint` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `type_id` int NOT NULL DEFAULT '0' COMMENT '蓝图的物品ID',
    `copying_time` int NOT NULL DEFAULT '0' COMMENT '复制时间',
    `manufacturing_time` int NOT NULL DEFAULT '0' COMMENT '生产时间',
    `product_type_id` int NOT NULL DEFAULT '0' COMMENT '产品id',
    `product_quantity` int NOT NULL DEFAULT '0' COMMENT '产品数量',
    `for_sale` int NOT NULL DEFAULT '0' COMMENT '是否有出售',
    `invention_type_id` int NOT NULL DEFAULT '0' COMMENT '可研发蓝图ID',
    `invention_probability` float unsigned NOT NULL DEFAULT '0.00' COMMENT '蓝图研发几率',
    `invention_quantity` int NOT NULL DEFAULT '0' COMMENT '蓝图研发数量',
    `average_price` float unsigned NOT NULL DEFAULT '0.00' COMMENT '平均价格',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_type_id` (`type_id`),
    KEY `idx_product_type_id` (`product_type_id`),
    KEY `idx_invention_type_id` (`invention_type_id`),
    KEY `idx_manufacturing_time` (`manufacturing_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='蓝图表';

DROP TABLE IF EXISTS `blueprint_material`;
CREATE TABLE `blueprint_material` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `blueprint_type_id` int NOT NULL DEFAULT '0' COMMENT '蓝图ID',
     `material_type_id` int NOT NULL DEFAULT '0' COMMENT '材料id',
     `material_quantity` int NOT NULL DEFAULT '0' COMMENT '材料数量',
     `activity_type` int NOT NULL DEFAULT '0' COMMENT '材料用途类型',
     `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
     `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
     PRIMARY KEY (`id`),
     UNIQUE KEY `uni_blueprint_material_type_id` (`blueprint_type_id`, `material_type_id`, `activity_type`),
     KEY `idx_blueprint_type_id` (`blueprint_type_id`),
     KEY `idx_material_type_id` (`material_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='蓝图材料表';

DROP TABLE IF EXISTS `type_price`;
CREATE TABLE `type_price` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `type_id` int NOT NULL DEFAULT '0' COMMENT '物品ID',
    `region_id` int NOT NULL DEFAULT '0' COMMENT '区域ID',
    `system_id` int NOT NULL DEFAULT '0' COMMENT '星系ID',
    `buy_max` float unsigned NOT NULL DEFAULT '0.00' COMMENT '购买最高价',
    `buy_min` float unsigned NOT NULL DEFAULT '0.00' COMMENT '购买最低价',
    `buy_quantity` double NOT NULL DEFAULT '0' COMMENT '购买总数',
    `sell_max` float unsigned NOT NULL DEFAULT '0.00' COMMENT '出售最高价',
    `sell_min` float unsigned NOT NULL DEFAULT '0.00' COMMENT '出售最低价',
    `sell_quantity` double NOT NULL DEFAULT '0' COMMENT '出售总数',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_type` (`type_id`, `region_id`, `system_id`),
    KEY `idx_region_id` (`region_id`),
    KEY `idx_system_id` (`system_id`),
    KEY `idx_buy_max` (`buy_max`),
    KEY `idx_buy_quantity` (`buy_quantity`),
    KEY `idx_sell_min` (`sell_min`),
    KEY `idx_sell_quantity` (`sell_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物品价格表';

DROP TABLE IF EXISTS `contract_item_cache`;
CREATE TABLE `contract_item_cache` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `type_id` int NOT NULL DEFAULT '0' COMMENT '物品ID',
    `price` float unsigned NOT NULL DEFAULT '0.00' COMMENT '出价',
    `is_blueprint_copy` int NOT NULL DEFAULT '0' COMMENT '是否拷贝',
    `time_efficiency` int NOT NULL DEFAULT '0' COMMENT '时间效率',
    `material_efficiency` int NOT NULL DEFAULT '0' COMMENT '材料效率',
    `runs` int NOT NULL DEFAULT '0' COMMENT '轮数',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uni_type_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合同条目缓存表';
