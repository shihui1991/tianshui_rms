/*
Navicat MySQL Data Transfer

Source Server         : localhost_phpwamp
Source Server Version : 50554
Source Host           : 127.0.0.1:3306
Source Database       : tianshui_rms

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2017-11-01 18:28:02
*/

CREATE DATABASE IF NOT EXISTS `tianshui_rms` DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

USE `tianshui_rms`;

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for api
-- ----------------------------
DROP TABLE IF EXISTS `api`;
CREATE TABLE `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级ID',
  `level` int(11) DEFAULT NULL COMMENT '层级',
  `name` varchar(255) DEFAULT NULL COMMENT ' 接口名称',
  `url` text COMMENT '接口地址',
  `infos` text COMMENT ' 接口描述',
  `params` text COMMENT '参数',
  `response` text COMMENT '响应',
  `sort` int(11) DEFAULT NULL COMMENT ' 排序',
  `status` tinyint(1) DEFAULT '1' COMMENT ' 状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='接口文档';

-- ----------------------------
-- Records of api
-- ----------------------------
INSERT INTO `api` VALUES ('1', '0', '1', '登录', '/api/index/index', '', '[{\"name\":\"p1\",\"value\":\"pv1\",\"infos\":\"\"},{\"name\":\"p2\",\"value\":\"pv2\",\"infos\":\"\"}]', '[{\"name\":\"r1\",\"value\":\"rv1\",\"infos\":\"\"},{\"name\":\"r2\",\"value\":\"rv2\",\"infos\":\"\"}]', '0', '1', '1509419246', '1509420332', null);
INSERT INTO `api` VALUES ('2', '1', '2', '注册', '/api/index/signup', '', '[{\"name\":\"p3\",\"value\":\"pv3\",\"infos\":\"\"}]', '[{\"name\":\"r3\",\"value\":\"rv3\",\"infos\":\"\"}]', '0', '1', '1509420423', '1509420423', null);

-- ----------------------------
-- Table structure for bank
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='常用银行列表';

-- ----------------------------
-- Records of bank
-- ----------------------------
INSERT INTO `bank` VALUES ('1', '中国工商银行', '', '1', '1509186075', '1509186075', null);

-- ----------------------------
-- Table structure for building_status
-- ----------------------------
DROP TABLE IF EXISTS `building_status`;
CREATE TABLE `building_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='建筑状态';

-- ----------------------------
-- Records of building_status
-- ----------------------------
INSERT INTO `building_status` VALUES ('1', '正常', '房屋权属证明记载面积、结构与现状房屋相符，并在院内无新建、改建、扩建房屋', '1', '1509173390', '1509174217', null);
INSERT INTO `building_status` VALUES ('2', '新建', '合法新建', '1', '1509173415', '1509174278', null);
INSERT INTO `building_status` VALUES ('3', '改建', '合法改建', '1', '1509173739', '1509174285', null);
INSERT INTO `building_status` VALUES ('4', '扩建', '合法扩建', '1', '1509173746', '1509174295', null);
INSERT INTO `building_status` VALUES ('5', '违建', '违章建筑', '1', '1509174133', '1509174654', null);

