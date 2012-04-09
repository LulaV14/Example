--Sql Script for the creation of a web application 
--List App

--Create database cl_db
CREATE DATABASE `cl_db`
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci;

--Create table User Information
CREATE TABLE cl_db.users
(
	UserID		INT PRIMARY KEY AUTO_INCREMENT,
	Username	VARCHAR(150) NOT NULL,
	Password	VARCHAR(150), 
	ver_code	VARCHAR(150),
	verified	TINYINT DEFAULT 0
);

--Create table List Information
CREATE TABLE cl_db.lists
(
    ListID      INT PRIMARY KEY AUTO_INCREMENT,
    UserID      INT NOT NULL,
    ListURL     VARCHAR(150)
);

--Create Table LIst Items
CREATE TABLE cl_db.list_items
(
    ListItemID       INT PRIMARY KEY AUTO_INCREMENT,
    ListID           INT NOT NULL,
    ListText         VARCHAR(150),
    ListItemDone     INT NOT NULL,
    ListItemPosition INT NOT NULL,
    ListItemColor    INT NOT NULL
);
