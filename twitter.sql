/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 100422
 Source Host           : localhost:3306
 Source Schema         : twitter

 Target Server Type    : MySQL
 Target Server Version : 100422
 File Encoding         : 65001

 Date: 30/10/2022 18:00:22
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for following
-- ----------------------------
DROP TABLE IF EXISTS `following`;
CREATE TABLE `following`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user_a` int NOT NULL,
  `id_user_b` int NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_following_id_user_a`(`id_user_a`) USING BTREE,
  INDEX `fk_following_id_user_b`(`id_user_b`) USING BTREE,
  CONSTRAINT `fk_following_id_user_a` FOREIGN KEY (`id_user_a`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_following_id_user_b` FOREIGN KEY (`id_user_b`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of following
-- ----------------------------
INSERT INTO `following` VALUES (11, 5, 3, '2022-10-30 15:29:29');
INSERT INTO `following` VALUES (24, 3, 4, '2022-10-30 17:04:31');
INSERT INTO `following` VALUES (25, 4, 5, '2022-10-30 17:04:56');
INSERT INTO `following` VALUES (26, 3, 5, '2022-10-30 17:24:10');
INSERT INTO `following` VALUES (27, 4, 3, '2022-10-30 17:48:55');
INSERT INTO `following` VALUES (28, 3, 6, '2022-10-30 17:53:36');

-- ----------------------------
-- Table structure for tweets
-- ----------------------------
DROP TABLE IF EXISTS `tweets`;
CREATE TABLE `tweets`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp,
  `state` enum('ACTIVE','INACTIVE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_users_id_user`(`id_user`) USING BTREE,
  CONSTRAINT `fk_users_id_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tweets
-- ----------------------------
INSERT INTO `tweets` VALUES (1, 3, '123', '2022-10-30 14:16:13', 'ACTIVE');
INSERT INTO `tweets` VALUES (2, 4, 'A test Tweet to be shown in other users', '2022-10-30 16:09:26', 'ACTIVE');
INSERT INTO `tweets` VALUES (3, 3, 'Hello World, this is just another test', '2022-10-30 17:48:02', 'ACTIVE');
INSERT INTO `tweets` VALUES (4, 3, 'A\'', '2022-10-30 17:48:09', 'ACTIVE');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp,
  `state` enum('ACTIVE','INACTIVE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (3, 'Brandon Zambrano', 'bazv', 'soloungamer@yahoo.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '', '2022-10-29 16:57:34', 'ACTIVE');
INSERT INTO `users` VALUES (4, 'Brenda Vélez', 'bnzv', 'nicole00zambrano@gmail.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '', '2022-10-29 18:41:40', 'ACTIVE');
INSERT INTO `users` VALUES (5, 'test', 'test', 'test', 'ee26b0dd4af7e749aa1a8ee3c10ae9923f618980772e473f8819a5d4940e0db27ac185f8a0e1d5f84f88bc887fd67b143732c304cc5fa9ad8e6f57f50028a8ff', '', '2022-10-30 13:49:41', 'ACTIVE');
INSERT INTO `users` VALUES (6, 'test2', 'test2', 'test2', '6d201beeefb589b08ef0672dac82353d0cbd9ad99e1642c83a1601f3d647bcca003257b5e8f31bdc1d73fbec84fb085c79d6e2677b7ff927e823a54e789140d9', '', '2022-10-30 17:53:29', 'ACTIVE');

-- ----------------------------
-- Table structure for users_images
-- ----------------------------
DROP TABLE IF EXISTS `users_images`;
CREATE TABLE `users_images`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `ext` enum('jpg','png','jpeg') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uuid_internal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uuid_external` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_users_images_id_user`(`id_user`) USING BTREE,
  CONSTRAINT `fk_users_images_id_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_images
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