-- ----------------------------
-- Table structure for building_struct
-- ----------------------------
DROP TABLE IF EXISTS `building_struct`;
CREATE TABLE `building_struct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='建筑结构';

-- ----------------------------
-- Records of building_struct
-- ----------------------------
INSERT INTO `building_struct` VALUES ('1', '砖混', '', '1', '1509170861', '1509171157', null);
INSERT INTO `building_struct` VALUES ('2', '钢混', '', '1', '1509172299', '1509172299', null);
INSERT INTO `building_struct` VALUES ('3', '砖木', '', '1', '1509172444', '1509172444', null);

-- ----------------------------
-- Table structure for building_use
-- ----------------------------
DROP TABLE IF EXISTS `building_use`;
CREATE TABLE `building_use` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='建筑使用性质';

-- ----------------------------
-- Records of building_use
-- ----------------------------
INSERT INTO `building_use` VALUES ('1', '住宅', '', '1', '1509172186', '1509172186', null);
INSERT INTO `building_use` VALUES ('2', '附属物', '', '1', '1509172208', '1509172208', null);
INSERT INTO `building_use` VALUES ('3', '公共附属物', '', '1', '1509172224', '1509172224', null);
INSERT INTO `building_use` VALUES ('4', '办公', '', '1', '1509172253', '1509172253', null);
INSERT INTO `building_use` VALUES ('5', '商服', '', '1', '1509172263', '1509172263', null);
INSERT INTO `building_use` VALUES ('6', '生产加工', '', '1', '1509172276', '1509172276', null);

-- ----------------------------
-- Table structure for company
-- ----------------------------
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='评估公司';

-- ----------------------------
-- Records of company
-- ----------------------------
INSERT INTO `company` VALUES ('1', '0', '重庆市步联科技有限公司', '步联科技', null, '重庆市渝北区杨柳北路9号力华科谷A区203', '步联科技', '步联科技', '02363624610', '02363624610', '步联科技', '', null, 'buliankeji', '123456', '8620b96ee66ada6c820df19b2df71517', '0', '1', '1509435208', '1509497326', null);
INSERT INTO `company` VALUES ('2', '1', '重庆市贤盾科技有限公司', '贤盾科技', '/uploads/20171031/fca9277be78db5eabf5cc6e244a38230.png', '重庆市贤盾科技有限公司', '贤盾科技', '贤盾科技', '贤盾科技', '贤盾科技', '贤盾科技', '', null, 'xiandunkeji', '123456', 'a8e9dea7dcaaeefa5233e5046ca8270c', '0', '1', '1509435817', '1509443023', null);

-- ----------------------------
-- Table structure for company_valuer
-- ----------------------------
DROP TABLE IF EXISTS `company_valuer`;
CREATE TABLE `company_valuer` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='评估师';

-- ----------------------------
-- Records of company_valuer
-- ----------------------------
INSERT INTO `company_valuer` VALUES ('1', '1', '步联科技', '12345678910', '123456789', '1509379200', '', '1', '1509442882', '1509501558', null);

-- ----------------------------
-- Table structure for crowd
-- ----------------------------
DROP TABLE IF EXISTS `crowd`;
CREATE TABLE `crowd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上级分类ID',
  `level` int(11) DEFAULT NULL COMMENT '层级',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='特殊人群分类';

-- ----------------------------
-- Records of crowd
-- ----------------------------
INSERT INTO `crowd` VALUES ('1', '0', '1', '残疾', '', '1', '1509180587', '1509181114', null);
INSERT INTO `crowd` VALUES ('2', '1', '2', '一级残疾', '', '1', '1509180628', '1509181114', null);
INSERT INTO `crowd` VALUES ('3', '1', '2', '二级残疾', '', '1', '1509180663', '1509181114', null);
INSERT INTO `crowd` VALUES ('4', '1', '2', '三级残疾', '', '1', '1509180673', '1509181114', null);
INSERT INTO `crowd` VALUES ('5', '1', '2', '四级残疾', '', '1', '1509180687', '1509181114', null);
INSERT INTO `crowd` VALUES ('6', '0', '1', '优抚对象', '', '1', '1509180730', '1509181114', null);
INSERT INTO `crowd` VALUES ('7', '6', '2', '城市居民最低生活保障', '', '1', '1509180810', '1509181114', null);
INSERT INTO `crowd` VALUES ('8', '6', '2', '伤残军人及优抚对象', '', '1', '1509180870', '1509181114', null);
INSERT INTO `crowd` VALUES ('9', '6', '2', '建档困难职工家庭及特困职工家庭', '', '1', '1509180930', '1509181114', null);
INSERT INTO `crowd` VALUES ('10', '0', '1', '失独', '', '1', '1509180969', '1509181114', null);
INSERT INTO `crowd` VALUES ('11', '10', '2', '失独家庭', '', '1', '1509181074', '1509181114', null);

-- ----------------------------
-- Table structure for dept
-- ----------------------------
DROP TABLE IF EXISTS `dept`;
CREATE TABLE `dept` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='组织与部门';

-- ----------------------------
-- Records of dept
-- ----------------------------
INSERT INTO `dept` VALUES ('1', '0', '管理层', '0', '1', '', '1', '1509152121', '1509171143', null);

