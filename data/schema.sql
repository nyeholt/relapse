-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 05, 2009 at 09:57 AM
-- Server version: 5.0.45
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `crm2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `actionaccess`
-- 

CREATE TABLE `actionaccess` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(64) NOT NULL,
  `module` varchar(32) NOT NULL,
  `controller` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `role` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `attendee`
-- 

CREATE TABLE `attendee` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `eventid` int(11) NOT NULL,
  `eventuserid` int(11) NOT NULL,
  `refererid` int(11) NOT NULL,
  `remindedon` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `client`
-- 

CREATE TABLE `client` (
  `id` int(11) NOT NULL auto_increment,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `postaladdress` text,
  `billingaddress` text,
  `website` varchar(255) default NULL,
  `email` varchar(64) default NULL,
  `phone` varchar(32) default NULL,
  `fax` varchar(16) default NULL,
  `created` datetime default NULL,
  `relationship` varchar(64) default NULL,
  `deleted` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `codereview`
-- 

CREATE TABLE `codereview` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  `revision` int(11) NOT NULL,
  `previousrevision` int(11) default NULL,
  `author` varchar(128) NOT NULL,
  `projectid` int(11) NOT NULL,
  `diff` text,
  `difflog` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `contact`
-- 

CREATE TABLE `contact` (
  `id` int(11) NOT NULL auto_increment,
  `clientid` int(11) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `firstname` varchar(64) default NULL,
  `lastname` varchar(64) default NULL,
  `title` varchar(32) default NULL,
  `department` varchar(32) default NULL,
  `postaladdress` text,
  `businessaddress` text,
  `switchboard` varchar(32) default NULL,
  `directline` varchar(32) default NULL,
  `fax` varchar(32) default NULL,
  `mobile` varchar(32) default NULL,
  `email` varchar(64) default NULL,
  `altemail` varchar(64) default NULL,
  `status` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `crmuser`
-- 

CREATE TABLE `crmuser` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(64) default NULL,
  `password` varchar(64) default NULL,
  `email` varchar(64) default NULL,
  `ticket` varchar(64) default NULL,
  `contactid` int(11) NOT NULL,
  `role` varchar(64) default NULL,
  `theme` varchar(32) default NULL,
  `longdateformat` varchar(32) default NULL,
  `dateformat` varchar(32) default NULL,
  `created` datetime default NULL,
  `lastlogin` datetime default NULL,
  `leave` double NOT NULL,
  `lastleavecalculation` datetime NOT NULL,
  `startdate` datetime NOT NULL,
  `firstname` varchar(64) default NULL,
  `lastname` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `event`
-- 

CREATE TABLE `event` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `eventdate` datetime default NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `posteven` text NOT NULL,
  `postevent` text NOT NULL,
  `maxattendees` int(11) NOT NULL,
  `inviteemail` text NOT NULL,
  `lastchanceemail` text NOT NULL,
  `reminderemail` text NOT NULL,
  `invitedate` datetime default NULL,
  `lastchancedate` datetime default NULL,
  `reminderdate` datetime default NULL,
  `inviteon` datetime default NULL,
  `starttime` varchar(10) default NULL,
  `endtime` varchar(10) default NULL,
  `ispublic` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `eventuser`
-- 

CREATE TABLE `eventuser` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(64) default NULL,
  `password` varchar(64) default NULL,
  `email` varchar(64) default NULL,
  `ticket` varchar(64) default NULL,
  `contactid` int(11) NOT NULL,
  `subscribed` int(1) default '0',
  `useruid` varchar(64) NOT NULL,
  `role` varchar(64) default NULL,
  `created` datetime default NULL,
  `lastlogin` datetime default NULL,
  `leave` double NOT NULL,
  `lastleavecalculation` datetime NOT NULL,
  `firstname` varchar(64) default NULL,
  `lastname` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `expense`
-- 

CREATE TABLE `expense` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `expensereportid` int(11) NOT NULL,
  `userreportid` int(11) NOT NULL,
  `username` varchar(64) NOT NULL,
  `approver` varchar(64) NOT NULL,
  `amount` float NOT NULL,
  `description` varchar(255) NOT NULL,
  `expensedate` datetime NOT NULL,
  `paiddate` datetime default NULL,
  `clientid` int(11) NOT NULL,
  `projectid` int(11) NOT NULL,
  `location` varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  `atocategory` varchar(255) NOT NULL,
  `expensetype` varchar(255) default NULL,
  `expensecategory` varchar(255) default NULL,
  `gst` float default 10.0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `expensereport`
-- 

CREATE TABLE `expensereport` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `title` varchar(255) default NULL,
  `username` varchar(64) NOT NULL,
  `projectid` int(11) default NULL,
  `clientid` int(11) default NULL,
  `content` text,
  `from` datetime default NULL,
  `to` datetime default NULL,
  `locked` int(11) default '0',
  `paiddate` datetime default NULL,
  `total` double NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `faq`
-- 

CREATE TABLE `faq` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `faqurl` varchar(255) NOT NULL,
  `faqcontent` text NOT NULL,
  `created` datetime NOT NULL,
  `authored` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` varchar(64) NOT NULL,
  `modifiedby` varchar(64) NOT NULL,
  `nextversionid` int(11) NOT NULL,
  `originalversion` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `feature`
