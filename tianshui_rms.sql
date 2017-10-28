/*
Navicat MySQL Data Transfer

Source Server         : localhost_phpwamp
Source Server Version : 50554
Source Host           : 127.0.0.1:3306
Source Database       : tianshui_rms

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2017-10-28 10:14:23
*/

CREATE DATABASE IF NOT EXISTS `tianshui_rms` DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

USE `tianshui_rms`;

SET FOREIGN_KEY_CHECKS=0;

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
INSERT INTO `dept` VALUES ('1', '0', '管理层', '0', '1', '', '1', '1509152121', '1509156110', null);

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
INSERT INTO `menu` VALUES ('1', '0', '系统设置', '1', '<img src=\"/static/system/img/setting_tools.png\"/>', '0', '/system/setting#', '', '1', '1', '1507862534', '1508407873', null);
INSERT INTO `menu` VALUES ('2', '1', '功能与菜单', '2', '<img src=\"/static/system/img/monitor_window_3d.png\"/>', '1', '/system/menu/index', '', '1', '1', '1507865210', '1509155756', null);
INSERT INTO `menu` VALUES ('3', '1', '权限与角色', '2', '<img src=\"/static/system/img/role.png\"/>', '3', '/system/role/index', '', '1', '1', '1507865414', '1509155756', null);
INSERT INTO `menu` VALUES ('4', '1', '系统用户', '2', '<img src=\"/static/system/img/folder_user.png\"/>', '4', '/system/user/index', '', '1', '1', '1507866165', '1509155756', null);
INSERT INTO `menu` VALUES ('5', '2', '添加菜单', '3', '<img src=\"/static/system/img/add.png\"/>', '0', '/system/menu/add', '', '0', '1', '1507872000', '1507897493', null);
INSERT INTO `menu` VALUES ('6', '2', '菜单详情', '3', '<img src=\"/static/system/img/page_white_paste.png\"/>', '0', '/system/menu/detail', '', '0', '1', '1507880446', '1507880446', null);
INSERT INTO `menu` VALUES ('7', '2', '菜单修改', '3', '<img src=\"/static/system/img/richtext_editor.png\"/>', '0', '/system/menu/edit', '', '0', '1', '1507880485', '1507896298', null);
INSERT INTO `menu` VALUES ('8', '1', '组织与部门', '2', '<img src=\"/static/system/img/group_gear.png\"/>', '2', '/system/dept/index', '', '1', '1', '1507880673', '1509155756', null);
INSERT INTO `menu` VALUES ('9', '0', '基础资料', '1', '<img src=\"/static/system/img/widgets.png\"/>', '0', '/system/bases#', '', '1', '1', '1508145488', '1509151733', null);
INSERT INTO `menu` VALUES ('10', '8', '新闻分类', '2', '<img src=\"/static/system/img/server_database.png\"/>', '0', '/system/newscate/index', '', '1', '1', '1508145595', '1509097071', '1509097071');
INSERT INTO `menu` VALUES ('11', '1', '个人中心', '2', '<img src=\"/static/system/img/report_user.png\"/>', '5', '/system/user/info', '', '0', '1', '1508145659', '1509155858', null);
INSERT INTO `menu` VALUES ('12', '8', '导航项目', '2', '<img src=\"/static/system/img/insert_element.png\"/>', '0', '/system/item/index', '', '1', '1', '1508145720', '1509097071', '1509097071');
INSERT INTO `menu` VALUES ('13', '8', '新闻管理', '2', '<img src=\"/static/system/img/add_on.png\"/>', '0', '/system/news/index', '', '1', '1', '1508146326', '1509097071', '1509097071');
INSERT INTO `menu` VALUES ('14', '8', '轮播图', '2', '<img src=\"/static/system/img/sharepoint.png\"/>', '0', '/system/bannerpic/index', '', '1', '1', '1508146527', '1509097071', '1509097071');
INSERT INTO `menu` VALUES ('15', '8', '首页项目', '2', '<img src=\"/static/system/img/outlook_new_meeting.png\"/>', '0', '/system/itemindex/index', '', '1', '1', '1508146593', '1509097071', '1509097071');
INSERT INTO `menu` VALUES ('16', '1', '修改用户密码', '2', '<img src=\"/static/system/img/page_code.png\"/>', '6', '/system/user/password', '', '0', '1', '1508146679', '1509155858', null);
INSERT INTO `menu` VALUES ('17', '2', '菜单排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\"/>', '0', '/system/menu/sort', '', '0', '1', '1508146897', '1508146897', null);
INSERT INTO `menu` VALUES ('18', '2', '菜单显示状态', '3', '<img src=\"/static/system/img/monitor_window_3d.png\"/>', '0', '/system/menu/show', '', '0', '1', '1508146986', '1508146986', null);
INSERT INTO `menu` VALUES ('19', '2', '菜单使用状态', '3', '<img src=\"/static/system/img/checked.png\"/>', '0', '/system/menu/status', '', '0', '1', '1508147023', '1508147023', null);
INSERT INTO `menu` VALUES ('20', '2', '删除菜单', '3', '<img src=\"/static/system/img/broom.png\"/>', '0', '/system/menu/delete', '', '0', '1', '1508147061', '1508147061', null);
INSERT INTO `menu` VALUES ('21', '2', '菜单恢复', '3', '<img src=\"/static/system/img/recycle.png\"/>', '0', '/system/menu/restore', '', '0', '1', '1508147092', '1508147092', null);
INSERT INTO `menu` VALUES ('22', '2', '菜单销毁', '3', '<img src=\"/static/system/img/destroy.png\"/>', '0', '/system/menu/destroy', '', '0', '1', '1508147134', '1508147140', null);
INSERT INTO `menu` VALUES ('23', '2', '所有菜单', '3', '<img src=\"/static/system/img/navigation.png\"/>', '0', '/system/menu/all', '', '0', '1', '1508147228', '1508147228', null);
INSERT INTO `menu` VALUES ('24', '8', '应用设置', '2', '<img src=\"/static/system/img/cog.png\"/>', '0', '/system/setting/index', '', '1', '1', '1508147838', '1509097071', '1509097071');
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
INSERT INTO `menu` VALUES ('43', '9', '导航排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/navtop/sort', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('44', '8', '部门状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/dept/status', '', '0', '1', '1508894998', '1509156031', null);
INSERT INTO `menu` VALUES ('45', '8', '删除部门', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/dept/delete', '', '0', '1', '1508894998', '1509156041', null);
INSERT INTO `menu` VALUES ('46', '8', '部门恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/dept/restore', '', '0', '1', '1508894998', '1509156050', null);
INSERT INTO `menu` VALUES ('47', '8', '部门销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/dept/destroy', '', '0', '1', '1508894998', '1509156058', null);
INSERT INTO `menu` VALUES ('48', '8', '所有部门', '3', '<img src=\"/static/system/img/navigation.png\" />', '0', '/system/dept/all', '', '0', '1', '1508894998', '1509156069', null);
INSERT INTO `menu` VALUES ('49', '10', '添加分类', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/newscate/add', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('50', '10', '分类详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/newscate/detail', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('51', '10', '分类修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/newscate/edit', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('52', '10', '分类排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/newscate/sort', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('53', '10', '分类状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/newscate/status', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('54', '10', '删除分类', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/newscate/delete', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('55', '10', '分类恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/newscate/restore', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('56', '10', '分类销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/newscate/destroy', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('57', '10', '所有分类', '3', '<img src=\"/static/system/img/navigation.png\" />', '0', '/system/newscate/all', null, '0', '1', '1508894998', '1509097020', '1509097020');
INSERT INTO `menu` VALUES ('58', '12', '添加项目', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/item/add', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('59', '12', '项目详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/item/detail', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('60', '12', '项目修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/item/edit', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('61', '12', '项目排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/item/sort', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('62', '12', '项目状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/item/status', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('63', '12', '删除项目', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/item/delete', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('64', '12', '项目恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/item/restore', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('65', '12', '项目销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/item/destroy', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('66', '12', '所有项目', '3', '<img src=\"/static/system/img/navigation.png\" />', '0', '/system/item/all', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('67', '13', '添加新闻', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/news/add', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('68', '13', '新闻详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/news/detail', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('69', '13', '新闻修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/news/edit', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('70', '13', '新闻排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/news/sort', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('71', '13', '新闻状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/news/status', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('72', '13', '删除新闻', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/news/delete', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('73', '13', '新闻恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/news/restore', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('74', '13', '新闻销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/news/destroy', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('75', '14', '添加轮播图', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/bannerpic/add', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('76', '14', '轮播图详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/bannerpic/detail', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('77', '14', '轮播图修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/bannerpic/edit', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('78', '14', '轮播图排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/bannerpic/sort', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('79', '14', '轮播图状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/bannerpic/status', null, '0', '1', '1508894998', '1509097021', '1509097021');
INSERT INTO `menu` VALUES ('80', '14', '删除轮播图', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/bannerpic/delete', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('81', '14', '轮播图恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/bannerpic/restore', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('82', '14', '轮播图销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/bannerpic/destroy', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('83', '15', '添加首页项目', '3', '<img src=\"/static/system/img/add.png\" />', '0', '/system/itemindex/add', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('84', '15', '首页项目详情', '3', '<img src=\"/static/system/img/page_white_paste.png\" />', '0', '/system/itemindex/detail', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('85', '15', '首页项目修改', '3', '<img src=\"/static/system/img/richtext_editor.png\" />', '0', '/system/itemindex/edit', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('86', '15', '首页项目排序', '3', '<img src=\"/static/system/img/text_list_numbers.png\" />', '0', '/system/itemindex/sort', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('87', '15', '首页项目状态', '3', '<img src=\"/static/system/img/checked.png\" />', '0', '/system/itemindex/status', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('88', '15', '删除首页项目', '3', '<img src=\"/static/system/img/broom.png\" />', '0', '/system/itemindex/delete', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('89', '15', '首页项目恢复', '3', '<img src=\"/static/system/img/recycle.png\" />', '0', '/system/itemindex/restore', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('90', '15', '首页项目销毁', '3', '<img src=\"/static/system/img/destroy.png\" />', '0', '/system/itemindex/destroy', null, '0', '1', '1508894998', '1509097022', '1509097022');
INSERT INTO `menu` VALUES ('91', '24', '数据设置', '3', '<img src=\"/static/system/img/cog.png\"/>', '0', '/system/setting/datasetting', '', '0', '1', '1508898200', '1509097022', '1509097022');

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
INSERT INTO `role` VALUES ('1', '0', '内置超级管理员', '1', '1', '', '[]', '1', '1509096221', '1509096221', null);

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
INSERT INTO `user` VALUES ('1', '0', '1', '开发者', null, '', '', '', '', 'demo', 'e10adc3949ba59abbe56e057f20f883e', '351cee86fba9ce77563462adc31ba200', '1509155539', '127.0.0.1', '1', '1509096250', '1509155247', null);
