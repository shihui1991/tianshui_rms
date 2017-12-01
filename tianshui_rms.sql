-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017-12-01 02:24:06
-- 服务器版本: 5.5.54
-- PHP 版本: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `tianshui_rms`
--

-- --------------------------------------------------------

--
-- 表的结构 `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级ID',
  `level` int(11) DEFAULT NULL COMMENT '层级',
  `name` varchar(255) DEFAULT NULL COMMENT ' 接口名称',
  `url` text COMMENT '接口地址',
  `type` varchar(255) DEFAULT 'get' COMMENT '请求类型，get或post',
  `infos` text COMMENT ' 接口描述',
  `params` text COMMENT '参数',
  `response` text COMMENT '响应',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序',
  `status` tinyint(1) DEFAULT '1' COMMENT ' 状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='接口文档' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `api`
--

TRUNCATE TABLE `api`;
--
-- 转存表中的数据 `api`
--

INSERT INTO `api` (`id`, `parent_id`, `level`, `name`, `url`, `type`, `infos`, `params`, `response`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 1, '登录', '/api/index/index', 'get', '', '[{"name":"p1","value":"pv1","infos":""},{"name":"p2","value":"pv2","infos":""}]', '[{"name":"r1","value":"rv1","infos":""},{"name":"r2","value":"rv2","infos":""}]', 0, 1, 1509419246, 1509670351, NULL),
(2, 1, 2, '注册', '/api/index/signup', 'post', '', '[{"name":"p3","value":"pv3","infos":""}]', '[{"name":"r3","value":"rv3","infos":""}]', 0, 1, 1509420423, 1510199000, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `assess`
--

CREATE TABLE IF NOT EXISTS `assess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `estate` decimal(30,2) DEFAULT NULL COMMENT '房产评估总额',
  `assets` decimal(30,2) DEFAULT NULL COMMENT '资产评估总额',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_id` (`collection_id`,`item_id`,`community_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户评估' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `assess`
--

TRUNCATE TABLE `assess`;
--
-- 转存表中的数据 `assess`
--