-- ----------------------------
-- Table structure for house
-- ----------------------------
DROP TABLE IF EXISTS `house`;
CREATE TABLE `house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `community_id` int(11) DEFAULT NULL COMMENT '区域 ID',
  `building` int(11) DEFAULT NULL COMMENT ' 几栋',
  `unit` int(11) DEFAULT NULL COMMENT ' 几单元',
  `floor` int(11) DEFAULT NULL COMMENT ' 几楼',
  `number` int(11) DEFAULT NULL COMMENT ' 几号',
  `layout_id` int(11) DEFAULT NULL COMMENT ' 户型 ID',
  `layout_pic_id` int(11) DEFAULT NULL COMMENT '户型图 ID',
  `area` float DEFAULT NULL COMMENT '面积 （平米）',
  `total_floor` int(11) DEFAULT NULL COMMENT '总楼层',
  `has_lift` tinyint(1) DEFAULT '0' COMMENT '是否配电梯，0否，1是',
  `property_manage_fee` float DEFAULT NULL COMMENT '物业管理费单价 (元/平米/月）',
  `public_fee` float DEFAULT NULL COMMENT '公摊费单价 （元/月）',
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='安置房源';

-- ----------------------------
-- Records of house
-- ----------------------------
INSERT INTO `house` VALUES ('1', '1', '0', '0', '0', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '{\"1\":[\"\\/uploads\\/20171031\\/9deecbfb338d8d0a493a576672caa75c.png\",\"\\/uploads\\/20171031\\/1b88ef251bc17d3b6b2ed30d02536791.png\"],\"2\":[\"\\/uploads\\/20171031\\/7b9f505b12b193da9f8413308f7d59e0.png\",\"\\/uploads\\/20171031\\/d3647939037c08130cf80d2b86b81b3d.png\"]}', '1508860800', '0', '1509413843', '1509415819', null);

-- ----------------------------
-- Table structure for house_community
-- ----------------------------
DROP TABLE IF EXISTS `house_community`;
CREATE TABLE `house_community` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` text COMMENT '地址',
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='安置房源小区';

-- ----------------------------
-- Records of house_community
-- ----------------------------
INSERT INTO `house_community` VALUES ('1', '渝北区杨柳北路9号', '力华科谷', '', '1', '1509333835', '1509333849', null);
INSERT INTO `house_community` VALUES ('2', '渝北区杨柳北路10号', '联通大厦', '', '1', '1509333921', '1509333921', null);

-- ----------------------------
-- Table structure for house_layout_pic
-- ----------------------------
DROP TABLE IF EXISTS `house_layout_pic`;
CREATE TABLE `house_layout_pic` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='安置房源户型图';

