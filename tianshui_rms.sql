/*
Navicat MySQL Data Transfer

Source Server         : localhost_phpwamp
Source Server Version : 50554
Source Host           : 127.0.0.1:3306
Source Database       : tianshui_rms

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2017-10-28 18:21:57
*/

CREATE DATABASE IF NOT EXISTS `tianshui_rms` DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

USE `tianshui_rms`;

SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8 COMMENT='功能与菜单';

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
INSERT INTO `menu` VALUES ('14', '9', '新闻公告分类', '2', '<img src=\"/static/system/img/sharepoint.png\"/>', '0', '/system/newscate/index', '', '1', '1', '1508146527', '1509175847', null);
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
INSERT INTO `user` VALUES ('1', '0', '1', '开发者', null, '', '', '', '', 'demo', 'e10adc3949ba59abbe56e057f20f883e', '351cee86fba9ce77563462adc31ba200', '1509155539', '127.0.0.1', '1', '1509096250', '1509171148', null);