INSERT INTO `assess` (`id`, `item_id`, `community_id`, `collection_id`, `estate`, `assets`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, NULL, NULL, 1512029360, 1512029470, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `assess_assets`
--

CREATE TABLE IF NOT EXISTS `assess_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT '入户评估ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `report_at` int(11) DEFAULT NULL COMMENT ' 报告时间',
  `valued_at` int(11) DEFAULT NULL COMMENT ' 价值时点',
  `method` text COMMENT ' 评估方法',
  `total` decimal(30,2) DEFAULT NULL COMMENT ' 评估总额',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `picture` text COMMENT '评估报告',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入户评估-资产评估' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `assess_assets`
--

TRUNCATE TABLE `assess_assets`;
-- --------------------------------------------------------

--
-- 表的结构 `assess_assets_valuer`
--

CREATE TABLE IF NOT EXISTS `assess_assets_valuer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT ' 入户评估ID',
  `estate_id` int(11) DEFAULT NULL COMMENT ' 房产评估ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `valuer_id` int(11) DEFAULT NULL COMMENT ' 评估师ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入户评估-资产评估-评估师' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `assess_assets_valuer`
--

TRUNCATE TABLE `assess_assets_valuer`;
-- --------------------------------------------------------

--
-- 表的结构 `assess_estate`
--

CREATE TABLE IF NOT EXISTS `assess_estate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT '入户评估ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `report_at` int(11) DEFAULT NULL COMMENT ' 报告时间',
  `valued_at` int(11) DEFAULT NULL COMMENT ' 价值时点',
  `method` text COMMENT ' 评估方法',
  `total` decimal(30,2) DEFAULT NULL COMMENT ' 评估总额',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `picture` text COMMENT '评估报告',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入户评估-房产评估' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `assess_estate`
--

TRUNCATE TABLE `assess_estate`;
-- --------------------------------------------------------

--
-- 表的结构 `assess_estate_building`
--

CREATE TABLE IF NOT EXISTS `assess_estate_building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT ' 入户评估ID',
  `estate_id` int(11) DEFAULT NULL COMMENT '房产评估ID',
  `building_id` int(11) DEFAULT NULL COMMENT '建筑ID',
  `price` decimal(30,2) DEFAULT NULL COMMENT ' 评估单价',
  `amount` decimal(30,2) DEFAULT NULL COMMENT ' 评估总价',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入户评估-房产评估-建筑评估' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `assess_estate_building`
--

TRUNCATE TABLE `assess_estate_building`;
-- --------------------------------------------------------

--
-- 表的结构 `assess_estate_valuer`
--

CREATE TABLE IF NOT EXISTS `assess_estate_valuer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT ' 入户评估ID',
  `estate_id` int(11) DEFAULT NULL COMMENT ' 房产评估ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `valuer_id` int(11) DEFAULT NULL COMMENT ' 评估师ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入户评估-房产评估-评估师' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `assess_estate_valuer`
--

TRUNCATE TABLE `assess_estate_valuer`;
-- --------------------------------------------------------

--
-- 表的结构 `bank`
--

CREATE TABLE IF NOT EXISTS `bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='常用银行列表' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `bank`
--

TRUNCATE TABLE `bank`;
--
-- 转存表中的数据 `bank`
--

INSERT INTO `bank` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '中国工商银行', '', 1, 1509186075, 1509670570, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `building_status`
--

CREATE TABLE IF NOT EXISTS `building_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='建筑状态' AUTO_INCREMENT=6 ;

--
-- 插入之前先把表清空（truncate） `building_status`
--

TRUNCATE TABLE `building_status`;
--
-- 转存表中的数据 `building_status`
--

INSERT INTO `building_status` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '正常', '房屋权属证明记载面积、结构与现状房屋相符，并在院内无新建、改建、扩建房屋', 1, 1509173390, 1509174217, NULL),
(2, '新建', '合法新建', 1, 1509173415, 1509174278, NULL),
(3, '改建', '合法改建', 1, 1509173739, 1509174285, NULL),
(4, '扩建', '合法扩建', 1, 1509173746, 1509174295, NULL),
(5, '违建', '违章建筑', 1, 1509174133, 1509174654, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `building_struct`
--

CREATE TABLE IF NOT EXISTS `building_struct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='建筑结构' AUTO_INCREMENT=4 ;

--
-- 插入之前先把表清空（truncate） `building_struct`
--

TRUNCATE TABLE `building_struct`;
--
-- 转存表中的数据 `building_struct`
--

INSERT INTO `building_struct` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '砖混', '', 1, 1509170861, 1509171157, NULL),
(2, '钢混', '', 1, 1509172299, 1509172299, NULL),
(3, '砖木', '', 1, 1509172444, 1509172444, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `building_use`
--

CREATE TABLE IF NOT EXISTS `building_use` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='建筑使用性质' AUTO_INCREMENT=7 ;

--
-- 插入之前先把表清空（truncate） `building_use`
--

TRUNCATE TABLE `building_use`;
--
-- 转存表中的数据 `building_use`
--

INSERT INTO `building_use` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '住宅', '', 1, 1509172186, 1509670887, NULL),
(2, '附属物', '', 1, 1509172208, 1509172208, NULL),
(3, '公共附属物', '', 1, 1509172224, 1509172224, NULL),
(4, '办公', '', 1, 1509172253, 1509172253, NULL),
(5, '商服', '', 1, 1509172263, 1509172263, NULL),
(6, '生产加工', '', 1, 1509172276, 1509172276, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection`
--

CREATE TABLE IF NOT EXISTS `collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 征地片区ID',
  `building` int(11) DEFAULT NULL COMMENT '几栋',
  `unit` int(11) DEFAULT NULL COMMENT '几单元',
  `floor` int(11) DEFAULT NULL COMMENT ' 几楼',
  `number` int(11) DEFAULT NULL COMMENT ' 几号',
  `type` tinyint(1) DEFAULT '0' COMMENT '类型，0私产，1公产',
  `land_prop` tinyint(1) DEFAULT '0' COMMENT '土地性质，0为国有，1为集体',
  `land_source` tinyint(1) DEFAULT '0' COMMENT '土地来源，0为出让，1为划拨',
  `land_status` tinyint(1) DEFAULT '0' COMMENT '土地状况，0为转让，1为租赁，2为抵押，3为查封',
  `default_use` int(11) DEFAULT NULL COMMENT '批准使用性质 ID',
  `real_use` int(11) DEFAULT NULL COMMENT ' 实际使用性质ID',
  `has_assets` tinyint(1) DEFAULT '0' COMMENT '是否需要资产评估，0为否，1为是',
  `is_agree` tinyint(1) DEFAULT '1' COMMENT '拆迁意见，0反对，1同意',
  `compensate_way` tinyint(1) DEFAULT '0' COMMENT '期望补偿方法，0为货币补偿，1为产权调换',
  `compensate_price` int(11) DEFAULT NULL COMMENT '期望补偿单价',
  `rebuild_addr` text COMMENT ' 期望还建地址',
  `rebuild_layout_id` int(11) DEFAULT NULL COMMENT '还建户型 ID',
  `rebuild_area` int(11) DEFAULT NULL COMMENT ' 还建面积',
  `rebuild_price` int(11) DEFAULT NULL COMMENT '还建增加面积的单价',
  `opinion` text COMMENT ' 其他意见',
  `receive_addr` text COMMENT ' 收件人地址',
  `receive_man` varchar(255) DEFAULT NULL COMMENT '收件人',
  `receive_phone` char(20) DEFAULT NULL COMMENT ' 联系电话',
  `picture` text COMMENT '土地证件等',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`,`item_id`,`community_id`,`building`,`unit`,`floor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `collection`
--

TRUNCATE TABLE `collection`;
--
-- 转存表中的数据 `collection`
--

INSERT INTO `collection` (`id`, `item_id`, `community_id`, `building`, `unit`, `floor`, `number`, `type`, `land_prop`, `land_source`, `land_status`, `default_use`, `real_use`, `has_assets`, `is_agree`, `compensate_way`, `compensate_price`, `rebuild_addr`, `rebuild_layout_id`, `rebuild_area`, `rebuild_price`, `opinion`, `receive_addr`, `receive_man`, `receive_phone`, `picture`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 0, 1, 0, 0, '渝北区杨柳北路9号', 0, 0, 0, '', '', '', '', '[]', 1, 1509932860, 1511601196, NULL),
(2, 2, 1, 1, 1, 2, 3, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, '', 0, 0, 0, '', '', '', '', '[]', 1, 1510196915, 1510883562, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_building`
--

CREATE TABLE IF NOT EXISTS `collection_building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT '0' COMMENT '入户摸底ID',
  `building` int(11) DEFAULT NULL COMMENT '几栋',
  `unit` int(11) DEFAULT NULL COMMENT ' 几单元',
  `floor` int(11) DEFAULT NULL COMMENT ' 几楼',
  `number` int(11) DEFAULT NULL COMMENT ' 几号',
  `total_floor` int(11) DEFAULT NULL COMMENT ' 总楼层',
  `direction` varchar(255) DEFAULT NULL COMMENT ' 朝向',
  `register` varchar(255) DEFAULT NULL COMMENT '登记号（房产证号）',
  `register_num` float DEFAULT NULL COMMENT '登记数量(登记面积）',
  `real_num` float DEFAULT NULL COMMENT ' 实际数量(测绘面积）',
  `real_unit` varchar(255) DEFAULT NULL COMMENT '数量单位',
  `default_use` int(11) DEFAULT NULL COMMENT ' 批准用途ID',
  `use_id` int(11) DEFAULT NULL COMMENT ' 实际用途ID',
  `struct_id` int(11) DEFAULT NULL COMMENT ' 结构ID',
  `status_id` int(11) DEFAULT '0' COMMENT ' 建筑状况ID，0待定',
  `picture` text COMMENT '图片',
  `status` tinyint(1) DEFAULT NULL COMMENT ' 使用状态，0自用，1租赁',
  `business` varchar(255) DEFAULT NULL COMMENT '经营项目',
  `build_year` int(11) DEFAULT NULL COMMENT ' 建造年份',
  `remark` text COMMENT '备注',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底-建筑' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `collection_building`
--

TRUNCATE TABLE `collection_building`;
--
-- 转存表中的数据 `collection_building`
--

INSERT INTO `collection_building` (`id`, `item_id`, `community_id`, `collection_id`, `building`, `unit`, `floor`, `number`, `total_floor`, `direction`, `register`, `register_num`, `real_num`, `real_unit`, `default_use`, `use_id`, `struct_id`, `status_id`, `picture`, `status`, `business`, `build_year`, `remark`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 1, 0, 0, 0, 0, 0, '', '', 0, 120, '㎡', 1, 3, 1, 1, '[]', 1, '', 0, '', 1510019791, 1511604085, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_community`
--

CREATE TABLE IF NOT EXISTS `collection_community` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` text,
  `name` varchar(255) DEFAULT NULL,
  `infos` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底-片区' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `collection_community`
--

TRUNCATE TABLE `collection_community`;
--
-- 转存表中的数据 `collection_community`
--

INSERT INTO `collection_community` (`id`, `address`, `name`, `infos`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '渝北区杨柳北路9号', '力华科谷', '', 1509617280, 1511429923, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_holder`
--

CREATE TABLE IF NOT EXISTS `collection_holder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 摸底ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `address` text COMMENT ' 地址',
  `phone` varchar(255) DEFAULT NULL COMMENT ' 电话',
  `holder` tinyint(1) DEFAULT '0' COMMENT '产权，0无，1产权人，2承租人',
  `portion` decimal(10,2) DEFAULT '0.00' COMMENT '补偿份额',
  `cardnum` varchar(255) DEFAULT NULL COMMENT '证件号（身份证）',
  `relation` varchar(255) DEFAULT NULL COMMENT ' 与户主关系',
  `gender` tinyint(1) DEFAULT '0' COMMENT ' 性别，0无，1男，2女',
  `birth` int(11) DEFAULT NULL COMMENT '生日',
  `nation` varchar(255) DEFAULT NULL COMMENT '民族',
  `job` varchar(255) DEFAULT NULL COMMENT '职业',
  `married` tinyint(1) DEFAULT '0' COMMENT '婚姻，0无，1未婚，2已婚',
  `live_addr` text COMMENT '现住址',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底-产权人及承租家庭' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `collection_holder`
--

TRUNCATE TABLE `collection_holder`;
--
-- 转存表中的数据 `collection_holder`
--

INSERT INTO `collection_holder` (`id`, `item_id`, `community_id`, `collection_id`, `name`, `address`, `phone`, `holder`, `portion`, `cardnum`, `relation`, `gender`, `birth`, `nation`, `job`, `married`, `live_addr`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, '重庆', '重庆市渝北区', '0123', 1, '100.00', '', '', 0, 0, '', '', 0, '', 1510111063, 1511748234, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_holder_crowd`
--

CREATE TABLE IF NOT EXISTS `collection_holder_crowd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `holder_id` int(11) DEFAULT NULL COMMENT ' 成员ID',
  `crowd_id` int(11) DEFAULT NULL COMMENT ' 特殊人群分类ID',
  `crowd_parent_id` int(11) DEFAULT NULL COMMENT ' 特殊人群分类分组ID',
  `picture` text COMMENT ' 相关证件',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底-家庭成员-特殊人群' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `collection_holder_crowd`
--

TRUNCATE TABLE `collection_holder_crowd`;
--
-- 转存表中的数据 `collection_holder_crowd`
--

INSERT INTO `collection_holder_crowd` (`id`, `item_id`, `community_id`, `collection_id`, `holder_id`, `crowd_id`, `crowd_parent_id`, `picture`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, 1, 9, 6, '["\\/uploads\\/image\\/20171108\\/e11890aff033dae658834003a3cd3268.jpg"]', 1510134644, 1511755243, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_holder_house`
--

CREATE TABLE IF NOT EXISTS `collection_holder_house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `collection_holder_id` int(11) DEFAULT NULL COMMENT ' 入户摸底-产权人或承租人ID',
  `house_id` int(11) DEFAULT NULL COMMENT ' 房源ID',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产权人或承租人选择安置房' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `collection_holder_house`
--

TRUNCATE TABLE `collection_holder_house`;
--
-- 转存表中的数据 `collection_holder_house`
--

INSERT INTO `collection_holder_house` (`id`, `item_id`, `community_id`, `collection_id`, `collection_holder_id`, `house_id`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, 1, 1, 1, 1511165768, 1511762953, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `collection_object`
--

CREATE TABLE IF NOT EXISTS `collection_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `object_id` int(11) DEFAULT NULL COMMENT ' 补偿事项ID',
  `number` int(11) DEFAULT NULL COMMENT ' 数量',
  `picture` text COMMENT ' 相关图片',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_id` (`object_id`,`item_id`,`collection_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='入户摸底-其他补偿事项' AUTO_INCREMENT=5 ;

--
-- 插入之前先把表清空（truncate） `collection_object`
--

TRUNCATE TABLE `collection_object`;
--
-- 转存表中的数据 `collection_object`
--

INSERT INTO `collection_object` (`id`, `item_id`, `collection_id`, `object_id`, `number`, `picture`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 1, 1, '[]', 1510304481, 1511763821, NULL),
(2, 2, 2, 2, 1, '[]', 1510796083, 1511763657, NULL),
(3, 2, 2, 3, 1, '[]', 1510796089, 1510796089, NULL),
(4, 2, 1, 4, 1, '[]', 1510796099, 1510796099, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT '0' COMMENT '类型，0为房产评估公司，1为资产评估公司',
  `name` text COMMENT '名称',
  `short_name` varchar(255) DEFAULT NULL COMMENT '简称',
  `logo` text COMMENT '公司LOGO',
  `address` text COMMENT ' 公司地址',
  `contact_man` varchar(255) DEFAULT NULL COMMENT '联系人',
  `contact_phone` char(20) DEFAULT NULL COMMENT ' 联系电话',
  `phone` char(20) DEFAULT NULL COMMENT ' 公司电话',
  `fax` char(20) DEFAULT NULL COMMENT '传真',
  `infos` text COMMENT '简介',
  `content` text COMMENT '详细介绍',
  `picture` text COMMENT '图片，json字符串',
  `username` varchar(255) DEFAULT NULL COMMENT '登录名',
  `password` varchar(255) DEFAULT NULL COMMENT '登录密码',
  `secret_key` varchar(255) DEFAULT NULL COMMENT '密钥',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0为禁用，1为启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_name` (`short_name`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='评估公司' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `company`
--

TRUNCATE TABLE `company`;
--
-- 转存表中的数据 `company`
--

INSERT INTO `company` (`id`, `type`, `name`, `short_name`, `logo`, `address`, `contact_man`, `contact_phone`, `phone`, `fax`, `infos`, `content`, `picture`, `username`, `password`, `secret_key`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, '重庆市步联科技有限公司', '步联科技', '', '重庆市渝北区杨柳北路9号力华科谷A区203', '步联科技', '步联科技', '02363624610', '02363624610', '步联科技', '<br />', '[]', 'buliankeji', '123456', '9600173F-AB5C-3DDD-0EEA-C204D1887295', 0, 1, 1509435208, 1509672112, NULL),
(2, 0, '重庆市贤盾科技有限公司', '贤盾科技', '/uploads/20171031/fca9277be78db5eabf5cc6e244a38230.png', '重庆市贤盾科技有限公司', '贤盾科技', '贤盾科技', '贤盾科技', '贤盾科技', '贤盾科技', '', '[]', 'xiandunkeji', '123456', '24D564C3-7865-774D-9B37-E67CE60E89BD', 0, 1, 1509435817, 1510902738, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `company_valuer`
--

CREATE TABLE IF NOT EXISTS `company_valuer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL COMMENT '评估公司ID',
  `name` varchar(255) DEFAULT NULL COMMENT '姓名',
  `phone` varchar(255) DEFAULT NULL COMMENT '联系电话',
  `register_num` varchar(255) DEFAULT NULL COMMENT ' 注册号',
  `valid_at` int(11) DEFAULT NULL COMMENT ' 有效时间',
  `infos` text COMMENT '说明 ',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `register_num` (`register_num`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='评估师' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `company_valuer`
--

TRUNCATE TABLE `company_valuer`;
--
-- 转存表中的数据 `company_valuer`
--

INSERT INTO `company_valuer` (`id`, `company_id`, `name`, `phone`, `register_num`, `valid_at`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '步联科技', '12345678910', '123456789', 1509379200, '', 1, 1509442882, 1509501558, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `crowd`
--

CREATE TABLE IF NOT EXISTS `crowd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级分类ID',
  `level` int(11) DEFAULT NULL COMMENT '层级',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='特殊人群分类' AUTO_INCREMENT=12 ;

--
-- 插入之前先把表清空（truncate） `crowd`
--

TRUNCATE TABLE `crowd`;
--
-- 转存表中的数据 `crowd`
--

INSERT INTO `crowd` (`id`, `parent_id`, `level`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 1, '残疾', '', 1, 1509180587, 1509181114, NULL),
(2, 1, 2, '一级残疾', '', 1, 1509180628, 1509181114, NULL),
(3, 1, 2, '二级残疾', '', 1, 1509180663, 1509181114, NULL),
(4, 1, 2, '三级残疾', '', 1, 1509180673, 1509181114, NULL),
(5, 1, 2, '四级残疾', '', 1, 1509180687, 1509181114, NULL),
(6, 0, 1, '优抚对象', '', 1, 1509180730, 1509181114, NULL),
(7, 6, 2, '城市居民最低生活保障', '', 1, 1509180810, 1509181114, NULL),
(8, 6, 2, '伤残军人及优抚对象', '', 1, 1509180870, 1509181114, NULL),
(9, 6, 2, '建档困难职工家庭及特困职工家庭', '', 1, 1509180930, 1509181114, NULL),
(10, 0, 1, '失独', '', 1, 1509180969, 1509181114, NULL),
(11, 10, 2, '失独家庭', '', 1, 1509181074, 1509181114, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `dept`
--

CREATE TABLE IF NOT EXISTS `dept` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级 ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `user_id` int(11) DEFAULT NULL COMMENT '主管用户ID',
  `level` int(11) DEFAULT NULL COMMENT ' 层级',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组织与部门' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `dept`
--

TRUNCATE TABLE `dept`;
--
-- 转存表中的数据 `dept`
--

INSERT INTO `dept` (`id`, `parent_id`, `name`, `user_id`, `level`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, '管理层', 0, 1, '', 1, 1509152121, 1509171143, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `funds_in`
--

CREATE TABLE IF NOT EXISTS `funds_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `pay_holder_id` int(11) DEFAULT NULL COMMENT ' 兑付-产权人或承租人ID',
  `name_id` int(11) DEFAULT NULL COMMENT '资金款项ID',
  `voucher` varchar(255) DEFAULT NULL COMMENT ' 凭证号',
  `entry_at` int(11) DEFAULT NULL COMMENT ' 缴纳时间',
  `payer` varchar(255) DEFAULT NULL COMMENT ' 缴纳人',
  `amount` decimal(30,2) DEFAULT NULL COMMENT ' 金额',
  `bank` varchar(255) DEFAULT NULL COMMENT ' 支付银行',
  `account` varchar(255) DEFAULT NULL COMMENT ' 支付账号',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资金收入' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `funds_in`
--

TRUNCATE TABLE `funds_in`;
-- --------------------------------------------------------

--
-- 表的结构 `funds_name`
--

CREATE TABLE IF NOT EXISTS `funds_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='资金款项' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `funds_name`
--

TRUNCATE TABLE `funds_name`;
--
-- 转存表中的数据 `funds_name`
--

INSERT INTO `funds_name` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '项目预备金', '', 1, 1509186075, 1509613310, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `funds_out`
--

CREATE TABLE IF NOT EXISTS `funds_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `pay_holder_id` int(11) DEFAULT NULL COMMENT ' 兑付-产权人或承租人ID',
  `name_id` int(11) DEFAULT NULL COMMENT '资金款项ID',
  `voucher` varchar(255) DEFAULT NULL COMMENT ' 凭证号',
  `outlay_at` int(11) DEFAULT NULL COMMENT '支付时间',
  `payee` varchar(255) DEFAULT NULL COMMENT '接收人',
  `amount` decimal(30,2) DEFAULT NULL COMMENT ' 金额',
  `bank` varchar(255) DEFAULT NULL COMMENT '接收银行',
  `account` varchar(255) DEFAULT NULL COMMENT '接收账号',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资金支出' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `funds_out`
--

TRUNCATE TABLE `funds_out`;
-- --------------------------------------------------------

--
-- 表的结构 `house`
--

CREATE TABLE IF NOT EXISTS `house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `community_id` int(11) DEFAULT NULL COMMENT '区域 ID',
  `building` int(11) DEFAULT NULL COMMENT ' 几栋',
  `unit` int(11) DEFAULT NULL COMMENT ' 几单元',
  `floor` int(11) DEFAULT NULL COMMENT ' 几楼',
  `number` int(11) DEFAULT NULL COMMENT ' 几号',
  `layout_id` int(11) DEFAULT NULL COMMENT ' 户型 ID',
  `layout_pic_id` int(11) DEFAULT NULL COMMENT '户型图 ID',
  `area` decimal(30,2) DEFAULT NULL COMMENT '面积 （平米）',
  `total_floor` int(11) DEFAULT NULL COMMENT '总楼层',
  `has_lift` tinyint(1) DEFAULT '0' COMMENT '是否配电梯，0否，1是',
  `manage_price` decimal(10,2) DEFAULT NULL COMMENT '物业管理费单价 (元/平米/月）',
  `public_price` decimal(10,2) DEFAULT NULL COMMENT '公摊费单价 （元/月）',
  `is_real` tinyint(1) DEFAULT '0' COMMENT '0期房，1现房',
  `is_buy` tinyint(1) DEFAULT '0' COMMENT '0非购置房，1购置房',
  `is_transit` tinyint(1) DEFAULT '0' COMMENT '0不可用作临时安置房，1可作临时安置房',
  `is_public` tinyint(1) DEFAULT '0' COMMENT '0项目专用房，1项目共用房',
  `picture` text COMMENT '房源图片',
  `deliver_at` int(11) DEFAULT NULL COMMENT '交付时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0闲置，1临时安置，2安置，3失效',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `community_id` (`community_id`,`building`,`unit`,`floor`,`number`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='安置房源' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `house`
--

TRUNCATE TABLE `house`;
--
-- 转存表中的数据 `house`
--

INSERT INTO `house` (`id`, `community_id`, `building`, `unit`, `floor`, `number`, `layout_id`, `layout_pic_id`, `area`, `total_floor`, `has_lift`, `manage_price`, `public_price`, `is_real`, `is_buy`, `is_transit`, `is_public`, `picture`, `deliver_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 0, 0, 0, 0, 1, 2, '0.00', 0, 0, '0.00', '0.00', 0, 0, 0, 0, '[]', 1508860800, 0, 1509413843, 1510897236, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `house_community`
--

CREATE TABLE IF NOT EXISTS `house_community` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` text COMMENT '地址',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='安置房源小区' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `house_community`
--

TRUNCATE TABLE `house_community`;
--
-- 转存表中的数据 `house_community`
--

INSERT INTO `house_community` (`id`, `address`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '渝北区杨柳北路9号', '力华科谷', '', 1, 1509333835, 1509333849, NULL),
(2, '渝北区杨柳北路10号', '联通大厦', '', 1, 1509333921, 1509333921, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `house_layout_pic`
--

CREATE TABLE IF NOT EXISTS `house_layout_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `community_id` int(11) DEFAULT NULL COMMENT '房源小区 ID',
  `layout_id` int(11) DEFAULT NULL COMMENT '户型 ID',
  `remark` varchar(255) DEFAULT NULL COMMENT '户型标记',
  `picture` text COMMENT '户型图',
  `infos` text COMMENT '描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remark` (`remark`,`community_id`,`layout_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='安置房源户型图' AUTO_INCREMENT=5 ;

--
-- 插入之前先把表清空（truncate） `house_layout_pic`
--

TRUNCATE TABLE `house_layout_pic`;
--
-- 转存表中的数据 `house_layout_pic`
--

INSERT INTO `house_layout_pic` (`id`, `community_id`, `layout_id`, `remark`, `picture`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'A', '/uploads/image/20171103/45840845ae0baad45f51372b2395d2c3.jpg', '', 1, 1509345081, 1509679524, NULL),
(2, 1, 1, 'B', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', 1, 1509345103, 1509374240, NULL),
(3, 2, 1, 'A', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', 1, 1509345280, 1509345364, NULL),
(4, 2, 2, 'C', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', 1, 1509367040, 1509526375, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `house_price`
--

CREATE TABLE IF NOT EXISTS `house_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `house_id` int(11) DEFAULT NULL COMMENT '房源ID',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT ' 市场评估价格',
  `price` decimal(10,2) DEFAULT NULL COMMENT ' 安置优惠价格',
  `start_at` int(11) DEFAULT NULL COMMENT ' 生效时间',
  `end_at` int(11) DEFAULT NULL COMMENT ' 有效期限',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `start_at` (`start_at`,`house_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='房源价格' AUTO_INCREMENT=4 ;

--
-- 插入之前先把表清空（truncate） `house_price`
--

TRUNCATE TABLE `house_price`;
--
-- 转存表中的数据 `house_price`
--

INSERT INTO `house_price` (`id`, `house_id`, `market_price`, `price`, `start_at`, `end_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2000.00', '1500.00', 1451577600, 1467216000, 1510897084, 1510898290, NULL),
(2, 1, '3000.00', '2000.00', 1467302400, 1483113600, 1510897750, 1510898622, NULL),
(3, 1, '4000.00', '3000.00', 1483200000, 1514649600, 1512010787, 1512010787, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `house_resettle`
--

CREATE TABLE IF NOT EXISTS `house_resettle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `collection_community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `collection_holder_id` int(11) DEFAULT NULL COMMENT '产权人或承租人ID',
  `house_id` int(11) DEFAULT NULL COMMENT ' 安置房源ID',
  `house_community_id` int(11) DEFAULT NULL COMMENT ' 房源小区ID',
  `start_at` int(11) DEFAULT NULL COMMENT ' 开始时间',
  `end_at` int(11) DEFAULT NULL COMMENT ' 结束时间',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='房源安置' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `house_resettle`
--

TRUNCATE TABLE `house_resettle`;
-- --------------------------------------------------------

--
-- 表的结构 `house_transit`
--

CREATE TABLE IF NOT EXISTS `house_transit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `collection_community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `house_id` int(11) DEFAULT NULL COMMENT ' 安置房源ID',
  `house_community_id` int(11) DEFAULT NULL COMMENT ' 房源小区ID',
  `start_at` int(11) DEFAULT NULL COMMENT ' 开始时间',
  `exp_end` int(11) DEFAULT NULL COMMENT ' 预计结束时间',
  `end_at` int(11) DEFAULT NULL COMMENT ' 结束时间',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='房源临时安置' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `house_transit`
--

TRUNCATE TABLE `house_transit`;
-- --------------------------------------------------------

--
-- 表的结构 `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `record_num` varchar(255) DEFAULT NULL COMMENT '档案编号',
  `area` text COMMENT ' 征收范围',
  `household` int(11) DEFAULT NULL COMMENT ' 预计户数',
  `funds` decimal(30,2) DEFAULT NULL COMMENT ' 预算资金',
  `house` int(11) DEFAULT NULL COMMENT ' 预计安置房数',
  `picture` text COMMENT '图片',
  `infos` text COMMENT '说明',
  `is_top` tinyint(1) DEFAULT '0' COMMENT '置顶，0否，1是',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0待定，1进行中，2完成，3取消',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `record_num` (`record_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目基本信息' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `item`
--

TRUNCATE TABLE `item`;
--
-- 转存表中的数据 `item`
--

INSERT INTO `item` (`id`, `name`, `record_num`, `area`, `household`, `funds`, `house`, `picture`, `infos`, `is_top`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '西关片区棚户区改造', '0123456', '东至，西至，', 200, '200000.00', 300, NULL, '', 0, 3, 1509531788, 1511573608, NULL),
(2, '永庆路北侧片区土地熟化', '32141654', '东至，西至，北至，南至', 100, '2000000.00', 200, '[]', '', 1, 1, 1509606705, 1511887648, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_company`
--

CREATE TABLE IF NOT EXISTS `item_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `infos` text COMMENT ' 评估说明',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`,`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目评估公司' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `item_company`
--

TRUNCATE TABLE `item_company`;
--
-- 转存表中的数据 `item_company`
--

INSERT INTO `item_company` (`id`, `item_id`, `company_id`, `infos`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, '', 1510198009, 1510198009, NULL),
(2, 2, 2, '', 1510198020, 1510198020, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_company_collection`
--

CREATE TABLE IF NOT EXISTS `item_company_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_company_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_id` (`collection_id`,`item_company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目评估公司-被征户' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `item_company_collection`
--

TRUNCATE TABLE `item_company_collection`;
--
-- 转存表中的数据 `item_company_collection`
--

INSERT INTO `item_company_collection` (`id`, `item_company_id`, `collection_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1510198009, 1510198009),
(2, 2, 2, 1510198020, 1510198020);

-- --------------------------------------------------------

--
-- 表的结构 `item_company_vote`
--

CREATE TABLE IF NOT EXISTS `item_company_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `collection_holder_id` int(11) DEFAULT NULL COMMENT ' 入户摸底-产权人或承租人ID',
  `company_id` int(11) DEFAULT NULL COMMENT ' 评估公司ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`,`item_id`,`community_id`,`collection_id`,`collection_holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评估公司选票' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `item_company_vote`
--

TRUNCATE TABLE `item_company_vote`;
-- --------------------------------------------------------

--
-- 表的结构 `item_house`
--

CREATE TABLE IF NOT EXISTS `item_house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `house_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `house_id` (`house_id`,`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='冻结安置房' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `item_house`
--

TRUNCATE TABLE `item_house`;
--
-- 转存表中的数据 `item_house`
--

INSERT INTO `item_house` (`id`, `item_id`, `house_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1509610556, 1509610591);

-- --------------------------------------------------------

--
-- 表的结构 `item_house_up`
--

CREATE TABLE IF NOT EXISTS `item_house_up` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '兑付-房屋ID',
  `up_start` decimal(10,2) DEFAULT NULL COMMENT '上浮面积区间-起',
  `up_end` decimal(10,2) DEFAULT NULL COMMENT '上浮面积区间-止',
  `up_rate` decimal(10,2) DEFAULT NULL COMMENT '上浮比例（%）',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目安置房上浮比例' AUTO_INCREMENT=6 ;

--
-- 插入之前先把表清空（truncate） `item_house_up`
--

TRUNCATE TABLE `item_house_up`;
--
-- 转存表中的数据 `item_house_up`
--

INSERT INTO `item_house_up` (`id`, `item_id`, `up_start`, `up_end`, `up_rate`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, '0.00', '15.00', '10.00', 1511340140, 1511340474, NULL),
(2, 2, '15.00', '25.00', '20.00', 1511340169, 1511340169, NULL),
(3, 2, '25.00', '30.00', '30.00', 1511340180, 1511340180, NULL),
(4, 2, '30.00', '0.00', '0.00', 1511340184, 1511340184, NULL),
(5, 1, '0.00', '15.00', '10.00', 1511340403, 1511340403, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_process`
--

CREATE TABLE IF NOT EXISTS `item_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `process_id` int(11) DEFAULT NULL COMMENT ' 流程ID',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0未进行，1进行中，2完成',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `process_id` (`process_id`,`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目流程' AUTO_INCREMENT=4 ;

--
-- 插入之前先把表清空（truncate） `item_process`
--

TRUNCATE TABLE `item_process`;
--
-- 转存表中的数据 `item_process`
--

INSERT INTO `item_process` (`id`, `item_id`, `process_id`, `sort`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 1, 1, 1511417066, 1511504868, NULL),
(2, 2, 2, 2, 0, 1511417084, 1511419456, NULL),
(3, 2, 3, 3, 0, 1511417121, 1511835278, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_status`
--

CREATE TABLE IF NOT EXISTS `item_status` (
  `keyname` varchar(255) DEFAULT NULL COMMENT '关键词名',
  `keyvalue` varchar(255) DEFAULT NULL COMMENT '关键词值',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `role_id` int(11) DEFAULT NULL COMMENT ' 角色ID',
  `role_parent_id` int(11) DEFAULT NULL COMMENT ' 上级角色ID',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态，0添加，1修改，2删除，3恢复，4销毁，8通过，9驳回',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目动态';

--
-- 插入之前先把表清空（truncate） `item_status`
--

TRUNCATE TABLE `item_status`;
--
-- 转存表中的数据 `item_status`
--

INSERT INTO `item_status` (`keyname`, `keyvalue`, `user_id`, `role_id`, `role_parent_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
('item_id', '2', 1, 1, NULL, 1, 1511515809, 1511515809, NULL),
('item_id', '2', 1, 1, NULL, 1, 1511515819, 1511515819, NULL),
('item_id', '2', 1, 1, NULL, 1, 1511515834, 1511515834, NULL),
('item_id', '2', 1, 1, NULL, 8, 1511515884, 1511515884, NULL),
('item_id', '2', 1, 1, NULL, 9, 1511515887, 1511515887, NULL),
('item_id', '2', 1, 1, NULL, 9, 1511515891, 1511515891, NULL),
('item_id', '2', 1, 1, NULL, 8, 1511515892, 1511515892, NULL),
('item_id', '2', 1, 1, NULL, 8, 1511515932, 1511515932, NULL),
('item_id', '2', 1, 1, NULL, 1, 1511516125, 1511516125, NULL),
('item_id', '2', 1, 1, NULL, 1, 1511516132, 1511516132, NULL),
('item_id', '1', 1, 1, NULL, 1, 1511573608, 1511573608, NULL),
('collection_id', '1', 1, 1, NULL, 3, 1511579101, 1511579101, NULL),
('collection_id', '1', 1, 1, NULL, 2, 1511580300, 1511580300, NULL),
('collection_id', '1', 1, 1, NULL, 3, 1511580313, 1511580313, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511580558, 1511580558, NULL),
('collection_id', '1', 1, 1, NULL, 8, 1511583988, 1511583988, NULL),
('collection_id', '1', 1, 1, NULL, 9, 1511584000, 1511584000, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511584003, 1511584003, NULL),
('collection_id', '1', 1, 1, NULL, 9, 1511584287, 1511584287, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511584292, 1511584292, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511584295, 1511584295, NULL),
('collection_id', '1', 1, 1, NULL, 8, 1511596469, 1511596469, NULL),
('collection_id', '1', 1, 1, NULL, 9, 1511596480, 1511596480, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511601182, 1511601182, NULL),
('collection_id', '1', 1, 1, NULL, 1, 1511601196, 1511601196, NULL),
('collection_id', '1', 1, 1, NULL, 8, 1511601751, 1511601751, NULL),
('collection_id', '1', 1, 1, NULL, 9, 1511601768, 1511601768, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_subject`
--

CREATE TABLE IF NOT EXISTS `item_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `subject_id` int(11) DEFAULT NULL COMMENT '科目ID',
  `default` int(11) DEFAULT '1' COMMENT '默认补偿次数',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_id` (`subject_id`,`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目可变补偿科目' AUTO_INCREMENT=10 ;

--
-- 插入之前先把表清空（truncate） `item_subject`
--

TRUNCATE TABLE `item_subject`;
--
-- 转存表中的数据 `item_subject`
--

INSERT INTO `item_subject` (`id`, `item_id`, `subject_id`, `default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 1, 1510362931, 1510362931, NULL),
(2, 2, 2, 1, 1510362975, 1510362975, NULL),
(3, 1, 1, 1, 1510363014, 1510363311, NULL),
(4, 2, 3, 1, 1510363342, 1510363342, NULL),
(5, 2, 4, 1, 1510363354, 1510363354, NULL),
(6, 2, 5, 1, 1510363372, 1510363372, NULL),
(7, 2, 6, 1, 1510363375, 1510363375, NULL),
(8, 2, 7, 1, 1510363380, 1510363380, NULL),
(9, 2, 8, 1, 1510363385, 1510363385, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `item_time`
--

CREATE TABLE IF NOT EXISTS `item_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `collection_start` int(11) DEFAULT NULL COMMENT ' 入户摸底时间起',
  `collection_end` int(11) DEFAULT NULL COMMENT ' 入户摸底时间止',
  `vote_start` int(11) DEFAULT NULL COMMENT '投票评估公司时间起',
  `vote_end` int(11) DEFAULT NULL COMMENT '投票评估公司时间止',
  `assess_start` int(11) DEFAULT NULL COMMENT '入户评估时间起',
  `assess_end` int(11) DEFAULT NULL COMMENT '入户评估时间止',
  `risk_start` int(11) DEFAULT NULL COMMENT '风险评估时间起',
  `risk_end` int(11) DEFAULT NULL COMMENT '风险评估时间止',
  `sign_start` int(11) DEFAULT NULL COMMENT '签约时间起',
  `sign_end` int(11) DEFAULT NULL COMMENT '签约时间止',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目重要时间' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `item_time`
--

TRUNCATE TABLE `item_time`;
--
-- 转存表中的数据 `item_time`
--

INSERT INTO `item_time` (`id`, `item_id`, `collection_start`, `collection_end`, `vote_start`, `vote_end`, `assess_start`, `assess_end`, `risk_start`, `risk_end`, `sign_start`, `sign_end`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1511864473, 1511864473);

-- --------------------------------------------------------

--
-- 表的结构 `item_topic`
--

CREATE TABLE IF NOT EXISTS `item_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `topic_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topic_id` (`topic_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目风险评估话题' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `item_topic`
--

TRUNCATE TABLE `item_topic`;
-- --------------------------------------------------------

--
-- 表的结构 `layout`
--

CREATE TABLE IF NOT EXISTS `layout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `infos` text COMMENT '说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='房屋户型' AUTO_INCREMENT=6 ;

--
-- 插入之前先把表清空（truncate） `layout`
--

TRUNCATE TABLE `layout`;
--
-- 转存表中的数据 `layout`
--

INSERT INTO `layout` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '一室一厅', '', 1, 1509332132, 1509332149, NULL),
(2, '两室一厅', '', 1, 1509366966, 1509366966, NULL),
(3, '三室一厅', '', 1, 1509366976, 1509366976, NULL),
(4, '四室一厅', '', 1, 1509366986, 1509366986, NULL),
(5, '五室一厅', '', 1, 1509366997, 1509366997, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级 ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `level` int(11) DEFAULT NULL COMMENT ' 层级',
  `icon` text COMMENT ' 图标',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序',
  `url` text COMMENT ' 路由地址',
  `infos` text COMMENT ' 描述',
  `display` tinyint(1) DEFAULT '0' COMMENT ' 显示状态，0隐藏，1显示',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='功能与菜单' AUTO_INCREMENT=411 ;

--
-- 插入之前先把表清空（truncate） `menu`
--

TRUNCATE TABLE `menu`;
--
-- 转存表中的数据 `menu`
--

INSERT INTO `menu` (`id`, `parent_id`, `name`, `level`, `icon`, `sort`, `url`, `infos`, `display`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, '系统设置', 1, '<img src="/static/system/img/setting_tools.png"/>', 1, '/system/setting#', '', 1, 1, 1507862534, 1510882136, NULL),
(2, 1, '功能与菜单', 2, '<img src="/static/system/img/monitor_window_3d.png"/>', 1, '/system/menu/index', '', 1, 1, 1507865210, 1509155756, NULL),
(3, 1, '权限与角色', 2, '<img src="/static/system/img/role.png"/>', 3, '/system/role/index', '', 1, 1, 1507865414, 1509155756, NULL),
(4, 1, '系统用户', 2, '<img src="/static/system/img/folder_user.png"/>', 4, '/system/user/index', '', 1, 1, 1507866165, 1509155756, NULL),
(5, 2, '添加菜单', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/menu/add', '', 0, 1, 1507872000, 1509171136, NULL),
(6, 2, '菜单详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/menu/detail', '', 0, 1, 1507880446, 1509171136, NULL),
(7, 2, '菜单修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/menu/edit', '', 0, 1, 1507880485, 1509171136, NULL),
(8, 1, '组织与部门', 2, '<img src="/static/system/img/group_gear.png"/>', 2, '/system/dept/index', '', 1, 1, 1507880673, 1509155756, NULL),
(9, 0, '基础资料', 1, '<img src="/static/system/img/widgets.png"/>', 2, '/system/bases#', '', 1, 1, 1508145488, 1510882136, NULL),
(10, 9, '建筑结构', 2, '<img src="/static/system/img/server_database.png"/>', 3, '/system/buildingstruct/index', '', 1, 1, 1508145595, 1510294511, NULL),
(11, 1, '个人中心', 2, '<img src="/static/system/img/report_user.png"/>', 5, '/system/user/info', '', 0, 1, 1508145659, 1509155858, NULL),
(12, 9, '建筑使用性质', 2, '<img src="/static/system/img/insert_element.png"/>', 4, '/system/buildinguse/index', '', 1, 1, 1508145720, 1510294511, NULL),
(13, 9, '建筑状况', 2, '<img src="/static/system/img/add_on.png"/>', 5, '/system/buildingstatus/index', '', 1, 1, 1508146326, 1510294511, NULL),
(14, 169, '新闻公告分类', 2, '<img src="/static/system/img/sharepoint.png"/>', 0, '/system/newscate/index', '', 1, 1, 1508146527, 1509526851, NULL),
(15, 9, '特殊人群分类', 2, '<img src="/static/system/img/outlook_new_meeting.png"/>', 6, '/system/crowd/index', '', 1, 1, 1508146593, 1510294511, NULL),
(16, 1, '修改用户密码', 2, '<img src="/static/system/img/page_code.png"/>', 6, '/system/user/password', '', 0, 1, 1508146679, 1510210694, NULL),
(17, 2, '菜单排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/menu/sort', '', 0, 1, 1508146897, 1509171136, NULL),
(18, 2, '菜单显示状态', 3, '<img src="/static/system/img/monitor_window_3d.png"/>', 0, '/system/menu/show', '', 0, 1, 1508146986, 1509171136, NULL),
(19, 2, '菜单使用状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/menu/status', '', 0, 1, 1508147023, 1509171136, NULL),
(20, 2, '删除菜单', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/menu/delete', '', 0, 1, 1508147061, 1509171136, NULL),
(21, 2, '菜单恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/menu/restore', '', 0, 1, 1508147092, 1509171136, NULL),
(22, 2, '菜单销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/menu/destroy', '', 0, 1, 1508147134, 1508147140, NULL),
(23, 2, '所有菜单', 3, '<img src="/static/system/img/navigation.png"/>', 0, '/system/menu/all', '', 0, 1, 1508147228, 1508147228, NULL),
(24, 9, '常用民族管理', 2, '<img src="/static/system/img/account_balances.png"/>', 7, '/system/nation/index', '', 1, 1, 1508147838, 1510294511, NULL),
(25, 3, '添加角色', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/role/add', '', 0, 1, 1508894820, 1508896706, NULL),
(26, 3, '角色详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/role/detail', '', 0, 1, 1508894864, 1508894864, NULL),
(27, 3, '角色修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/role/edit', '', 0, 1, 1508894920, 1508894920, NULL),
(28, 3, '角色排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/role/sort', '', 0, 1, 1508894998, 1508894998, NULL),
(29, 3, '角色状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/role/status', '', 0, 1, 1508894998, 1508895543, NULL),
(30, 3, '删除角色', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/role/delete', '', 0, 1, 1508894998, 1508895558, NULL),
(31, 3, '角色恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/role/restore', '', 0, 1, 1508894998, 1508895590, NULL),
(32, 3, '角色销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/role/destroy', '', 0, 1, 1508894998, 1508895601, NULL),
(33, 4, '添加用户', 3, '<img src="/static/system/img/add.png" />', 0, '/system/user/add', NULL, 0, 1, 1508894998, 1508894998, NULL),
(34, 4, '用户详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/user/detail', NULL, 0, 1, 1508894998, 1508894998, NULL),
(35, 4, '用户修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/user/edit', NULL, 0, 1, 1508894998, 1508894998, NULL),
(36, 4, '用户状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/user/status', NULL, 0, 1, 1508894998, 1508894998, NULL),
(37, 4, '删除用户', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/user/delete', NULL, 0, 1, 1508894998, 1508894998, NULL),
(38, 4, '用户恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/user/restore', NULL, 0, 1, 1508894998, 1508894998, NULL),
(39, 4, '用户销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/user/destroy', NULL, 0, 1, 1508894998, 1508894998, NULL),
(40, 8, '添加部门', 3, '<img src="/static/system/img/add.png" />', 0, '/system/dept/add', '', 0, 1, 1508894998, 1509155977, NULL),
(41, 8, '部门详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/dept/detail', '', 0, 1, 1508894998, 1509155998, NULL),
(42, 8, '部门修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/dept/edit', '', 0, 1, 1508894998, 1509156007, NULL),
(43, 24, '添加常用民族', 3, '<img src="/static/system/img/add.png" />', 0, '/system/nation/add', '', 0, 1, 1508894998, 1509184467, NULL),
(44, 8, '部门状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/dept/status', '', 0, 1, 1508894998, 1509156031, NULL),
(45, 8, '删除部门', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/dept/delete', '', 0, 1, 1508894998, 1509156041, NULL),
(46, 8, '部门恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/dept/restore', '', 0, 1, 1508894998, 1509156050, NULL),
(47, 8, '部门销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/dept/destroy', '', 0, 1, 1508894998, 1509156058, NULL),
(48, 8, '所有部门', 3, '<img src="/static/system/img/navigation.png" />', 0, '/system/dept/all', '', 0, 1, 1508894998, 1509156069, NULL),
(49, 10, '添加结构', 3, '<img src="/static/system/img/add.png" />', 0, '/system/buildingstruct/add', '', 0, 1, 1508894998, 1509157802, NULL),
(50, 10, '结构详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/buildingstruct/detail', '', 0, 1, 1508894998, 1509157802, NULL),
(51, 10, '结构修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/buildingstruct/edit', '', 0, 1, 1508894998, 1509157802, NULL),
(52, 24, '常用民族详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/nation/detail', '', 0, 1, 1508894998, 1509184557, NULL),
(53, 10, '结构状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/buildingstruct/status', '', 0, 1, 1508894998, 1509157891, NULL),
(54, 10, '删除结构', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/buildingstruct/delete', '', 0, 1, 1508894998, 1509157891, NULL),
(55, 10, '结构恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/buildingstruct/restore', '', 0, 1, 1508894998, 1509157891, NULL),
(56, 10, '结构销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/buildingstruct/destroy', '', 0, 1, 1508894998, 1509157891, NULL),
(57, 24, '常用民族修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/nation/edit', '', 0, 1, 1508894998, 1509184614, NULL),
(58, 12, '添加使用性质', 3, '<img src="/static/system/img/add.png" />', 0, '/system/buildinguse/add', '', 0, 1, 1508894998, 1509171843, NULL),
(59, 12, '使用性质详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/buildinguse/detail', '', 0, 1, 1508894998, 1509171843, NULL),
(60, 12, '使用性质修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/buildinguse/edit', '', 0, 1, 1508894998, 1509171843, NULL),
(61, 24, '常用民族状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/nation/status', '', 0, 1, 1508894998, 1509184673, NULL),
(62, 12, '使用性质状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/buildinguse/status', '', 0, 1, 1508894998, 1509171843, NULL),
(63, 12, '删除使用性质', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/buildinguse/delete', '', 0, 1, 1508894998, 1509171843, NULL),
(64, 12, '使用性质恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/buildinguse/restore', '', 0, 1, 1508894998, 1509171843, NULL),
(65, 12, '使用性质销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/buildinguse/destroy', '', 0, 1, 1508894998, 1509171843, NULL),
(66, 24, '常用民族删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/nation/delete', '', 0, 1, 1508894998, 1509184709, NULL),
(67, 13, '添加建筑状况', 3, '<img src="/static/system/img/add.png" />', 0, '/system/buildingstatus/add', '', 0, 1, 1508894998, 1509173229, NULL),
(68, 13, '建筑状况详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/buildingstatus/detail', '', 0, 1, 1508894998, 1509173229, NULL),
(69, 13, '建筑状况修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/buildingstatus/edit', '', 0, 1, 1508894998, 1509173229, NULL),
(70, 24, '常用民族恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/nation/restore', '', 0, 1, 1508894998, 1509184852, NULL),
(71, 13, '建筑状况状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/buildingstatus/status', '', 0, 1, 1508894998, 1509173229, NULL),
(72, 13, '删除建筑状况', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/buildingstatus/delete', '', 0, 1, 1508894998, 1509173229, NULL),
(73, 13, '建筑状况恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/buildingstatus/restore', '', 0, 1, 1508894998, 1509173229, NULL),
(74, 13, '建筑状况销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/buildingstatus/destroy', '', 0, 1, 1508894998, 1509173229, NULL),
(75, 14, '添加新闻分类', 3, '<img src="/static/system/img/add.png" />', 0, '/system/newscate/add', '', 0, 1, 1508894998, 1509176009, NULL),
(76, 14, '新闻分类详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/newscate/detail', '', 0, 1, 1508894998, 1509176009, NULL),
(77, 14, '新闻分类修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/newscate/edit', '', 0, 1, 1508894998, 1509176009, NULL),
(78, 24, '常用民族销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/nation/destroy', '', 0, 1, 1508894998, 1509184836, NULL),
(79, 14, '新闻分类状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/newscate/status', '', 0, 1, 1508894998, 1509176009, NULL),
(80, 14, '删除新闻分类', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/newscate/delete', '', 0, 1, 1508894998, 1509176009, NULL),
(81, 14, '新闻分类恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/newscate/restore', '', 0, 1, 1508894998, 1509176009, NULL),
(82, 14, '新闻分类销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/newscate/destroy', '', 0, 1, 1508894998, 1509175999, NULL),
(83, 15, '添加特殊人群分类', 3, '<img src="/static/system/img/add.png" />', 0, '/system/crowd/add', '', 0, 1, 1508894998, 1509185223, NULL),
(84, 15, '特殊人群分类详情', 3, '<img src="/static/system/img/page_white_paste.png" />', 0, '/system/crowd/detail', '', 0, 1, 1508894998, 1509185223, NULL),
(85, 15, '特殊人群分类修改', 3, '<img src="/static/system/img/richtext_editor.png" />', 0, '/system/crowd/edit', '', 0, 1, 1508894998, 1509185223, NULL),
(86, 15, '所有特殊人群', 3, '<img src="/static/system/img/navigation.png" />', 0, '/system/crowd/all', '', 0, 1, 1508894998, 1509185332, NULL),
(87, 15, '特殊人群分类状态', 3, '<img src="/static/system/img/checked.png" />', 0, '/system/crowd/status', '', 0, 1, 1508894998, 1509185223, NULL),
(88, 15, '删除特殊人群分类', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/crowd/delete', '', 0, 1, 1508894998, 1509185223, NULL),
(89, 15, '特殊人群分类恢复', 3, '<img src="/static/system/img/recycle.png" />', 0, '/system/crowd/restore', '', 0, 1, 1508894998, 1509185223, NULL),
(90, 15, '特殊人群分类销毁', 3, '<img src="/static/system/img/destroy.png" />', 0, '/system/crowd/destroy', '', 0, 1, 1508894998, 1509185223, NULL),
(91, 9, '常用银行管理', 2, '<img src="/static/system/img/email_trace.png"/>', 8, '/system/bank/index', '', 1, 1, 1508898200, 1510294511, NULL),
(92, 91, '添加常用银行', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/bank/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(93, 91, '常用银行详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/bank/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(94, 91, '常用银行修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/bank/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(95, 91, '常用银行状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/bank/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(96, 91, '删除常用银行', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/bank/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(97, 91, '常用银行恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/bank/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(98, 91, '常用银行销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/bank/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(99, 9, '房屋户型', 2, '<img src="/static/system/img/bricks.png"/>', 9, '/system/layout/index', '', 1, 1, 1509331742, 1510294511, NULL),
(100, 99, '添加房屋户型', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/layout/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(101, 99, '房屋户型详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/layout/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(102, 99, '房屋户型修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/layout/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(103, 99, '房屋户型状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/layout/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(104, 99, '删除房屋户型', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/layout/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(105, 99, '房屋户型恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/layout/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(106, 99, '房屋户型销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/layout/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(107, 0, '安置房管理', 1, '<img src="/static/system/img/house.png"/>', 3, '/system/house#', '', 1, 1, 1509332337, 1510882136, NULL),
(108, 107, '安置房源', 2, '<img src="/static/system/img/books.png"/>', 0, '/system/house/index', '', 1, 1, 1509332381, 1509332381, NULL),
(109, 107, '房源小区', 2, '<img src="/static/system/img/chart_organisation_add.png"/>', 0, '/system/housecommunity/index', '', 1, 1, 1509332513, 1509332513, NULL),
(110, 107, '房源户型图', 2, '<img src="/static/system/img/navigation.png"/>', 0, '/system/houselayoutpic/index', '', 1, 1, 1509332583, 1509332583, NULL),
(111, 109, '添加房源小区', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/housecommunity/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(112, 109, '房源小区详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/housecommunity/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(113, 109, '房源小区修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/housecommunity/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(114, 109, '房源小区状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/housecommunity/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(115, 109, '删除房源小区', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/housecommunity/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(116, 109, '房源小区恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/housecommunity/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(117, 109, '房源小区销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/housecommunity/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(118, 110, '添加户型图', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/houselayoutpic/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(119, 110, '户型图详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/houselayoutpic/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(120, 110, '户型图修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/houselayoutpic/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(121, 110, '户型图状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/houselayoutpic/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(122, 110, '删除户型图', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/houselayoutpic/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(123, 110, '户型图恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/houselayoutpic/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(124, 110, '户型图销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/houselayoutpic/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(125, 108, '添加房源', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/house/add', '', 0, 1, 1509348295, 1509348295, NULL),
(126, 108, '房源详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/house/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(127, 108, '房源修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/house/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(128, 108, '房源状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/house/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(129, 108, '删除房源', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/house/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(130, 108, '房源恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/house/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(131, 108, '房源销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/house/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(132, 1, '接口文档', 2, '<img src="/static/system/img/chart_line.png"/>', 0, '/system/api/index', '', 1, 1, 1509416365, 1509416365, NULL),
(133, 132, '添加接口文档', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/api/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(134, 132, '接口文档详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/api/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(135, 132, '接口文档修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/api/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(136, 132, '接口文档排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/api/sort', NULL, 0, 1, 1508146326, 1508146326, NULL),
(137, 132, '接口文档状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/api/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(138, 132, '删除接口文档', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/api/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(139, 132, '接口文档恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/api/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(140, 132, '接口文档销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/api/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(141, 0, '资金管理', 1, '<img src="/static/system/img/small_business.png"/>', 4, '/system/funds#', '', 1, 1, 1509421185, 1510882136, NULL),
(142, 0, '评估公司管理', 1, '<img src="/static/system/img/report_design.png"/>', 5, '/system/company#', '', 1, 1, 1509421300, 1510882136, NULL),
(143, 142, '评估公司', 2, '<img src="/static/system/img/bricks.png"/>', 0, '/system/company/index', '', 1, 1, 1509421512, 1509421512, NULL),
(144, 141, '收入管理', 2, '<img src="/static/system/img/add_on.png"/>', 0, '/system/fundsin/index', '', 1, 1, 1509421612, 1509421705, NULL),
(145, 141, '支出管理', 2, '<img src="/static/system/img/on_the_shelves.png"/>', 0, '/system/fundsout/index', '', 1, 1, 1509421662, 1509421662, NULL),
(146, 143, '添加评估公司', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/company/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(147, 143, '评估公司详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/company/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(148, 143, '评估公司修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/company/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(149, 143, '评估公司排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/company/sort', NULL, 0, 1, 1508146326, 1508146326, NULL),
(150, 143, '评估公司状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/company/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(151, 143, '删除评估公司', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/company/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(152, 143, '评估公司恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/company/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(153, 143, '评估公司销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/company/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(154, 142, '评估师', 2, '<img src="/static/system/img/account_balances.png"/>', 0, '/system/companyvaluer/index', '', 1, 1, 1509436749, 1509436749, NULL),
(155, 154, '添加评估师', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/companyvaluer/add', '', 0, 1, 1508146326, 1509436918, NULL),
(156, 154, '评估师详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/companyvaluer/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(157, 154, '评估师修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/companyvaluer/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(158, 154, '评估师状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/companyvaluer/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(159, 154, '删除评估师', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/companyvaluer/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(160, 154, '评估师恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/companyvaluer/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(161, 154, '评估师销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/companyvaluer/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(162, 0, '项目管理', 1, '<img src="/static/system/img/web_disk.png"/>', 6, '/system/item#', '', 1, 1, 1509445088, 1510882136, NULL),
(163, 162, '项目档案及设置', 2, '<img src="/static/system/img/application_view_list.png"/>', 1, '/system/item/index', '', 1, 1, 1509446298, 1510306638, NULL),
(164, 163, '添加项目', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/item/add', '', 0, 1, 1509446352, 1509496790, NULL),
(165, 163, '项目详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/item/detail', '', 0, 1, 1509496863, 1509496863, NULL),
(166, 163, '项目修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/item/edit', '', 0, 1, 1509496907, 1509496907, NULL),
(167, 163, '项目状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/item/status', '', 0, 1, 1509497026, 1509497026, NULL),
(168, 163, '项目置顶', 3, '<img src="/static/system/img/top.png"/>', 0, '/system/item/istop', '', 0, 1, 1509498222, 1509531632, NULL),
(169, 0, '新闻公告管理', 1, '<img src="/static/system/img/monitor_window_3d.png"/>', 7, '/system/news#', '', 1, 1, 1509526699, 1510882136, NULL),
(170, 169, '新闻公告', 2, '<img src="/static/system/img/books.png"/>', 0, '/system/news/index', '', 1, 1, 1509526910, 1509526910, NULL),
(171, 170, '添加新闻公告', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/news/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(172, 170, '新闻公告详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/news/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(173, 170, '新闻公告修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/news/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(174, 170, '新闻公告排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/news/sort', NULL, 0, 1, 1508146326, 1508146326, NULL),
(175, 170, '新闻公告状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/news/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(176, 170, '新闻公告置顶', 3, '<img src="/static/system/img/top.png"/>', 0, '/system/istop/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(177, 107, '冻结安置房', 2, '<img src="/static/system/img/freeze.png"/>', 0, '/system/itemhouse/index', '', 1, 1, 1509588149, 1509588149, NULL),
(178, 177, '添加冻结房源', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemhouse/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(179, 177, '冻结房源删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/itemhouse/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(180, 141, '资金款项', 2, '<img src="/static/system/img/file_extension_log.png"/>', 0, '/system/fundsname/index', '', 1, 1, 1509610953, 1509610953, NULL),
(181, 180, '添加资金款项', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/fundsname/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(182, 180, '资金款项详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/fundsname/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(183, 180, '资金款项修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/fundsname/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(184, 180, '资金款项状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/fundsname/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(185, 180, '删除资金款项', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/fundsname/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(186, 180, '资金款项恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/fundsname/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(187, 180, '资金款项销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/fundsname/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(188, 0, '入户摸底管理', 1, '<img src="/static/system/img/butterfly.png"/>', 8, '/system/collection#', '', 1, 1, 1509616455, 1511591927, NULL),
(189, 188, '征地片区', 2, '<img src="/static/system/img/chart_pie.png"/>', 1, '/system/collectioncommunity/index', '', 1, 1, 1509616556, 1510302985, NULL),
(190, 189, '添加征地片区', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectioncommunity/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(191, 189, '征地片区详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectioncommunity/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(192, 189, '征地片区修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectioncommunity/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(193, 163, '删除项目', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/item/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(194, 163, '项目恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/item/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(195, 163, '项目销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/item/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(196, 170, '删除新闻公告', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/news/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(197, 170, '新闻公告恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/news/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(198, 170, '新闻公告销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/news/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(199, 189, '删除征地片区', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/collectioncommunity/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(200, 189, '征地片区恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectioncommunity/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(201, 189, '征地片区销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/collectioncommunity/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(202, 188, '入户摸底', 2, '<img src="/static/system/img/outlook_new_meeting.png"/>', 2, '/system/collection/index', '', 1, 1, 1509700423, 1511591941, NULL),
(203, 202, '添加入户摸底', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collection/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(204, 202, '入户摸底详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collection/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(205, 202, '入户摸底修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collection/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(206, 202, '入户摸底状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/collection/status', NULL, 0, 1, 1508146326, 1508146326, NULL),
(207, 202, '删除入户摸底', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/collection/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(208, 202, '入户摸底恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collection/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(209, 202, '入户摸底销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/collection/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(210, 188, '入户摸底-建筑信息', 2, '<img src="/static/system/img/house.png"/>', 3, '/system/collectionbuilding/index', '', 1, 1, 1509952296, 1510302985, NULL),
(211, 210, '添加入户摸底-建筑', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectionbuilding/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(212, 210, '入户摸底-建筑详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectionbuilding/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(213, 210, '入户摸底-建筑修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectionbuilding/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(214, 210, '删除入户摸底-建筑', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/collectionbuilding/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(215, 210, '入户摸底-建筑恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectionbuilding/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(216, 210, '入户摸底-建筑销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/collectionbuilding/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(217, 188, '入户摸底-家庭情况', 2, '<img src="/static/system/img/account_balances.png"/>', 4, '/system/collectionholder/index', '', 1, 1, 1510033478, 1510302985, NULL),
(218, 217, '添加入户摸底-家庭人员', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectionholder/add', NULL, 0, 1, 1508146326, 1508146326, NULL),
(219, 217, '入户摸底-家庭人员详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectionholder/detail', NULL, 0, 1, 1508146326, 1508146326, NULL),
(220, 217, '入户摸底-家庭人员修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectionholder/edit', NULL, 0, 1, 1508146326, 1508146326, NULL),
(221, 217, '删除入户摸底-家庭人员', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/collectionholder/delete', NULL, 0, 1, 1508146326, 1508146326, NULL),
(222, 217, '入户摸底-家庭人员恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectionholder/restore', NULL, 0, 1, 1508146326, 1508146326, NULL),
(223, 217, '入户摸底-家庭人员销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/collectionholder/destroy', NULL, 0, 1, 1508146326, 1508146326, NULL),
(224, 188, '入户摸底-特殊人群', 2, '<img src="/static/system/img/folder_user.png"/>', 5, '/system/collectionholdercrowd/index', '', 1, 1, 1510113371, 1510302985, NULL),
(225, 224, '添加入户摸底-特殊人群', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectionholdercrowd/add', NULL, 0, 1, 1508894864, 1508894864, NULL),
(226, 224, '入户摸底-特殊人群详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectionholdercrowd/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(227, 224, '入户摸底-特殊人群修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectionholdercrowd/edit', NULL, 0, 1, 1508894864, 1508894864, NULL),
(228, 224, '入户摸底-特殊人群删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/collectionholdercrowd/delete', NULL, 0, 1, 1508894864, 1508894864, NULL),
(229, 224, '入户摸底-特殊人群恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectionholdercrowd/restore', NULL, 0, 1, 1508894864, 1508894864, NULL),
(230, 224, '入户摸底-特殊人群销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/collectionholdercrowd/destroy', NULL, 0, 1, 1508894864, 1508894864, NULL),
(231, 0, '入户评估管理', 1, '<img src="/static/system/img/navigation.png"/>', 9, '/system/assess#', '', 1, 1, 1510137913, 1510882136, NULL),
(232, 231, '项目评估公司', 2, '<img src="/static/system/img/group_gear.png"/>', 0, '/system/itemcompany/index', '', 1, 1, 1510138108, 1510138108, NULL),
(233, 232, '添加项目评估公司', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemcompany/add', '', 0, 1, 1508894864, 1510138450, NULL),
(234, 232, '项目评估公司详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/itemcompany/detail', '', 0, 1, 1508894864, 1510138473, NULL),
(235, 232, '项目评估公司修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/itemcompany/edit', '', 0, 1, 1508894864, 1510138486, NULL),
(236, 232, '项目评估公司删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/itemcompany/delete', '', 0, 1, 1508894864, 1510138502, NULL),
(237, 231, '入户评估汇总', 2, '<img src="/static/system/img/bricks.png"/>', 0, '/system/assess/index', '', 1, 1, 1508894864, 1510209344, NULL),
(238, 231, '入户评估-房产评估', 2, '<img src="/static/system/img/books.png"/>', 0, '/system/assessestate/index', '', 1, 1, 1508894864, 1510206416, NULL),
(239, 238, '添加入户评估-房产评估', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/assessestate/add', '', 0, 1, 1508894864, 1510206587, NULL),
(240, 238, '入户评估-房产评估详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/assessestate/detail', '', 0, 1, 1508894864, 1510210000, NULL),
(241, 238, '入户评估-房产评估修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/assessestate/edit', '', 0, 1, 1508894864, 1510209971, NULL),
(242, 238, '入户评估-房产评估删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/assessestate/delete', '', 0, 1, 1508894864, 1510210036, NULL),
(243, 238, '入户评估-房产评估恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/assessestate/restore', '', 0, 1, 1508894864, 1510210056, NULL),
(244, 238, '入户评估-房产评估销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/assessestate/destroy', '', 0, 1, 1508894864, 1510210073, NULL),
(245, 251, '添加入户评估-资产评估', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/assessassets/add', NULL, 0, 1, 1508894864, 1508894864, NULL),
(246, 251, '入户评估-资产评估详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/assessassets/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(247, 251, '入户评估-资产评估修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/assessassets/edit', NULL, 0, 1, 1508894864, 1508894864, NULL),
(248, 237, '入户评估删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/assess/delete', '', 0, 1, 1508894864, 1510210179, NULL),
(249, 237, '入户评估恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/assess/restore', '', 0, 1, 1508894864, 1510210198, NULL),
(250, 237, '入户评估销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/assess/destroy', '', 0, 1, 1508894864, 1510210213, NULL),
(251, 231, '入户评估-资产评估', 2, '<img src="/static/system/img/web_disk.png"/>', 0, '/system/assessassets/index', '', 1, 1, 1510210590, 1510210590, NULL),
(252, 251, '入户评估-资产评估删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/assessassets/delete', NULL, 0, 1, 1508894864, 1508894864, NULL),
(253, 251, '入户评估-资产评估恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/assessassets/restore', NULL, 0, 1, 1508894864, 1508894864, NULL),
(254, 251, '入户评估-资产评估销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/assessassets/destroy', NULL, 0, 1, 1508894864, 1508894864, NULL),
(255, 238, '入户评估-房产评估状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/assessestate/status', '', 0, 1, 1510213563, 1510213563, NULL),
(256, 251, '入户评估-资产评估状态', 3, '<img src="/static/system/img/checked.png"/>', 0, '/system/assessassets/status', '', 0, 1, 1510213605, 1510213605, NULL),
(257, 0, '风险评估管理', 1, '<img src="/static/system/img/chart_bar.png"/>', 10, '/system/risk#', '', 1, 1, 1510278140, 1510882136, NULL),
(258, 257, '调查话题管理', 2, '<img src="/static/system/img/insert_element.png"/>', 0, '/system/topic/index', '', 1, 1, 1510278361, 1510278361, NULL),
(259, 257, '风险评估', 2, '<img src="/static/system/img/chart_line.png"/>', 0, '/system/risk/index', '', 1, 1, 1510280166, 1510280166, NULL),
(260, 257, '风险评估-自选话题', 2, '<img src="/static/system/img/database_table.png"/>', 0, '/system/risktopic/index', '', 1, 1, 1510280265, 1510280265, NULL),
(261, 162, '项目风险评估话题', 2, '<img src="/static/system/img/database_table.png"/>', 2, '/system/itemtopic/index', '', 0, 1, 1510280342, 1510280672, NULL),
(262, 9, '其他补偿事项', 2, '<img src="/static/system/img/books.png"/>', 2, '/system/object/index', '', 1, 1, 1510293674, 1510294511, NULL),
(263, 262, '添加补偿事项', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/object/add', '', 0, 1, 1510293715, 1510293715, NULL),
(264, 262, '补偿事项详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/object/detail', '', 0, 1, 1510294005, 1510294005, NULL),
(265, 262, '补偿事项修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/object/edit', '', 0, 1, 1510294062, 1510294062, NULL),
(266, 262, '补偿事项删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/object/delete', '', 0, 1, 1510294093, 1510294093, NULL),
(267, 262, '补偿事项恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/object/restore', '', 0, 1, 1510294125, 1510294125, NULL),
(268, 262, '补偿事项销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/object/destroy', '', 0, 1, 1510294164, 1510294164, NULL),
(269, 9, '重要补偿科目', 2, '<img src="/static/system/img/small_business.png"/>', 1, '/system/subject/index', '', 1, 1, 1510294261, 1510294511, NULL),
(270, 269, '添加补偿科目', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/subject/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(271, 269, '补偿科目详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/subject/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(272, 269, '补偿科目修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/subject/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(273, 269, '补偿科目删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/subject/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(274, 269, '补偿科目恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/subject/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(275, 269, '补偿科目销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/subject/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(276, 188, '入户摸底-其他事项', 2, '<img src="/static/system/img/application_form.png"/>', 6, '/system/collectionobject/index', '', 0, 1, 1510302958, 1511146980, NULL),
(277, 162, '项目重要补偿科目', 2, '<img src="/static/system/img/bricks.png"/>', 3, '/system/itemsubject/index', '', 0, 1, 1510307504, 1510307504, NULL),
(278, 0, '兑付与协议管理', 1, '<img src="/static/system/img/server_database.png"/>', 11, '/system/pay#', '', 1, 1, 1510308510, 1510882136, NULL),
(279, 278, '补偿协议', 2, '<img src="/static/system/img/file_extension_txt.png"/>', 0, '/system/pact/index', '', 0, 1, 1510309182, 1510817343, NULL),
(280, 278, '兑付汇总', 2, '<img src="/static/system/img/email_at_sign.png"/>', 0, '/system/pay/index', '', 1, 1, 1510309266, 1510309266, NULL),
(281, 277, '添加科目', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemsubject/add', '', 1, 1, 1510360550, 1510360550, NULL),
(282, 277, '科目详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/itemsubject/detail', '', 0, 1, 1510360637, 1510360637, NULL),
(283, 277, '科目修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/itemsubject/edit', '', 0, 1, 1510360683, 1510360683, NULL),
(284, 277, '科目删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/itemsubject/delete', '', 0, 1, 1510360721, 1510360721, NULL),
(285, 277, '科目恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/itemsubject/restore', '', 0, 1, 1510360784, 1510360784, NULL),
(286, 277, '科目销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/itemsubject/destroy', '', 0, 1, 1510360820, 1510360820, NULL),
(287, 280, '添加兑付', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/pay/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(288, 280, '兑付详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/pay/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(289, 280, '兑付修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/pay/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(290, 280, '兑付删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/pay/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(291, 280, '兑付恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/pay/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(292, 280, '兑付销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/pay/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(293, 278, '兑付-分权兑付', 2, '<img src="/static/system/img/group_gear.png"/>', 0, '/system/payholder/index', '', 0, 1, 1510641148, 1510811350, NULL),
(294, 293, '分权兑付详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/payholder/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(295, 293, '分权兑付修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/payholder/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(296, 278, '兑付-重要补偿科目', 2, '<img src="/static/system/img/bricks.png"/>', 0, '/system/paysubject/index', '', 0, 1, 1510811322, 1510811322, NULL),
(297, 296, '添加补偿科目', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/paysubject/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(298, 296, '补偿科目详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/paysubject/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(299, 296, '补偿科目修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/paysubject/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(300, 296, '补偿科目删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/paysubject/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(301, 296, '补偿科目恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/paysubject/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(302, 296, '补偿科目销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/paysubject/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(303, 278, '兑付-其他补偿事项', 2, '<img src="/static/system/img/books.png"/>', 0, '/system/payobject/index', '', 0, 1, 1510811579, 1510811579, NULL),
(304, 303, '补偿科目修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/payobject/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(305, 303, '补偿科目删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/payobject/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(306, 303, '补偿科目恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/payobject/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(307, 303, '补偿科目销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/payobject/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(308, 279, '添加补偿协议', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/pact/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(309, 279, '补偿协议详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/pact/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(310, 279, '补偿协议修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/pact/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(311, 0, '投票与统计', 1, '<img src="/static/system/img/chart_line.png"/>', 12, '/system/statis#', '', 1, 1, 1510882058, 1510882136, NULL),
(312, 311, '评估公司选票', 2, '<img src="/static/system/img/bricks.png"/>', 0, '/system/itemcompanyvote/index', '', 1, 1, 1510882226, 1510882226, NULL),
(313, 188, '安置房选择', 2, '<img src="/static/system/img/books.png"/>', 7, '/system/collectionholderhouse/index', '', 0, 1, 1510882290, 1511147485, NULL),
(314, 107, '房源价格', 2, '<img src="/static/system/img/chart_line.png"/>', 0, '/system/houseprice/index', '', 0, 1, 1510885890, 1510885890, NULL),
(315, 314, '添加房源价格', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/houseprice/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(316, 314, '房源价格详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/houseprice/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(317, 314, '房源价格修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/houseprice/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(318, 314, '房源价格删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/houseprice/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(319, 314, '房源价格恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/houseprice/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(320, 314, '房源价格销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/houseprice/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(321, 162, '控制流程设置', 2, '<img src="/static/system/img/chart_organisation_add.png"/>', 4, '/system/process/index', '', 1, 1, 1510912266, 1510912286, NULL),
(322, 321, '添加控制流程', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/process/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(323, 321, '控制流程详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/process/detail', NULL, 0, 1, 1508894864, 1508894864, NULL);
INSERT INTO `menu` (`id`, `parent_id`, `name`, `level`, `icon`, `sort`, `url`, `infos`, `display`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(324, 321, '控制流程修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/process/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(325, 321, '控制流程删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/process/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(326, 321, '控制流程恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/process/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(327, 321, '控制流程销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/process/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(328, 162, '项目流程控制', 2, '<img src="/static/system/img/combined_chart.png"/>', 5, '/system/itemprocess/index', '', 0, 1, 1511139613, 1511141405, NULL),
(329, 276, '添加入户摸底-其他事项', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectionobject/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(330, 276, '入户摸底-其他事项详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectionobject/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(331, 276, '入户摸底-其他事项修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectionobject/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(332, 276, '入户摸底-其他事项删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/collectionobject/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(333, 276, '入户摸底-其他事项恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectionobject/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(334, 276, '入户摸底-其他事项销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/collectionobject/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(335, 313, '添加安置房选择', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/collectionholderhouse/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(336, 313, '安置房选择详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/collectionholderhouse/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(337, 313, '安置房选择修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/collectionholderhouse/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(338, 313, '安置房选择删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/collectionholderhouse/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(339, 313, '安置房选择恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/collectionholderhouse/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(340, 313, '安置房选择销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/collectionholderhouse/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(341, 312, '添加评估公司选票', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemcompanyvote/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(342, 312, '评估公司选票详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/itemcompanyvote/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(343, 312, '评估公司选票删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/itemcompanyvote/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(344, 278, '兑付-安置房', 2, '<img src="/static/system/img/house.png"/>', 0, '/system/payholderhouse/index', '', 0, 1, 1511167691, 1511167787, NULL),
(345, 344, '添加兑付-安置房', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/payholderhouse/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(346, 344, '兑付-安置房排序', 3, '<img src="/static/system/img/text_list_numbers.png"/>', 0, '/system/payholderhouse/sort', NULL, 0, 1, 1508894998, 1508894998, NULL),
(347, 344, '兑付-安置房删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/payholderhouse/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(348, 162, '安置房价上浮设置', 2, '<img src="/static/system/img/house.png"/>', 6, '/system/itemhouseup/index', '', 0, 1, 1511335743, 1511335755, NULL),
(349, 107, '临时安置记录', 2, '<img src="/static/system/img/transit.png"/>', 0, '/system/housetransit/index', '', 1, 1, 1511337799, 1511337799, NULL),
(350, 107, '安置记录', 2, '<img src="/static/system/img/reset.png"/>', 0, '/system/houseresettle/index', '', 1, 1, 1511337841, 1511337841, NULL),
(351, 348, '添加房价上浮', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemhouseup/add', NULL, 0, 1, 1508894820, 1508896706, NULL),
(352, 348, '房价上浮详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/itemhouseup/detail', NULL, 0, 1, 1508894864, 1508894864, NULL),
(353, 348, '房价上浮修改', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/itemhouseup/edit', NULL, 0, 1, 1508894920, 1508894920, NULL),
(354, 348, '房价上浮删除', 3, '<img src="/static/system/img/broom.png" />', 0, '/system/itemhouseup/delete', NULL, 0, 1, 1508894998, 1508895558, NULL),
(355, 348, '房价上浮恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/itemhouseup/restore', NULL, 0, 1, 1508894998, 1508895590, NULL),
(356, 348, '房价上浮销毁', 3, '<img src="/static/system/img/destroy.png">', 0, '/system/itemhouseup/destroy', NULL, 0, 1, 1508894998, 1508895601, NULL),
(357, 163, '项目重要时间', 3, '<img src="/static/system/img/chart_pie.png"/>', 0, '/system/item/itemtime', '', 0, 1, 1511919590, 1511919590, NULL),
(358, 163, '项目审核', 3, '<img src="/static/system/img/recommend.png"/>', 0, '/system/item/check', '', 0, 1, 1511919672, 1511919672, NULL),
(359, 328, '添加项目流程', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemprocess/add', '', 0, 1, 1512009429, 1512009429, NULL),
(360, 328, '项目流程详情', 3, '<img src="/static/system/img/page_white_paste.png">', 0, '/system/itemprocess/detail', '', 0, 1, 1512009526, 1512009746, NULL),
(361, 328, '项目流程修改', 3, '<img src="/static/system/img/richtext_editor.png">', 0, '/system/itemprocess/edit', '', 0, 1, 1512009591, 1512009755, NULL),
(362, 328, '项目流程排序', 3, '<img src="/static/system/img/text_list_numbers.png">', 0, '/system/itemprocess/sort', '', 0, 1, 1512009641, 1512009765, NULL),
(363, 328, '项目流程删除', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/itemprocess/delete', '', 0, 1, 1512009713, 1512009713, NULL),
(364, 328, '项目流程恢复', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/itemprocess/restore', '', 0, 1, 1512009833, 1512009833, NULL),
(365, 328, '项目流程销毁', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/itemprocess/destroy', '', 0, 1, 1512009866, 1512009866, NULL),
(366, 349, '添加临时安置记录', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/housetransit/add', '', 0, 1, 1512012922, 1512012922, NULL),
(367, 349, '临时安置记录详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/housetransit/detail', '', 0, 1, 1512021631, 1512021631, NULL),
(368, 349, '修改临时安置记录', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/housetransit/edit', '', 0, 1, 1512021740, 1512021740, NULL),
(369, 349, '销毁临时安置记录', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/housetransit/destroy', '', 0, 1, 1512021859, 1512021859, NULL),
(370, 350, '添加安置记录', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/houseresettle/add', '', 0, 1, 1512022063, 1512022063, NULL),
(371, 350, '安置记录详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/houseresettle/detail', '', 0, 1, 1512022160, 1512022160, NULL),
(372, 350, '修改安置记录', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/houseresettle/edit', '', 0, 1, 1512022234, 1512022234, NULL),
(373, 350, '销毁安置记录', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/houseresettle/destroy', '', 0, 1, 1512022463, 1512022463, NULL),
(374, 145, '添加资金支出', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/fundsout/add', '', 0, 1, 1512023108, 1512023108, NULL),
(375, 145, '资金支出详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/fundsout/detail', '', 0, 1, 1512023203, 1512023203, NULL),
(376, 145, '修改资金支出', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/fundsout/edit', '', 0, 1, 1512023304, 1512023304, NULL),
(377, 145, '删除资金支出', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/fundsout/delete', '', 0, 1, 1512023471, 1512023554, NULL),
(378, 145, '恢复资金支出', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/fundsout/restore', '', 0, 1, 1512023610, 1512023610, NULL),
(379, 145, '销毁资金支出', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/fundsout/destroy', '', 0, 1, 1512023817, 1512023817, NULL),
(380, 144, '添加资金收入', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/fundsin/add', '', 0, 1, 1512025431, 1512025431, NULL),
(381, 144, '资金收入详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/fundsin/detail', '', 0, 1, 1512025485, 1512025485, NULL),
(382, 144, '修改资金收入', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/fundsin/edit', '', 0, 1, 1512025534, 1512025534, NULL),
(383, 144, '删除资金收入', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/fundsin/delete', '', 0, 1, 1512025655, 1512025655, NULL),
(384, 144, '恢复资金收入', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/fundsin/restore', '', 0, 1, 1512025732, 1512025732, NULL),
(385, 144, '销毁资金收入', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/fundsin/destroy', '', 0, 1, 1512025784, 1512025784, NULL),
(386, 261, '添加项目风险评估话题', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/itemtopic/add', '', 0, 1, 1512026955, 1512026955, NULL),
(387, 261, '项目风险评估话题详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/itemtopic/detail', '', 0, 1, 1512027136, 1512027136, NULL),
(388, 261, '修改项目风险评估话题', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/itemtopic/edit', '', 0, 1, 1512027223, 1512027223, NULL),
(389, 107, '房源管理费', 2, '<img src="/static/system/img/butterfly.png"/>', 0, '/system/housemanagefee/index', '', 1, 1, 1512027223, 1512027223, NULL),
(390, 261, '删除项目风险评估话题', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/itemtopic/delete', '', 0, 1, 1512028392, 1512028392, NULL),
(391, 261, '恢复项目风险评估话题', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/itemtopic/restore', '', 0, 1, 1512028655, 1512028655, NULL),
(392, 261, '销毁项目风险评估话题', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/itemtopic/destroy', '', 0, 1, 1512028760, 1512030266, NULL),
(393, 258, '添加调查话题', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/topic/add', '', 0, 1, 1512030850, 1512030850, NULL),
(394, 258, '调查话题详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/topic/detail', '', 0, 1, 1512030915, 1512030915, NULL),
(395, 258, '修改调查话题', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/topic/edit', '', 0, 1, 1512031002, 1512031002, NULL),
(396, 258, '删除调查话题', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/topic/delete', '', 0, 1, 1512031074, 1512031074, NULL),
(397, 258, '恢复调查话题', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/topic/restore', '', 0, 1, 1512031203, 1512031203, NULL),
(398, 258, '销毁调查话题', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/topic/destroy', '', 0, 1, 1512031280, 1512031280, NULL),
(399, 259, '添加风险评估', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/risk/add', '', 0, 1, 1512032351, 1512032351, NULL),
(400, 259, '风险评估详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/risk/detail', '', 0, 1, 1512032415, 1512032415, NULL),
(401, 259, '修改风险评估', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/risk/edit', '', 0, 1, 1512032478, 1512032478, NULL),
(402, 259, '删除风险评估', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/risk/delete', '', 0, 1, 1512032523, 1512032523, NULL),
(403, 259, '恢复风险评估', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/risk/restore', '', 0, 1, 1512032874, 1512032874, NULL),
(404, 259, '销毁风险评估', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/risk/destroy', '', 0, 1, 1512032907, 1512032907, NULL),
(405, 260, '添加风险评估-自选话题', 3, '<img src="/static/system/img/add.png"/>', 0, '/system/risktopic/add', '', 0, 1, 1512033328, 1512033328, NULL),
(406, 260, '风险评估-自选话题详情', 3, '<img src="/static/system/img/page_white_paste.png"/>', 0, '/system/risktopic/detail', '', 0, 1, 1512033389, 1512033389, NULL),
(407, 260, '修改风险评估-自选话题', 3, '<img src="/static/system/img/richtext_editor.png"/>', 0, '/system/risktopic/edit', '', 0, 1, 1512033453, 1512033453, NULL),
(408, 260, '删除风险评估-自选话题', 3, '<img src="/static/system/img/broom.png"/>', 0, '/system/risktopic/delete', '', 0, 1, 1512033508, 1512033508, NULL),
(409, 260, '恢复风险评估-自选话题', 3, '<img src="/static/system/img/recycle.png"/>', 0, '/system/risktopic/restore', '', 0, 1, 1512033558, 1512033558, NULL),
(410, 260, '销毁风险评估-自选话题', 3, '<img src="/static/system/img/destroy.png"/>', 0, '/system/risktopic/destroy', '', 0, 1, 1512033608, 1512033608, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `nation`
--

CREATE TABLE IF NOT EXISTS `nation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='常用民族列表' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `nation`
--

TRUNCATE TABLE `nation`;
--
-- 转存表中的数据 `nation`
--

INSERT INTO `nation` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '汉族', '', 1, 1509184988, 1509185030, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) DEFAULT NULL COMMENT '分类ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 标题',
  `item_id` int(11) DEFAULT NULL COMMENT '所属项目ID',
  `release_at` int(11) DEFAULT NULL COMMENT ' 发布时间',
  `keywords` text COMMENT ' 关键词',
  `infos` text COMMENT ' 简介',
  `content` text COMMENT ' 内容',
  `picture` text COMMENT '图片',
  `title_page` text COMMENT ' 封面',
  `url` text COMMENT '链接地址',
  `url_name` varchar(255) DEFAULT NULL COMMENT ' 链接名称',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序 ',
  `is_top` tinyint(1) DEFAULT '0' COMMENT '置顶，0否，1是',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `news`
--

TRUNCATE TABLE `news`;
--
-- 转存表中的数据 `news`
--

INSERT INTO `news` (`id`, `cate_id`, `name`, `item_id`, `release_at`, `keywords`, `infos`, `content`, `picture`, `title_page`, `url`, `url_name`, `sort`, `is_top`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '征收范围的公告', 0, 1509552000, '', '', '', '[]', '/uploads/image/20171103/4b46efc8f273e72e169c846ad5c90b54.jpg', '', '', 0, 0, 1, 1509531194, 1509694473, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `news_cate`
--

CREATE TABLE IF NOT EXISTS `news_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻公告分类' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `news_cate`
--

TRUNCATE TABLE `news_cate`;
--
-- 转存表中的数据 `news_cate`
--

INSERT INTO `news_cate` (`id`, `name`, `infos`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '征收范围公告', '', 1, 1509176668, 1509176668, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `object`
--

CREATE TABLE IF NOT EXISTS `object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `infos` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='其他补偿事项' AUTO_INCREMENT=9 ;

--
-- 插入之前先把表清空（truncate） `object`
--

TRUNCATE TABLE `object`;
--
-- 转存表中的数据 `object`
--

INSERT INTO `object` (`id`, `name`, `infos`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '装潢补助', '', 1510299886, 1510299886, NULL),
(2, '宽带', '', 1510299979, 1510299979, NULL),
(3, '固定电话', '', 1510299995, 1510299995, NULL),
(4, '有线电视', '', 1510300041, 1510300041, NULL),
(5, '电热水器', '', 1510300334, 1510300334, NULL),
(6, '燃气热水器', '', 1510300352, 1510300352, NULL),
(7, '太阳能热水器', '', 1510300365, 1510300365, NULL),
(8, '空调', '', 1510300383, 1510300383, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `pact`
--

CREATE TABLE IF NOT EXISTS `pact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `pay_holder_id` int(11) DEFAULT NULL COMMENT ' 兑付-产权人或承租人ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `content` longtext COMMENT ' 内容',
  `picture` text COMMENT '图片',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0失效，1有效',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='兑付协议' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `pact`
--

TRUNCATE TABLE `pact`;
--
-- 转存表中的数据 `pact`
--

INSERT INTO `pact` (`id`, `item_id`, `community_id`, `collection_id`, `pay_id`, `pay_holder_id`, `name`, `content`, `picture`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 2, 1, 1, '约定搬迁协议', '多', '[]', 1, 1510826924, 1510827015);

-- --------------------------------------------------------

--
-- 表的结构 `pay`
--

CREATE TABLE IF NOT EXISTS `pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `assess_id` int(11) DEFAULT NULL COMMENT ' 入户评估ID',
  `estate_amount` decimal(30,2) DEFAULT NULL COMMENT '房产补偿',
  `assets_amount` decimal(30,2) DEFAULT NULL COMMENT '资产补偿',
  `public_amount` decimal(30,2) DEFAULT NULL COMMENT '公共附属物评估总额',
  `public_num` int(11) DEFAULT NULL COMMENT '公共评估户数',
  `public_avg` decimal(30,2) DEFAULT NULL COMMENT '公共附属物平均',
  `subject_amount` decimal(30,2) DEFAULT NULL COMMENT '科目补偿',
  `object_amount` decimal(30,2) DEFAULT NULL COMMENT ' 事项补偿',
  `total` decimal(30,2) DEFAULT NULL COMMENT '补偿总额',
  `compensate_way` tinyint(1) DEFAULT '0' COMMENT '补偿方式，0为货币补偿，1为产权调换',
  `transit_way` tinyint(1) DEFAULT '0' COMMENT '过渡方式，0为货币过渡，1为周转房临时安置',
  `move_way` tinyint(1) DEFAULT '0' COMMENT '搬迁方式，0自行搬迁，1政府负责',
  `pay_way` tinyint(1) DEFAULT '1' COMMENT '兑付方式，0分权兑付，1合并兑付',
  `picture` text COMMENT '兑付表图',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_id` (`collection_id`,`item_id`,`community_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='兑付' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `pay`
--

TRUNCATE TABLE `pay`;
--
-- 转存表中的数据 `pay`
--

INSERT INTO `pay` (`id`, `item_id`, `community_id`, `collection_id`, `assess_id`, `estate_amount`, `assets_amount`, `public_amount`, `public_num`, `public_avg`, `subject_amount`, `object_amount`, `total`, `compensate_way`, `transit_way`, `move_way`, `pay_way`, `picture`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, 0, '0.00', '0.00', '0.00', 1, '0.00', '0.00', '220.00', '220.00', 1, 0, 0, 1, '[]', 1510804110, 1511258278, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `pay_holder`
--

CREATE TABLE IF NOT EXISTS `pay_holder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底 ID',
  `assess_id` int(11) DEFAULT NULL COMMENT ' 入户评估ID',
  `pay_id` int(11) DEFAULT NULL COMMENT '兑付ID',
  `collection_holder_id` int(11) DEFAULT NULL COMMENT ' 入户摸底-产权人或承租人ID',
  `holder` tinyint(1) DEFAULT '1' COMMENT '产权，1产权人，2承租人',
  `portion` decimal(10,2) DEFAULT NULL COMMENT ' 补偿份额',
  `estate_amount` decimal(30,2) DEFAULT NULL COMMENT '房产补偿',
  `assets_amount` decimal(30,2) DEFAULT NULL COMMENT '资产补偿',
  `public_amount` decimal(30,2) DEFAULT NULL COMMENT '公共附属物评估总额',
  `public_num` int(11) DEFAULT NULL COMMENT '公共评估户数',
  `public_avg` decimal(30,2) DEFAULT NULL COMMENT '公共附属物平均',
  `subject_amount` decimal(30,2) DEFAULT NULL COMMENT ' 科目补偿',
  `object_amount` decimal(30,2) DEFAULT NULL COMMENT ' 事项补偿',
  `total_amount` decimal(30,2) DEFAULT NULL COMMENT ' 补偿总额',
  `house_amount` decimal(30,2) DEFAULT NULL COMMENT '安置房款',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_holder_id` (`collection_holder_id`,`item_id`,`community_id`,`collection_id`,`pay_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='兑付-产权人或承租人' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `pay_holder`
--

TRUNCATE TABLE `pay_holder`;
--
-- 转存表中的数据 `pay_holder`
--

INSERT INTO `pay_holder` (`id`, `item_id`, `community_id`, `collection_id`, `assess_id`, `pay_id`, `collection_holder_id`, `holder`, `portion`, `estate_amount`, `assets_amount`, `public_amount`, `public_num`, `public_avg`, `subject_amount`, `object_amount`, `total_amount`, `house_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, NULL, 1, 1, 1, '100.00', '0.00', '0.00', '0.00', 1, '0.00', '0.00', '220.00', '220.00', NULL, 1510804110, 1511247722, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `pay_holder_house`
--

CREATE TABLE IF NOT EXISTS `pay_holder_house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT ' 片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `pay_id` int(11) DEFAULT NULL COMMENT ' 兑付ID',
  `pay_holder_id` int(11) DEFAULT NULL COMMENT ' 兑付-产权人或承租人ID',
  `house_id` int(11) DEFAULT NULL COMMENT ' 房源ID',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序',
  `price` decimal(10,2) DEFAULT NULL COMMENT ' 单价',
  `area` decimal(30,2) DEFAULT NULL COMMENT ' 面积',
  `amount` decimal(30,2) DEFAULT NULL COMMENT '房屋优惠总价',
  `amount_up` decimal(30,2) DEFAULT NULL COMMENT '房屋上浮总额',
  `total` decimal(30,2) DEFAULT NULL COMMENT ' 总价',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `house_id` (`house_id`,`pay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑付-安置房' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `pay_holder_house`
--

TRUNCATE TABLE `pay_holder_house`;
-- --------------------------------------------------------

--
-- 表的结构 `pay_holder_house_up`
--

CREATE TABLE IF NOT EXISTS `pay_holder_house_up` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_holder_house_id` int(11) NOT NULL COMMENT '兑付-房屋ID',
  `up_start` decimal(10,2) DEFAULT NULL COMMENT '上浮面积区间-起',
  `up_end` decimal(10,2) DEFAULT NULL COMMENT '上浮面积区间-止',
  `up_area` float(10,2) DEFAULT NULL COMMENT '上浮面积',
  `up_rate` float(10,2) DEFAULT NULL COMMENT '上浮比例（%）',
  `price` float(10,2) DEFAULT NULL COMMENT '基本单价',
  `amount` float(30,2) DEFAULT NULL COMMENT ' 上浮小计',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑付房屋-上浮' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `pay_holder_house_up`
--

TRUNCATE TABLE `pay_holder_house_up`;
-- --------------------------------------------------------

--
-- 表的结构 `pay_object`
--

CREATE TABLE IF NOT EXISTS `pay_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `pay_id` int(11) DEFAULT NULL COMMENT '兑付ID',
  `collection_object_id` int(11) DEFAULT NULL COMMENT ' 入户摸底-补偿事项ID',
  `price` decimal(30,2) DEFAULT NULL COMMENT ' 补偿单价',
  `number` decimal(10,2) DEFAULT NULL COMMENT ' 数量或次数',
  `amount` decimal(30,2) DEFAULT NULL COMMENT '补偿总价',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_object_id` (`collection_object_id`,`pay_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='兑付-补偿事项' AUTO_INCREMENT=3 ;

--
-- 插入之前先把表清空（truncate） `pay_object`
--

TRUNCATE TABLE `pay_object`;
--
-- 转存表中的数据 `pay_object`
--

INSERT INTO `pay_object` (`id`, `item_id`, `community_id`, `collection_id`, `pay_id`, `collection_object_id`, `price`, `number`, `amount`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 2, 1, 2, '120.00', '1.00', '120.00', 1510804110, 1510804796, NULL),
(2, 2, 1, 2, 1, 3, '100.00', '1.00', '100.00', 1510804110, 1510804655, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `pay_subject`
--

CREATE TABLE IF NOT EXISTS `pay_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `pay_id` int(11) DEFAULT NULL COMMENT '兑付ID',
  `item_subject_id` int(11) DEFAULT NULL COMMENT '项目重要补偿科目ID',
  `price` decimal(30,2) DEFAULT NULL COMMENT ' 补偿单价',
  `number` decimal(30,2) DEFAULT NULL COMMENT ' 数量',
  `unit` varchar(255) DEFAULT NULL COMMENT '数量单位',
  `times` int(11) DEFAULT '1' COMMENT '补偿次数',
  `amount` decimal(30,2) DEFAULT NULL COMMENT '补偿总价',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑付-项目重要补偿科目' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `pay_subject`
--

TRUNCATE TABLE `pay_subject`;
-- --------------------------------------------------------

--
-- 表的结构 `process`
--

CREATE TABLE IF NOT EXISTS `process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `infos` text COMMENT ' 说明 ',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='控制流程' AUTO_INCREMENT=4 ;

--
-- 插入之前先把表清空（truncate） `process`
--

TRUNCATE TABLE `process`;
--
-- 转存表中的数据 `process`
--

INSERT INTO `process` (`id`, `name`, `infos`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '入户摸底', '', 1511416268, 1511416362, NULL),
(2, '投票评估公司', '', 1511416973, 1511416973, NULL),
(3, '选定评估公司', '', 1511417039, 1511417039, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `process_url`
--

CREATE TABLE IF NOT EXISTS `process_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) DEFAULT NULL COMMENT '控制流程ID',
  `url` text COMMENT '操作地址',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='控制流程-操作地址' AUTO_INCREMENT=24 ;

--
-- 插入之前先把表清空（truncate） `process_url`
--

TRUNCATE TABLE `process_url`;
--
-- 转存表中的数据 `process_url`
--

INSERT INTO `process_url` (`id`, `process_id`, `url`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 1, '/system/collectionbuilding/destroy', 1511416362, 1511416362, NULL),
(8, 1, '/system/collectionbuilding/restore', 1511416362, 1511416362, NULL),
(9, 1, '/system/collectionbuilding/delete', 1511416362, 1511416362, NULL),
(10, 1, '/system/collectionbuilding/status', 1511416362, 1511416362, NULL),
(11, 1, '/system/collectionbuilding/edit', 1511416362, 1511416362, NULL),
(12, 1, '/system/collectionbuilding/add', 1511416362, 1511416362, NULL),
(13, 1, '/system/collection/destroy', 1511416362, 1511416362, NULL),
(14, 1, '/system/collection/restore', 1511416362, 1511416362, NULL),
(15, 1, '/system/collection/delete', 1511416362, 1511416362, NULL),
(16, 1, '/system/collection/status', 1511416362, 1511416362, NULL),
(17, 1, '/system/collection/edit', 1511416362, 1511416362, NULL),
(18, 1, '/system/collection/add', 1511416362, 1511416362, NULL),
(19, 2, '/system/itemcompanyvote/delete', 1511416973, 1511416973, NULL),
(20, 2, '/system/itemcompanyvote/add', 1511416973, 1511416973, NULL),
(21, 3, '/system/itemcompany/delete', 1511417039, 1511417039, NULL),
(22, 3, '/system/itemcompany/edit', 1511417039, 1511417039, NULL),
(23, 3, '/system/itemcompany/add', 1511417039, 1511417039, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `risk`
--

CREATE TABLE IF NOT EXISTS `risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT ' 入户摸底ID',
  `holder_id` int(11) DEFAULT NULL COMMENT ' 成员ID',
  `deputy` tinyint(1) DEFAULT '0' COMMENT '群众代表，0拒绝，1同意',
  `recommemd_holder_id` int(11) DEFAULT NULL COMMENT ' 推荐代表成员ID',
  `is_agree` tinyint(1) DEFAULT '1' COMMENT ' 方案意见，0反对，1同意',
  `compensate_way` tinyint(1) DEFAULT '0' COMMENT '补偿方式，0为货币补偿，1为产权调换',
  `compensate_price` int(11) DEFAULT NULL COMMENT '补偿单价',
  `transit_way` tinyint(1) DEFAULT '0' COMMENT '过渡方式，0为货币过渡，1为周转房临时安置',
  `move_way` tinyint(1) DEFAULT '0' COMMENT '搬迁方式，0自行搬迁，1政府负责',
  `opinion` text COMMENT '其他意见',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holder_id` (`holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='风险评估' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `risk`
--

TRUNCATE TABLE `risk`;
-- --------------------------------------------------------

--
-- 表的结构 `risk_topic`
--

CREATE TABLE IF NOT EXISTS `risk_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL COMMENT '项目ID',
  `community_id` int(11) DEFAULT NULL COMMENT '片区ID',
  `collection_id` int(11) DEFAULT NULL COMMENT '入户摸底ID',
  `holder_id` int(11) DEFAULT NULL COMMENT ' 成员ID',
  `risk_id` int(11) DEFAULT NULL COMMENT ' 风险评估ID',
  `topic_id` int(11) DEFAULT NULL COMMENT ' 话题ID',
  `answer` text COMMENT '回答',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='风险评估话题结果' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `risk_topic`
--

TRUNCATE TABLE `risk_topic`;
-- --------------------------------------------------------

--
-- 表的结构 `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级 ID',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `is_admin` tinyint(1) DEFAULT '0' COMMENT '角色类型，0受约束角色，1超级管理员',
  `level` int(11) DEFAULT NULL COMMENT ' 层级',
  `infos` text COMMENT ' 描述',
  `menu_ids` text COMMENT '授权菜单ID集合',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='权限与角色' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `role`
--

TRUNCATE TABLE `role`;
--
-- 转存表中的数据 `role`
--

INSERT INTO `role` (`id`, `parent_id`, `name`, `is_admin`, `level`, `infos`, `menu_ids`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, '内置超级管理员', 1, 1, '', '[]', 1, 1509096221, 1509171145, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `subject`
--

CREATE TABLE IF NOT EXISTS `subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称 ',
  `num_from` tinyint(1) DEFAULT '0' COMMENT '获取数量方式，0合法建筑面积，1合法建筑总价，2自定义',
  `unit` varchar(255) DEFAULT NULL COMMENT '数量单位',
  `infos` text COMMENT '说明',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='补偿科目' AUTO_INCREMENT=9 ;

--
-- 插入之前先把表清空（truncate） `subject`
--

TRUNCATE TABLE `subject`;
--
-- 转存表中的数据 `subject`
--

INSERT INTO `subject` (`id`, `name`, `num_from`, `unit`, `infos`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '签约奖励', 0, '㎡', '住宅性质的签约奖励', 1510296759, 1510296866, NULL),
(2, '签约奖励', 1, '元', '非住宅性质的签约奖励', 1510296882, 1510296907, NULL),
(3, '房屋奖励', 0, '㎡', '未新建、改建、扩建的房屋奖励', 1510297014, 1510297014, NULL),
(4, '搬迁奖励', 2, '户', '按协议期限内完成搬迁的奖励', 1510297078, 1510297078, NULL),
(5, '搬迁补助费', 0, '㎡', '由房屋征收部门负责搬迁的，不予支付搬迁补助费', 1510297825, 1510298029, NULL),
(6, '临时安置费', 0, '㎡', '选择货币补偿或提供周转房临时安置的，不发放临时安置费', 1510297964, 1510297964, NULL),
(7, '临时安置费上浮', 2, '元', '依据入户摸底中特殊人群有优惠政策，对临时安置费优惠标准上浮', 1510298446, 1510298446, NULL),
(8, '停产停业损失', 2, '户', '', 1510298618, 1510298618, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `topic`
--

CREATE TABLE IF NOT EXISTS `topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COMMENT '名称',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='调查话题' AUTO_INCREMENT=1 ;

--
-- 插入之前先把表清空（truncate） `topic`
--

TRUNCATE TABLE `topic`;
-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_id` int(11) DEFAULT NULL COMMENT '部门ID',
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  `name` varchar(255) DEFAULT '' COMMENT ' 名称',
  `signature` text COMMENT '电子签名',
  `phone` varchar(255) DEFAULT NULL COMMENT '移动电话',
  `office_phone` varchar(255) DEFAULT NULL COMMENT '办公电话',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `infos` text COMMENT ' 描述',
  `username` varchar(255) DEFAULT NULL COMMENT '用户名',
  `password` varchar(255) DEFAULT NULL COMMENT ' 密码',
  `secret_key` varchar(255) DEFAULT NULL COMMENT ' 密钥',
  `login_at` int(11) DEFAULT NULL COMMENT '最近登录时间',
  `login_ip` varchar(255) DEFAULT NULL COMMENT '最近登录IP',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='系统用户' AUTO_INCREMENT=2 ;

--
-- 插入之前先把表清空（truncate） `user`
--

TRUNCATE TABLE `user`;
--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `dept_id`, `role_id`, `name`, `signature`, `phone`, `office_phone`, `email`, `infos`, `username`, `password`, `secret_key`, `login_at`, `login_ip`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 1, '', '/uploads/image/20171103/1be724c55dcc9ee0f62fb14b170ab0d1.png', '', '', '', '', 'demo', 'e10adc3949ba59abbe56e057f20f883e', 'E2DB6AF6-D1C9-68FF-816D-0D8CDA322FFA', 1512094796, '113.250.252.132', 1, 1509544403, 1512029489, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
