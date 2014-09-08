/*
Parkar.sql (part of parkar.de)
Rushy Panchal
*/

CREATE DATABASE IF NOT EXISTS `$DATABASE_NAME`;

CREATE USER `$DATABASE_USER`@`localhost` IDENTIFIED BY '$DATABASE_USER_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON `$DATABASE_NAME`.* TO `$DATABASE_USER`@'localhost';

CREATE TABLE `users` (
	`id` 		INT 			PRIMARY KEY			AUTO_INCREMENT,
	`username`	VARCHAR(30)		UNIQUE			NOT NULL,
	`email`		VARCHAR(50)		DEFAULT	''		NOT NULL,
	`password`	VARCHAR(128)	NOT NULL,
	`salt`		VARCHAR(128)	NOT NULL,
	`ip_addr`	CHAR(19)		DEFAULT	'0.0.0.0'	NOT NULL,
	`created`	TIMESTAMP 		DEFAULT	CURRENT_TIMESTAMP
	);

CREATE TABLE `user_sessions` (
	`user_id`	INT(11)		NOT NULL,
	`ip_addr`	CHAR(19)		DEFAULT	'0.0.0.0'		NOT NULL	UNIQUE,
	`cookie`	VARCHAR(255)	DEFAULT	'parkar'		NOT NULL,
	`created`	TIMESTAMP 		DEFAULT	CURRENT_TIMESTAMP,
	`expire`	TIMESTAMP 		NOT NULL
	);

CREATE TRIGGER `session_expire` BEFORE INSERT ON `user_sessions` 
	FOR EACH ROW SET new.expire = CURRENT_TIMESTAMP + INTERVAL 1 MONTH;

CREATE TABLE `login_attempts` (
	`user_id`	INT(11)		NOT NULL,
	`ip_addr` 	CHAR(19)		DEFAULT	'0.0.0.0'		NOT NULL,
	`time` 		TIMESTAMP 		DEFAULT	CURRENT_TIMESTAMP
	);

CREATE TRIGGER `purge_attempts` AFTER INSERT ON `login_attempts` 
	FOR EACH ROW DELETE FROM `login_attempts` WHERE `user_id` = new.user_id AND `time` < (NOW() - INTERVAL 1 HOUR)

CREATE TABLE `http_log` (
	`ip_addr`	CHAR(19)		DEFAULT	'0.0.0.0'	NOT NULL,
	`request`	VARCHAR(255)	DEFAULT	''		NOT NULL,
	`type`		VARCHAR(6)		DEFAULT	'GET'		NOT NULL,
	`time`		TIMESTAMP 		DEFAULT	CURRENT_TIMESTAMP
	);