-- ----------------------------
-- Records of house_layout_pic
-- ----------------------------
INSERT INTO `house_layout_pic` VALUES ('1', '1', '1', 'A', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', '1', '1509345081', '1509373005', null);
INSERT INTO `house_layout_pic` VALUES ('2', '1', '1', 'B', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', '1', '1509345103', '1509374240', null);
INSERT INTO `house_layout_pic` VALUES ('3', '2', '1', 'A', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', '1', '1509345280', '1509345364', null);
INSERT INTO `house_layout_pic` VALUES ('4', '2', '2', 'C', '/uploads/20171030/0ebb38a09a84b424391c472569a76edc.jpg', '', '1', '1509367040', '1509526375', null);

-- ----------------------------
-- Table structure for item
-- ----------------------------
DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `record_num` varchar(255) DEFAULT NULL COMMENT '档案编号',
  `area` text COMMENT ' 征收范围',
  `household` int(11) DEFAULT NULL COMMENT ' 预计户数',
  `funds` float DEFAULT NULL COMMENT ' 预算资金',
  `house` int(11) DEFAULT NULL COMMENT ' 预计安置房数',
  `picture` text COMMENT '图片',
  `infos` text COMMENT '说明',
  `is_top` tinyint(1) DEFAULT '0' COMMENT '置顶，0否，1是',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='项目基本信息';

-- ----------------------------
-- Records of item
-- ----------------------------
INSERT INTO `item` VALUES ('1', '西关片区棚户区改造', '0123456', '东至，西至，', '200', '200000', '300', null, '', '0', '1', '1509531788', '1509531788');

-- ----------------------------
-- Table structure for layout
-- ----------------------------
DROP TABLE IF EXISTS `layout`;
CREATE TABLE `layout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `infos` text COMMENT '说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='房屋户型';

-- ----------------------------
-- Records of layout
-- ----------------------------
INSERT INTO `layout` VALUES ('1', '一室一厅', '', '1', '1509332132', '1509332149', null);
INSERT INTO `layout` VALUES ('2', '两室一厅', '', '1', '1509366966', '1509366966', null);
INSERT INTO `layout` VALUES ('3', '三室一厅', '', '1', '1509366976', '1509366976', null);
INSERT INTO `layout` VALUES ('4', '四室一厅', '', '1', '1509366986', '1509366986', null);
INSERT INTO `layout` VALUES ('5', '五室一厅', '', '1', '1509366997', '1509366997', null);

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
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
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8 COMMENT='功能与菜单';

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('1', '0', '系统设置', '1', '<img src=\"/static/system/img/setting_tools.png\"/>', '0', '/system/setting#', '', '1', '1', '1507862534', '1509171136', null);
INSERT INTO `menu` VALUES ('2', '1', '功能与菜单', '2', '<img src=\"/static/system/img/monitor_window_3d.png\"/>', '1', '/system/menu/index', '', '1', '1', '1507865210', '1509155756', null);
INSERT INTO `menu` VALUES ('3', '1', '权限与角色', '2', '<img src=\"/static/system/img/role.png\"/>', '3', '/system/role/index', '', '1', '1', '1507865414', '1509155756', null);
INSERT INTO `menu` VALUES ('4', '1', '系统用户', '2', '<img src=\"/static/system/img/folder_user.png\"/>', '4', '/system/user/index', '', '1', '1', '1507866165', '1509155756', null);
INSERT INTO `menu` VALUES ('5', '2', '添加菜单', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/menu/add', '', '0', '1', '1507872000', '1509171136', null);
INSERT INTO `menu` VALUES ('6', '2', '菜单详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/menu/detail', '', '0', '1', '1507880446', '1509171136', null);
INSERT INTO `menu` VALUES ('7', '2', '菜单修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/menu/edit', '', '0', '1', '1507880485', '1509171136', null);
INSERT INTO `menu` VALUES ('8', '1', '组织与部门', '2', '<img src=\"/static/system/img/group_gear.png\"/>', '2', '/system/dept/index', '', '1', '1', '1507880673', '1509155756', null);
INSERT INTO `menu` VALUES ('9', '0', '基础资料', '1', '<img src=\"/static/system/img/widgets.png\"/>', '0', '/system/bases#', '', '1', '1', '1508145488', '1509171136', null);
INSERT INTO `menu` VALUES ('10', '9', '建筑结构', '2', '<img src=\"/static/system/img/server_database.png\"/>', '0', '/system/buildingstruct/index', '', '1', '1', '1508145595', '1509171136', null);
INSERT INTO `menu` VALUES ('11', '1', '个人中心', '2', '<img src=\"/static/system/img/report_user.png\"/>', '5', '/system/user/info', '', '0', '1', '1508145659', '1509155858', null);
INSERT INTO `menu` VALUES ('12', '9', '建筑使用性质', '2', '<img src=\"/static/system/img/insert_element.png\"/>', '0', '/system/buildinguse/index', '', '1', '1', '1508145720', '1509171687', null);
INSERT INTO `menu` VALUES ('13', '9', '建筑状况', '2', '<img src=\"/static/system/img/add_on.png\"/>', '0', '/system/buildingstatus/index', '', '1', '1', '1508146326', '1509173243', null);
INSERT INTO `menu` VALUES ('14', '169', '新闻公告分类', '2', '<img src=\"/static/system/img/sharepoint.png\"/>', '0', '/system/newscate/index', '', '1', '1', '1508146527', '1509526851', null);
INSERT INTO `menu` VALUES ('15', '9', '特殊人群分类', '2', '<img src=\"/static/system/img/outlook_new_meeting.png\"/>', '0', '/system/crowd/index', '', '1', '1', '1508146593', '1509178461', null);
INSERT INTO `menu` VALUES ('16', '1', '修改用户密码', '2', '<img src=\"/static/system/img/page_code.png\"/>', '6', '/system/user/password', '', '0', '1', '1508146679', '1509155858', null);
INSERT INTO `menu` VALUES ('17', '2', '菜单排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/menu/sort', '', '0', '1', '1508146897', '1509171136', null);
INSERT INTO `menu` VALUES ('18', '2', '菜单显示状态', '3', '<img src=\"/static/system/img/monitor_window_3d.png\"/>', '0', '/system/menu/show', '', '0', '1', '1508146986', '1509171136', null);
INSERT INTO `menu` VALUES ('19', '2', '菜单使用状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/menu/status', '', '0', '1', '1508147023', '1509171136', null);
INSERT INTO `menu` VALUES ('20', '2', '删除菜单', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/menu/delete', '', '0', '1', '1508147061', '1509171136', null);
INSERT INTO `menu` VALUES ('21', '2', '菜单恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/menu/restore', '', '0', '1', '1508147092', '1509171136', null);
INSERT INTO `menu` VALUES ('22', '2', '菜单销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/menu/destroy', '', '0', '1', '1508147134', '1508147140', null);
INSERT INTO `menu` VALUES ('23', '2', '所有菜单', '3', '<img src=\"/static/system/img/navigation.png\"/>', '0', '/system/menu/all', '', '0', '1', '1508147228', '1508147228', null);
INSERT INTO `menu` VALUES ('24', '9', '常用民族管理', '2', '<img src=\"/static/system/img/account_balances.png\"/>', '0', '/system/nation/index', '', '1', '1', '1508147838', '1509184430', null);
INSERT INTO `menu` VALUES ('25', '3', '添加角色', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/role/add', '', '0', '1', '1508894820', '1508896706', null);
INSERT INTO `menu` VALUES ('26', '3', '角色详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/role/detail', '', '0', '1', '1508894864', '1508894864', null);
INSERT INTO `menu` VALUES ('27', '3', '角色修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/role/edit', '', '0', '1', '1508894920', '1508894920', null);
INSERT INTO `menu` VALUES ('28', '3', '角色排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/role/sort', '', '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('29', '3', '角色状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/role/status', '', '0', '1', '1508894998', '1508895543', null);
INSERT INTO `menu` VALUES ('30', '3', '删除角色', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/role/delete', '', '0', '1', '1508894998', '1508895558', null);
INSERT INTO `menu` VALUES ('31', '3', '角色恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/role/restore', '', '0', '1', '1508894998', '1508895590', null);
INSERT INTO `menu` VALUES ('32', '3', '角色销毁', '3', '<img src=\"/static/system/img/destroy.png\">', '0', '/system/role/destroy', '', '0', '1', '1508894998', '1508895601', null);
INSERT INTO `menu` VALUES ('33', '4', '添加用户', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/user/add', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('34', '4', '用户详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/user/detail', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('35', '4', '用户修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/user/edit', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('36', '4', '用户状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/user/status', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('37', '4', '删除用户', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/user/delete', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('38', '4', '用户恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/user/restore', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('39', '4', '用户销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/user/destroy', null, '0', '1', '1508894998', '1508894998', null);
INSERT INTO `menu` VALUES ('40', '8', '添加部门', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/dept/add', '', '0', '1', '1508894998', '1509155977', null);
INSERT INTO `menu` VALUES ('41', '8', '部门详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/dept/detail', '', '0', '1', '1508894998', '1509155998', null);
INSERT INTO `menu` VALUES ('42', '8', '部门修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/dept/edit', '', '0', '1', '1508894998', '1509156007', null);
INSERT INTO `menu` VALUES ('43', '24', '添加常用民族', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/nation/add', '', '0', '1', '1508894998', '1509184467', null);
INSERT INTO `menu` VALUES ('44', '8', '部门状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/dept/status', '', '0', '1', '1508894998', '1509156031', null);
INSERT INTO `menu` VALUES ('45', '8', '删除部门', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/dept/delete', '', '0', '1', '1508894998', '1509156041', null);
INSERT INTO `menu` VALUES ('46', '8', '部门恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/dept/restore', '', '0', '1', '1508894998', '1509156050', null);
INSERT INTO `menu` VALUES ('47', '8', '部门销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/dept/destroy', '', '0', '1', '1508894998', '1509156058', null);
INSERT INTO `menu` VALUES ('48', '8', '所有部门', '3', '<img src=\"/static/system/img/navigation.png\" />', '0', '/system/dept/all', '', '0', '1', '1508894998', '1509156069', null);
INSERT INTO `menu` VALUES ('49', '10', '添加结构', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/buildingstruct/add', '', '0', '1', '1508894998', '1509157802', null);
INSERT INTO `menu` VALUES ('50', '10', '结构详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/buildingstruct/detail', '', '0', '1', '1508894998', '1509157802', null);
INSERT INTO `menu` VALUES ('51', '10', '结构修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/buildingstruct/edit', '', '0', '1', '1508894998', '1509157802', null);
INSERT INTO `menu` VALUES ('52', '24', '常用民族详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/nation/detail', '', '0', '1', '1508894998', '1509184557', null);
INSERT INTO `menu` VALUES ('53', '10', '结构状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/buildingstruct/status', '', '0', '1', '1508894998', '1509157891', null);
INSERT INTO `menu` VALUES ('54', '10', '删除结构', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/buildingstruct/delete', '', '0', '1', '1508894998', '1509157891', null);
INSERT INTO `menu` VALUES ('55', '10', '结构恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/buildingstruct/restore', '', '0', '1', '1508894998', '1509157891', null);
INSERT INTO `menu` VALUES ('56', '10', '结构销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/buildingstruct/destroy', '', '0', '1', '1508894998', '1509157891', null);
INSERT INTO `menu` VALUES ('57', '24', '常用民族修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/nation/edit', '', '0', '1', '1508894998', '1509184614', null);
INSERT INTO `menu` VALUES ('58', '12', '添加使用性质', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/buildinguse/add', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('59', '12', '使用性质详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/buildinguse/detail', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('60', '12', '使用性质修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/buildinguse/edit', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('61', '24', '常用民族状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/nation/status', '', '0', '1', '1508894998', '1509184673', null);
INSERT INTO `menu` VALUES ('62', '12', '使用性质状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/buildinguse/status', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('63', '12', '删除使用性质', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/buildinguse/delete', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('64', '12', '使用性质恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/buildinguse/restore', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('65', '12', '使用性质销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/buildinguse/destroy', '', '0', '1', '1508894998', '1509171843', null);
INSERT INTO `menu` VALUES ('66', '24', '常用民族删除', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/nation/delete', '', '0', '1', '1508894998', '1509184709', null);
INSERT INTO `menu` VALUES ('67', '13', '添加建筑状况', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/buildingstatus/add', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('68', '13', '建筑状况详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/buildingstatus/detail', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('69', '13', '建筑状况修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/buildingstatus/edit', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('70', '24', '常用民族恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/nation/restore', '', '0', '1', '1508894998', '1509184852', null);
INSERT INTO `menu` VALUES ('71', '13', '建筑状况状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/buildingstatus/status', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('72', '13', '删除建筑状况', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/buildingstatus/delete', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('73', '13', '建筑状况恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/buildingstatus/restore', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('74', '13', '建筑状况销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/buildingstatus/destroy', '', '0', '1', '1508894998', '1509173229', null);
INSERT INTO `menu` VALUES ('75', '14', '添加新闻分类', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/newscate/add', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('76', '14', '新闻分类详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/newscate/detail', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('77', '14', '新闻分类修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/newscate/edit', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('78', '24', '常用民族销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/nation/destroy', '', '0', '1', '1508894998', '1509184836', null);
INSERT INTO `menu` VALUES ('79', '14', '新闻分类状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/newscate/status', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('80', '14', '删除新闻分类', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/newscate/delete', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('81', '14', '新闻分类恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/newscate/restore', '', '0', '1', '1508894998', '1509176009', null);
INSERT INTO `menu` VALUES ('82', '14', '新闻分类销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/newscate/destroy', '', '0', '1', '1508894998', '1509175999', null);
INSERT INTO `menu` VALUES ('83', '15', '添加特殊人群分类', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/crowd/add', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('84', '15', '特殊人群分类详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/crowd/detail', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('85', '15', '特殊人群分类修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/crowd/edit', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('86', '15', '所有特殊人群', '3', '<img src=\"/static/system/img/navigation.png\" />', '0', '/system/crowd/all', '', '0', '1', '1508894998', '1509185332', null);
INSERT INTO `menu` VALUES ('87', '15', '特殊人群分类状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/crowd/status', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('88', '15', '删除特殊人群分类', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/crowd/delete', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('89', '15', '特殊人群分类恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/crowd/restore', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('90', '15', '特殊人群分类销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/crowd/destroy', '', '0', '1', '1508894998', '1509185223', null);
INSERT INTO `menu` VALUES ('91', '9', '常用银行管理', '2', '<img src=\"/static/system/img/email_trace.png\"/>', '0', '/system/bank/index', '', '1', '1', '1508898200', '1509185506', null);
INSERT INTO `menu` VALUES ('92', '91', '添加常用银行', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/bank/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('93', '91', '常用银行详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/bank/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('94', '91', '常用银行修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/bank/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('95', '91', '常用银行状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/bank/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('96', '91', '删除常用银行', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/bank/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('97', '91', '常用银行恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/bank/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('98', '91', '常用银行销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/bank/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('99', '9', '房屋户型', '2', '<img src=\"/static/system/img/bricks.png\"/>', '0', '/system/layout/index', '', '1', '1', '1509331742', '1509346080', null);
INSERT INTO `menu` VALUES ('100', '99', '添加房屋户型', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/layout/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('101', '99', '房屋户型详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/layout/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('102', '99', '房屋户型修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/layout/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('103', '99', '房屋户型状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/layout/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('104', '99', '删除房屋户型', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/layout/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('105', '99', '房屋户型恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/layout/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('106', '99', '房屋户型销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/layout/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('107', '0', '安置房管理', '1', '<img src=\"/static/system/img/house.png\"/>', '0', '/system/house#', '', '1', '1', '1509332337', '1509332337', null);
INSERT INTO `menu` VALUES ('108', '107', '安置房源', '2', '<img src=\"/static/system/img/books.png\"/>', '0', '/system/house/index', '', '1', '1', '1509332381', '1509332381', null);
INSERT INTO `menu` VALUES ('109', '107', '房源小区', '2', '<img src=\"/static/system/img/chart_organisation_add.png\"/>', '0', '/system/housecommunity/index', '', '1', '1', '1509332513', '1509332513', null);
INSERT INTO `menu` VALUES ('110', '107', '房源户型图', '2', '<img src=\"/static/system/img/navigation.png\"/>', '0', '/system/houselayoutpic/index', '', '1', '1', '1509332583', '1509332583', null);
INSERT INTO `menu` VALUES ('111', '109', '添加房源小区', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/housecommunity/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('112', '109', '房源小区详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/housecommunity/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('113', '109', '房源小区修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/housecommunity/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('114', '109', '房源小区状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/housecommunity/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('115', '109', '删除房源小区', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/housecommunity/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('116', '109', '房源小区恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/housecommunity/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('117', '109', '房源小区销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/housecommunity/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('118', '110', '添加户型图', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/houselayoutpic/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('119', '110', '户型图详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/houselayoutpic/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('120', '110', '户型图修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/houselayoutpic/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('121', '110', '户型图状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/houselayoutpic/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('122', '110', '删除户型图', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/houselayoutpic/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('123', '110', '户型图恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/houselayoutpic/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('124', '110', '户型图销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/houselayoutpic/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('125', '108', '添加房源', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/house/add', '', '0', '1', '1509348295', '1509348295', null);
INSERT INTO `menu` VALUES ('126', '108', '房源详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/house/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('127', '108', '房源修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/house/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('128', '108', '房源状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/house/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('129', '108', '删除房源', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/house/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('130', '108', '房源恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/house/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('131', '108', '房源销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/house/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('132', '1', '接口文档', '2', '<img src=\"/static/system/img/chart_line.png\"/>', '0', '/system/api/index', '', '1', '1', '1509416365', '1509416365', null);
INSERT INTO `menu` VALUES ('133', '132', '添加接口文档', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/api/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('134', '132', '接口文档详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/api/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('135', '132', '接口文档修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/api/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('136', '132', '接口文档排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/api/sort', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('137', '132', '接口文档状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/api/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('138', '132', '删除接口文档', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/api/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('139', '132', '接口文档恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/api/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('140', '132', '接口文档销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/api/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('141', '0', '资金管理', '1', '<img src=\"/static/system/img/small_business.png\"/>', '0', '/system/funds#', '', '1', '1', '1509421185', '1509421185', null);
INSERT INTO `menu` VALUES ('142', '0', '评估公司管理', '1', '<img src=\"/static/system/img/report_design.png\"/>', '0', '/system/company#', '', '1', '1', '1509421300', '1509421460', null);
INSERT INTO `menu` VALUES ('143', '142', '评估公司', '2', '<img src=\"/static/system/img/bricks.png\"/>', '0', '/system/company/index', '', '1', '1', '1509421512', '1509421512', null);
INSERT INTO `menu` VALUES ('144', '141', '收入管理', '2', '<img src=\"/static/system/img/add_on.png\"/>', '0', '/system/fundsin/index', '', '1', '1', '1509421612', '1509421705', null);
INSERT INTO `menu` VALUES ('145', '141', '支出管理', '2', '<img src=\"/static/system/img/on_the_shelves.png\"/>', '0', '/system/fundsout/index', '', '1', '1', '1509421662', '1509421662', null);
INSERT INTO `menu` VALUES ('146', '143', '添加评估公司', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/company/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('147', '143', '评估公司详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/company/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('148', '143', '评估公司修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/company/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('149', '143', '评估公司排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/company/sort', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('150', '143', '评估公司状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/company/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('151', '143', '删除评估公司', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/company/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('152', '143', '评估公司恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/company/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('153', '143', '评估公司销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/company/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('154', '142', '评估师', '2', '<img src=\"/static/system/img/account_balances.png\"/>', '0', '/system/companyvaluer/index', '', '1', '1', '1509436749', '1509436749', null);
INSERT INTO `menu` VALUES ('155', '154', '添加评估师', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/companyvaluer/add', '', '0', '1', '1508146326', '1509436918', null);
INSERT INTO `menu` VALUES ('156', '154', '评估师详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/companyvaluer/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('157', '154', '评估师修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/companyvaluer/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('158', '154', '评估师状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/companyvaluer/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('159', '154', '删除评估师', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/companyvaluer/delete', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('160', '154', '评估师恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/companyvaluer/restore', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('161', '154', '评估师销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/companyvaluer/destroy', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('162', '0', '项目管理', '1', '<img src=\"/static/system/img/web_disk.png\"/>', '0', '/system/item#', '', '1', '1', '1509445088', '1509445088', null);
INSERT INTO `menu` VALUES ('163', '162', '项目列表', '2', '<img src=\"/static/system/img/application_view_list.png\"/>', '0', '/system/item/index', '', '1', '1', '1509446298', '1509496760', null);
INSERT INTO `menu` VALUES ('164', '163', '添加项目', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/item/add', '', '0', '1', '1509446352', '1509496790', null);
INSERT INTO `menu` VALUES ('165', '163', '项目详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/item/detail', '', '0', '1', '1509496863', '1509496863', null);
INSERT INTO `menu` VALUES ('166', '163', '项目修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/item/edit', '', '0', '1', '1509496907', '1509496907', null);
INSERT INTO `menu` VALUES ('167', '163', '项目状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/item/status', '', '0', '1', '1509497026', '1509497026', null);
INSERT INTO `menu` VALUES ('168', '163', '项目置顶', '3', '<img src=\"/static/system/img/top.png\"/>', '0', '/system/item/istop', '', '0', '1', '1509498222', '1509531632', null);
INSERT INTO `menu` VALUES ('169', '0', '新闻公告管理', '1', '<img src=\"/static/system/img/monitor_window_3d.png\"/>', '0', '/system/news#', '', '1', '1', '1509526699', '1509526750', null);
INSERT INTO `menu` VALUES ('170', '169', '新闻公告', '2', '<img src=\"/static/system/img/books.png\"/>', '0', '/system/news/index', '', '1', '1', '1509526910', '1509526910', null);
INSERT INTO `menu` VALUES ('171', '170', '添加新闻公告', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/news/add', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('172', '170', '新闻公告详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/news/detail', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('173', '170', '新闻公告修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/news/edit', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('174', '170', '新闻公告排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/news/sort', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('175', '170', '新闻公告状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/news/status', null, '0', '1', '1508146326', '1508146326', null);
INSERT INTO `menu` VALUES ('176', '170', '新闻公告置顶', '3', '<img src=\"/static/system/img/top.png\"/>', '0', '/system/istop/status', null, '0', '1', '1508146326', '1508146326', null);

-- ----------------------------
-- Table structure for nation
-- ----------------------------
DROP TABLE IF EXISTS `nation`;
CREATE TABLE `nation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='常用民族列表';

-- ----------------------------
-- Records of nation
-- ----------------------------
INSERT INTO `nation` VALUES ('1', '汉族', '', '1', '1509184988', '1509185030', null);

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='新闻公告';

-- ----------------------------
-- Records of news
-- ----------------------------
INSERT INTO `news` VALUES ('1', '1', '征收范围的公告', '0', '1509552000', '', '', '', null, '/uploads/20171101/45a8abb8744dc6fbebc0d7b22e4e199b.jpg', '', '', '0', '0', '1', '1509531194', '1509531418');

-- ----------------------------
-- Table structure for news_cate
-- ----------------------------
DROP TABLE IF EXISTS `news_cate`;
CREATE TABLE `news_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT ' 名称',
  `infos` text COMMENT ' 描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，0禁用，1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='新闻公告分类';

-- ----------------------------
-- Records of news_cate
-- ----------------------------
INSERT INTO `news_cate` VALUES ('1', '征收范围公告', '', '1', '1509176668', '1509176668', null);

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='权限与角色';

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '0', '内置超级管理员', '1', '1', '', '[]', '1', '1509096221', '1509171145', null);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统用户';

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', '0', '1', '开发者', null, '', '', '', '', 'demo', 'e10adc3949ba59abbe56e057f20f883e', '351cee86fba9ce77563462adc31ba200', '1509510349', '127.0.0.1', '1', '1509096250', '1509171148', null);