-- 

CREATE TABLE `feature` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `estimated` float default '0',
  `verification` text,
  `parentpath` varchar(255) default NULL,
  `projectid` int(11) default NULL,
  `priority` varchar(64) default NULL,
  `milestone` int(11) default NULL,
  `sortorder` int(11) default '0',
  `hours` float default NULL,
  `complete` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `feed`
-- 

CREATE TABLE `feed` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `url` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `file`
-- 

CREATE TABLE `file` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `path` text,
  `owner` varchar(64) default NULL,
  `isprivate` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `groupmember`
-- 

CREATE TABLE `groupmember` (
  `id` int(11) NOT NULL auto_increment,
  `groupid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `invitee`
-- 

CREATE TABLE `invitee` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `eventid` int(11) NOT NULL,
  `eventuserid` int(11) NOT NULL,
  `uid` varchar(64) NOT NULL,
  `invitedon` datetime default NULL,
  `remindedon` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `invoice`
-- 

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `title` varchar(255) default NULL,
  `projectid` int(11) default NULL,
  `timesheetid` int(11) NOT NULL,
  `amountpaid` float NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `issue`
-- 

CREATE TABLE `issue` (
  `id` int(11) NOT NULL auto_increment,
  `isprivate` int(1) NOT NULL default '0',
  `title` varchar(255) default NULL,
  `description` text,
  `clientid` int(11) default NULL,
  `projectid` int(11) default NULL,
  `severity` varchar(32) default NULL,
  `status` varchar(32) NOT NULL,
  `issuetype` varchar(32) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `userid` varchar(64) default NULL,
  `product` varchar(64) NOT NULL,
  `operatingsystem` varchar(64) NOT NULL,
  `databasetype` varchar(64) NOT NULL,
  `release` varchar(32) default NULL,
  `category` varchar(64) default NULL,
  `estimated` float NOT NULL default '0',
  `elapsed` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `itemlink`
-- 

CREATE TABLE `itemlink` (
  `id` int(11) NOT NULL auto_increment,
  `fromid` int(11) NOT NULL,
  `fromtype` varchar(32) NOT NULL,
  `toid` int(11) NOT NULL,
  `totype` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `itemwatch`
-- 

CREATE TABLE `itemwatch` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `itemid` int(11) default NULL,
  `itemtype` varchar(64) default NULL,
  `userid` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `leave`
-- 

CREATE TABLE `leave` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `username` varchar(64) NOT NULL,
  `days` float NOT NULL,
  `lastleavecalculation` datetime NOT NULL,
  `leavetype` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `leaveapplication`
-- 

CREATE TABLE `leaveapplication` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `username` varchar(64) NOT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  `approver` varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  `reason` text NOT NULL,
  `days` float NOT NULL,
  `numdays` float NOT NULL default '0',
  `leavetype` varchar(32) NOT NULL default 'Annual',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `mailout`
-- 

CREATE TABLE `mailout` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `tomail` datetime NOT NULL,
  `maildate` datetime NOT NULL,
  `html` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `note`
-- 

CREATE TABLE `note` (
  `id` int(11) NOT NULL auto_increment,
  `userid` varchar(64) default NULL,
  `created` datetime default NULL,
  `title` varchar(255) default NULL,
  `note` text,
  `attachedtotype` varchar(64) default NULL,
  `attachedtoid` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `performancereview`
-- 

CREATE TABLE `performancereview` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `modifiedby` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `username` varchar(64) NOT NULL,
  `nextversionid` int(11) NOT NULL,
  `originalversion` int(11) NOT NULL,
  `from` datetime default NULL,
  `to` datetime default NULL,
  `position` varchar(128) NOT NULL,
  `reportsto` varchar(64) NOT NULL,
  `shortgoals` text NOT NULL,
  `mediumgoals` text NOT NULL,
  `longgoals` text NOT NULL,
  `development` text NOT NULL,
  `intermediatereviews` text NOT NULL,
  `signedemployee` datetime default NULL,
  `signedmanager` datetime default NULL,
  `managercomments` text NOT NULL,
  `employeecomments` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `project`
-- 

CREATE TABLE `project` (
  `id` int(11) NOT NULL auto_increment,
  `parentid` int(11) NOT NULL default '0',
  `ownerid` int(11) default NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `enablereports` tinyint(1) NOT NULL default '0',
  `due` datetime default NULL,
  `started` datetime default NULL,
  `actualstart` datetime default NULL,
  `completed` datetime default NULL,
  `clientid` int(11) default NULL,
  `estimated` float default NULL,
  `currenttime` float NOT NULL default '0',
  `taskestimate` float default '0',
  `featureestimate` float default '0',
  `budgeted` float default '0',
  `rate` float default NULL,
  `url` varchar(255) NOT NULL,
  `deleted` int(1) default NULL,
  `manager` varchar(64) default NULL,
  `isprivate` int(11) NOT NULL default '0',
  `nextrelease` varchar(32) NOT NULL,
  `svnurl` varchar(255) NOT NULL,
  `ismilestone` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `projectstatus`
-- 

CREATE TABLE `projectstatus` (
  `id` int(11) NOT NULL auto_increment,
  `projectid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `completednotes` text NOT NULL,
  `todonotes` text NOT NULL,
  `overduetasks` text NOT NULL,
  `completetasks` text NOT NULL,
  `duetasks` text NOT NULL,
  `openissues` text NOT NULL,
  `closedissues` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `recipient`
-- 

CREATE TABLE `recipient` (
  `id` int(11) NOT NULL auto_increment,
  `mailid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `mailedon` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `tag`
-- 

CREATE TABLE `tag` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL,
  `itemid` int(11) NOT NULL,
  `itemtype` varchar(32) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `task`
-- 

CREATE TABLE `task` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `creator` varchar(64) NOT NULL,
  `projectid` int(11) default NULL,
  `dependency` varchar(255) NOT NULL,
  `title` varchar(255) default NULL,
  `userid` text,
  `description` text,
  `status` varchar(32) default NULL,
  `startdate` datetime default NULL,
  `due` datetime default NULL,
  `timespent` int(11) default NULL,
  `estimated` float default NULL,
  `complete` int(11) default NULL,
  `category` varchar(64) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `timesheet`
-- 

CREATE TABLE `timesheet` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `title` varchar(255) default NULL,
  `projectid` int(11) default NULL,
  `clientid` int(11) default NULL,
  `content` text,
  `from` datetime default NULL,
  `to` datetime default NULL,
  `locked` int(11) default '0',
  `tasktype` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `timesheetrecord`
-- 

CREATE TABLE `timesheetrecord` (
  `id` int(11) NOT NULL auto_increment,
  `taskid` int(11) NOT NULL,
  `userid` varchar(64) NOT NULL default '',
  `starttime` int(16) default NULL,
  `endtime` int(16) default NULL,
  `created` datetime default NULL,
  `timesheetid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `trackerentry`
-- 

CREATE TABLE `trackerentry` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(64) default NULL,
  `url` varchar(255) default NULL,
  `actionname` varchar(64) default NULL,
  `actionid` varchar(255) default NULL,
  `remoteip` varchar(128) default NULL,
  `created` datetime default NULL,
  `entrydata` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `usergroup`
-- 

CREATE TABLE `usergroup` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `description` text,
  `parentpath` varchar(255) default '\r\n',
  `updated` datetime default NULL,
  `created` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `userrole`
-- 

CREATE TABLE `userrole` (
  `id` int(11) NOT NULL auto_increment,
  `authority` varchar(64) NOT NULL,
  `role` int(11) NOT NULL,
  `itemtype` varchar(32) NOT NULL,
  `itemid` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `usertaskassignment`
-- 

CREATE TABLE `usertaskassignment` (
  `id` int(11) NOT NULL auto_increment,
  `userid` varchar(64) NOT NULL,
  `taskid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
