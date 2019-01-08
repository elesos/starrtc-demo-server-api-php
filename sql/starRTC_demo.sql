-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2019-01-08 05:55:05
-- 服务器版本: 5.1.73
-- PHP 版本: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `starRTC_demo`
--

-- --------------------------------------------------------

--
-- 表的结构 `audio_lists`
--

CREATE TABLE IF NOT EXISTS `audio_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `channelId` varchar(50) DEFAULT NULL,
  `roomId` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `userId` varchar(100) NOT NULL,
  `liveState` tinyint(3) unsigned DEFAULT '0',
  `os_type` varchar(10) DEFAULT NULL,
  `ip_addr` varchar(50) DEFAULT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`),
  KEY `roomId` (`roomId`),
  KEY `channelId` (`channelId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `channels`
--

CREATE TABLE IF NOT EXISTS `channels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channelId` varchar(100) NOT NULL,
  `channelType` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'LOGIN_PUBLIC等',
  `liveType` tinyint(3) unsigned NOT NULL COMMENT '1会议2直播',
  `ownerId` varchar(100) NOT NULL COMMENT '群或聊天室',
  `ownerType` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1群2room',
  `conNum` smallint(5) unsigned NOT NULL DEFAULT '200',
  `liveState` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `userId` varchar(100) NOT NULL COMMENT '上传者',
  `extra` varchar(1024) NOT NULL,
  `specify` varchar(50) NOT NULL,
  `lastOnlineTime` int(10) unsigned DEFAULT '0',
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channelId` (`channelId`),
  KEY `ownerId` (`ownerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3692 ;

-- --------------------------------------------------------

--
-- 表的结构 `chatRoom`
--

CREATE TABLE IF NOT EXISTS `chatRoom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roomId` varchar(100) NOT NULL,
  `userId` varchar(200) NOT NULL,
  `roomName` varchar(200) NOT NULL,
  `roomType` tinyint(3) unsigned NOT NULL COMMENT '1public与2login',
  `liveType` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1会议2直播',
  `maxNum` mediumint(8) unsigned NOT NULL DEFAULT '200',
  `state` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `lastOnlineTime` int(10) unsigned DEFAULT '0',
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roomId` (`roomId`),
  KEY `userId` (`userId`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2895 ;

-- --------------------------------------------------------

--
-- 表的结构 `class_lists`
--

CREATE TABLE IF NOT EXISTS `class_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `roomId` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `userId` varchar(100) NOT NULL,
  `os_type` varchar(10) DEFAULT NULL,
  `ip_addr` varchar(50) DEFAULT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `roomId` (`roomId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=386 ;

-- --------------------------------------------------------

--
-- 表的结构 `groupId`
--

CREATE TABLE IF NOT EXISTS `groupId` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10395 ;

--
-- 转存表中的数据 `groupId`
--

INSERT INTO `groupId` (`id`, `state`) VALUES
(10394, 100);

-- --------------------------------------------------------

--
-- 表的结构 `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `groupName` varchar(1024) DEFAULT NULL,
  `userList` longtext,
  `needAuth` tinyint(3) unsigned DEFAULT '1',
  `hasLive` tinyint(3) unsigned DEFAULT '0',
  `state` tinyint(3) unsigned DEFAULT '1',
  `creator` varchar(100) NOT NULL,
  `numLimit` mediumint(8) unsigned DEFAULT '2000',
  `curNum` mediumint(8) unsigned DEFAULT NULL,
  `ctime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupId` (`groupId`),
  KEY `creator` (`creator`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100321 ;

-- --------------------------------------------------------

--
-- 表的结构 `im_chatroom_lists`
--

CREATE TABLE IF NOT EXISTS `im_chatroom_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roomId` varchar(200) NOT NULL,
  `name` varchar(100) NOT NULL,
  `userId` varchar(100) NOT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roomId` (`roomId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1737 ;

-- --------------------------------------------------------

--
-- 表的结构 `live_lists`
--

CREATE TABLE IF NOT EXISTS `live_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `channelId` varchar(50) DEFAULT NULL,
  `roomId` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `userId` varchar(100) NOT NULL,
  `liveState` tinyint(3) unsigned DEFAULT '0',
  `os_type` varchar(10) DEFAULT NULL,
  `ip_addr` varchar(50) DEFAULT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`),
  KEY `roomId` (`roomId`),
  KEY `channelId` (`channelId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2914 ;

-- --------------------------------------------------------

--
-- 表的结构 `meeting_lists`
--

CREATE TABLE IF NOT EXISTS `meeting_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `roomId` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `userId` varchar(100) NOT NULL,
  `os_type` varchar(10) DEFAULT NULL,
  `ip_addr` varchar(50) DEFAULT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `roomId` (`roomId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2202 ;

-- --------------------------------------------------------

--
-- 表的结构 `userGroup`
--

CREATE TABLE IF NOT EXISTS `userGroup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(100) NOT NULL,
  `groupList` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=280 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
