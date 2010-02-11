-- phpMyAdmin SQL Dump
-- version 3.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 11, 2010 at 03:06 PM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: 'relapsetesting'
--

-- --------------------------------------------------------

--
-- Table structure for table 'actionaccess'
--

CREATE TABLE actionaccess (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL,
  module varchar(32) NOT NULL,
  controller varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  role varchar(32) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'attendee'
--

CREATE TABLE attendee (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  eventid int(11) NOT NULL,
  eventuserid int(11) NOT NULL,
  refererid int(11) NOT NULL,
  remindedon datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'client'
--

CREATE TABLE `client` (
  id int(11) NOT NULL AUTO_INCREMENT,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  modifier varchar(64) NOT NULL,
  title varchar(255) DEFAULT NULL,
  description text,
  postaladdress text,
  billingaddress text,
  website varchar(255) DEFAULT NULL,
  email varchar(64) DEFAULT NULL,
  phone varchar(32) DEFAULT NULL,
  fax varchar(16) DEFAULT NULL,
  created datetime DEFAULT NULL,
  relationship varchar(64) DEFAULT NULL,
  deleted int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'clientversion'
--

CREATE TABLE clientversion (
  id int(11) NOT NULL AUTO_INCREMENT,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  modifier varchar(64) NOT NULL,
  recordid int(11) NOT NULL,
  validfrom datetime NOT NULL,
  label varchar(32) NOT NULL,
  title varchar(255) DEFAULT NULL,
  description text,
  postaladdress text,
  billingaddress text,
  website varchar(255) DEFAULT NULL,
  email varchar(64) DEFAULT NULL,
  phone varchar(32) DEFAULT NULL,
  fax varchar(16) DEFAULT NULL,
  created datetime DEFAULT NULL,
  relationship varchar(64) DEFAULT NULL,
  deleted int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'codereview'
--

CREATE TABLE codereview (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(128) NOT NULL,
  description text NOT NULL,
  updated datetime NOT NULL,
  created datetime NOT NULL,
  revision int(11) NOT NULL,
  previousrevision int(11) DEFAULT NULL,
  author varchar(128) NOT NULL,
  projectid int(11) NOT NULL,
  diff text,
  difflog text,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'contact'
--

CREATE TABLE contact (
  id int(11) NOT NULL AUTO_INCREMENT,
  clientid int(11) DEFAULT NULL,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  firstname varchar(64) DEFAULT NULL,
  lastname varchar(64) DEFAULT NULL,
  title varchar(32) DEFAULT NULL,
  department varchar(32) DEFAULT NULL,
  postaladdress text,
  businessaddress text,
  switchboard varchar(32) DEFAULT NULL,
  directline varchar(32) DEFAULT NULL,
  fax varchar(32) DEFAULT NULL,
  mobile varchar(32) DEFAULT NULL,
  email varchar(64) DEFAULT NULL,
  altemail varchar(64) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'crmuser'
--

CREATE TABLE crmuser (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(64) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  salt varchar(64) NOT NULL,
  email varchar(64) DEFAULT NULL,
  ticket varchar(64) DEFAULT NULL,
  contactid int(11) NOT NULL,
  role varchar(64) DEFAULT NULL,
  theme varchar(32) DEFAULT NULL,
  longdateformat varchar(32) DEFAULT NULL,
  dateformat varchar(32) DEFAULT NULL,
  created datetime DEFAULT NULL,
  lastlogin datetime DEFAULT NULL,
  `leave` double NOT NULL,
  lastleavecalculation datetime NOT NULL,
  startdate datetime NOT NULL,
  firstname varchar(64) DEFAULT NULL,
  lastname varchar(64) DEFAULT NULL,
  defaultmodule varchar(32) NOT NULL DEFAULT 'default',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'event'
--

CREATE TABLE `event` (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  title varchar(255) NOT NULL,
  eventdate datetime DEFAULT NULL,
  location varchar(255) NOT NULL,
  description text NOT NULL,
  posteven text NOT NULL,
  postevent text NOT NULL,
  maxattendees int(11) NOT NULL,
  inviteemail text NOT NULL,
  lastchanceemail text NOT NULL,
  reminderemail text NOT NULL,
  invitedate datetime DEFAULT NULL,
  lastchancedate datetime DEFAULT NULL,
  reminderdate datetime DEFAULT NULL,
  inviteon datetime DEFAULT NULL,
  starttime varchar(10) DEFAULT NULL,
  endtime varchar(10) DEFAULT NULL,
  ispublic int(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'eventuser'
--

CREATE TABLE eventuser (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(64) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  email varchar(64) DEFAULT NULL,
  ticket varchar(64) DEFAULT NULL,
  contactid int(11) NOT NULL,
  subscribed int(1) DEFAULT '0',
  useruid varchar(64) NOT NULL,
  role varchar(64) DEFAULT NULL,
  created datetime DEFAULT NULL,
  lastlogin datetime DEFAULT NULL,
  `leave` double NOT NULL,
  lastleavecalculation datetime NOT NULL,
  firstname varchar(64) DEFAULT NULL,
  lastname varchar(64) DEFAULT NULL,
  theme varchar(32) DEFAULT NULL,
  dateformat varchar(32) DEFAULT NULL,
  longdateformat varchar(32) DEFAULT NULL,
  defaultmodule varchar(32) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'expense'
--

CREATE TABLE expense (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  expensereportid int(11) NOT NULL,
  userreportid int(11) NOT NULL,
  username varchar(64) NOT NULL,
  approver varchar(64) NOT NULL,
  amount float NOT NULL,
  description varchar(255) NOT NULL,
  expensedate datetime NOT NULL,
  paiddate datetime DEFAULT NULL,
  clientid int(11) NOT NULL,
  projectid int(11) NOT NULL,
  location varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  atocategory varchar(255) NOT NULL,
  expensetype varchar(255) NOT NULL DEFAULT 'Cash',
  expensecategory varchar(255) NOT NULL DEFAULT 'Other',
  gst float NOT NULL DEFAULT '10',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'expensereport'
--

CREATE TABLE expensereport (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  username varchar(64) NOT NULL,
  projectid int(11) DEFAULT NULL,
  clientid int(11) DEFAULT NULL,
  content text,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  locked int(11) DEFAULT '0',
  paiddate datetime DEFAULT NULL,
  total double NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'faq'
--

CREATE TABLE faq (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(64) NOT NULL,
  description varchar(255) NOT NULL,
  faqurl varchar(255) NOT NULL,
  faqcontent text NOT NULL,
  created datetime NOT NULL,
  authored datetime NOT NULL,
  updated datetime NOT NULL,
  author varchar(64) NOT NULL,
  modifiedby varchar(64) NOT NULL,
  nextversionid int(11) NOT NULL,
  originalversion int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'feature'
--

CREATE TABLE feature (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  modifier varchar(64) NOT NULL,
  title varchar(255) DEFAULT NULL,
  description text,
  implementation text,
  estimated float DEFAULT '0',
  verification text,
  parentpath varchar(255) DEFAULT NULL,
  projectid int(11) DEFAULT NULL,
  priority varchar(64) DEFAULT NULL,
  milestone int(11) DEFAULT NULL,
  sortorder int(11) DEFAULT '0',
  hours float DEFAULT NULL,
  complete int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'featureversion'
--

CREATE TABLE featureversion (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  modifier varchar(64) NOT NULL,
  recordid int(11) NOT NULL,
  validfrom datetime NOT NULL,
  label varchar(32) NOT NULL,
  title varchar(255) DEFAULT NULL,
  description text,
  implementation text,
  estimated float DEFAULT '0',
  verification text,
  parentpath varchar(255) DEFAULT NULL,
  projectid int(11) DEFAULT NULL,
  priority varchar(64) DEFAULT NULL,
  milestone int(11) DEFAULT NULL,
  sortorder int(11) DEFAULT '0',
  hours float DEFAULT NULL,
  complete int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'feed'
--

CREATE TABLE feed (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  url varchar(255) NOT NULL,
  content text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'file'
--

CREATE TABLE `file` (
  id int(11) NOT NULL AUTO_INCREMENT,
  filename varchar(255) NOT NULL,
  title varchar(255) DEFAULT NULL,
  description text,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  path text,
  `owner` varchar(64) DEFAULT NULL,
  isprivate int(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'groupmember'
--

CREATE TABLE groupmember (
  id int(11) NOT NULL AUTO_INCREMENT,
  groupid int(11) NOT NULL,
  userid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'invitee'
--

CREATE TABLE invitee (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  eventid int(11) NOT NULL,
  eventuserid int(11) NOT NULL,
  uid varchar(64) NOT NULL,
  invitedon datetime DEFAULT NULL,
  remindedon datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'invoice'
--

CREATE TABLE invoice (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  projectid int(11) DEFAULT NULL,
  timesheetid int(11) NOT NULL,
  amountpaid float NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue'
--

CREATE TABLE issue (
  id int(11) NOT NULL AUTO_INCREMENT,
  isprivate int(1) NOT NULL DEFAULT '0',
  title varchar(255) DEFAULT NULL,
  description text,
  clientid int(11) DEFAULT NULL,
  projectid int(11) DEFAULT NULL,
  severity varchar(32) DEFAULT NULL,
  `status` varchar(32) NOT NULL,
  issuetype varchar(32) DEFAULT NULL,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  userid varchar(64) DEFAULT NULL,
  product varchar(64) NOT NULL,
  operatingsystem varchar(64) NOT NULL,
  databasetype varchar(64) NOT NULL,
  `release` varchar(32) DEFAULT NULL,
  category varchar(64) DEFAULT NULL,
  estimated float NOT NULL DEFAULT '0',
  elapsed float NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'itemlink'
--

CREATE TABLE itemlink (
  id int(11) NOT NULL AUTO_INCREMENT,
  fromid int(11) NOT NULL,
  fromtype varchar(32) NOT NULL,
  toid int(11) NOT NULL,
  totype varchar(32) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'itemwatch'
--

CREATE TABLE itemwatch (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  itemid int(11) DEFAULT NULL,
  itemtype varchar(64) DEFAULT NULL,
  userid varchar(64) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'leave'
--

CREATE TABLE `leave` (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  username varchar(64) NOT NULL,
  days float NOT NULL,
  lastleavecalculation datetime NOT NULL,
  leavetype varchar(16) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'leaveapplication'
--

CREATE TABLE leaveapplication (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  username varchar(64) NOT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  approver varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  reason text NOT NULL,
  days float NOT NULL,
  numdays float NOT NULL DEFAULT '0',
  leavetype varchar(32) NOT NULL DEFAULT 'Annual',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'mailout'
--

CREATE TABLE mailout (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  tomail datetime DEFAULT NULL,
  maildate datetime DEFAULT NULL,
  html text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'note'
--

CREATE TABLE note (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid varchar(64) DEFAULT NULL,
  created datetime DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  note text,
  attachedtotype varchar(64) DEFAULT NULL,
  attachedtoid int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'objectversion'
--

CREATE TABLE objectversion (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  creator varchar(64) NOT NULL,
  modifier varchar(64) NOT NULL,
  validfrom datetime NOT NULL,
  objectid int(11) NOT NULL,
  objecttype varchar(32) NOT NULL,
  item text NOT NULL,
  label varchar(32) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'performancereview'
--

CREATE TABLE performancereview (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  modifiedby varchar(64) NOT NULL,
  title varchar(255) NOT NULL,
  username varchar(64) NOT NULL,
  nextversionid int(11) NOT NULL,
  originalversion int(11) NOT NULL,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  position varchar(128) NOT NULL,
  reportsto varchar(64) NOT NULL,
  shortgoals text NOT NULL,
  mediumgoals text NOT NULL,
  longgoals text NOT NULL,
  development text NOT NULL,
  intermediatereviews text NOT NULL,
  signedemployee datetime DEFAULT NULL,
  signedmanager datetime DEFAULT NULL,
  managercomments text NOT NULL,
  employeecomments text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project'
--

CREATE TABLE project (
  id int(11) NOT NULL AUTO_INCREMENT,
  parentid int(11) NOT NULL DEFAULT '0',
  ownerid int(11) DEFAULT NULL,
  created datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  updated datetime DEFAULT NULL,
  modifier varchar(64) NOT NULL,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  enablereports tinyint(1) NOT NULL DEFAULT '0',
  due datetime DEFAULT NULL,
  started datetime DEFAULT NULL,
  actualstart datetime DEFAULT NULL,
  completed datetime DEFAULT NULL,
  startfgp datetime NOT NULL,
  durationfgp int(4) NOT NULL,
  paiddate datetime DEFAULT NULL,
  clientid int(11) DEFAULT NULL,
  estimated float DEFAULT NULL,
  currenttime float NOT NULL DEFAULT '0',
  taskestimate float DEFAULT '0',
  featureestimate float DEFAULT '0',
  budgeted float DEFAULT '0',
  rate float DEFAULT NULL,
  url varchar(255) NOT NULL,
  deleted int(1) DEFAULT NULL,
  manager varchar(64) DEFAULT NULL,
  isprivate int(11) NOT NULL DEFAULT '0',
  nextrelease varchar(32) NOT NULL,
  svnurl varchar(255) NOT NULL,
  ismilestone int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'projectstatus'
--

CREATE TABLE projectstatus (
  id int(11) NOT NULL AUTO_INCREMENT,
  projectid int(11) NOT NULL,
  title varchar(255) NOT NULL,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  completednotes text NOT NULL,
  todonotes text NOT NULL,
  milestone int(11) DEFAULT NULL,
  startdate datetime DEFAULT NULL,
  enddate datetime DEFAULT NULL,
  `snapshot` text,
  dategenerated datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'projectversion'
--

CREATE TABLE projectversion (
  id int(11) NOT NULL AUTO_INCREMENT,
  parentid int(11) NOT NULL DEFAULT '0',
  ownerid int(11) DEFAULT NULL,
  created datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  updated datetime DEFAULT NULL,
  modifier varchar(64) NOT NULL,
  recordid int(11) NOT NULL,
  validfrom datetime NOT NULL,
  label varchar(32) NOT NULL,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  enablereports tinyint(1) NOT NULL DEFAULT '0',
  due datetime DEFAULT NULL,
  started datetime DEFAULT NULL,
  actualstart datetime DEFAULT NULL,
  completed datetime DEFAULT NULL,
  startfgp datetime NOT NULL,
  durationfgp int(4) NOT NULL,
  paiddate datetime DEFAULT NULL,
  clientid int(11) DEFAULT NULL,
  estimated float DEFAULT NULL,
  currenttime float NOT NULL DEFAULT '0',
  taskestimate float DEFAULT '0',
  featureestimate float DEFAULT '0',
  budgeted float DEFAULT '0',
  rate float DEFAULT NULL,
  url varchar(255) NOT NULL,
  deleted int(1) DEFAULT NULL,
  manager varchar(64) DEFAULT NULL,
  isprivate int(11) NOT NULL DEFAULT '0',
  nextrelease varchar(32) NOT NULL,
  svnurl varchar(255) NOT NULL,
  ismilestone int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'recipient'
--

CREATE TABLE recipient (
  id int(11) NOT NULL AUTO_INCREMENT,
  mailid int(11) NOT NULL,
  userid int(11) NOT NULL,
  uid varchar(255) NOT NULL,
  mailedon datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'tag'
--

CREATE TABLE tag (
  id int(11) NOT NULL AUTO_INCREMENT,
  tag varchar(255) NOT NULL,
  itemid int(11) NOT NULL,
  itemtype varchar(32) NOT NULL,
  uid int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY tag (tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'task'
--

CREATE TABLE task (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  creator varchar(64) NOT NULL,
  projectid int(11) DEFAULT NULL,
  dependency varchar(255) NOT NULL,
  title varchar(255) DEFAULT NULL,
  userid text,
  description text,
  `status` varchar(32) DEFAULT NULL,
  startdate datetime DEFAULT NULL,
  due datetime DEFAULT NULL,
  timespent int(11) DEFAULT NULL,
  estimated float DEFAULT NULL,
  complete int(11) DEFAULT NULL,
  category varchar(64) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'timesheet'
--

CREATE TABLE timesheet (
  id int(11) NOT NULL AUTO_INCREMENT,
  created datetime DEFAULT NULL,
  updated datetime DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  projectid int(11) DEFAULT NULL,
  clientid int(11) DEFAULT NULL,
  content text,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  locked int(11) DEFAULT '0',
  tasktype text,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'timesheetrecord'
--

CREATE TABLE timesheetrecord (
  id int(11) NOT NULL AUTO_INCREMENT,
  taskid int(11) NOT NULL,
  userid varchar(64) NOT NULL DEFAULT '',
  starttime int(16) DEFAULT NULL,
  endtime int(16) DEFAULT NULL,
  created datetime DEFAULT NULL,
  timesheetid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'trackerentry'
--

CREATE TABLE trackerentry (
  id int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(64) DEFAULT NULL,
  url varchar(255) DEFAULT NULL,
  actionname varchar(64) DEFAULT NULL,
  actionid varchar(255) DEFAULT NULL,
  remoteip varchar(128) DEFAULT NULL,
  created datetime DEFAULT NULL,
  entrydata text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'usergroup'
--

CREATE TABLE usergroup (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(64) NOT NULL,
  description text,
  parentpath varchar(255) DEFAULT '\r\n',
  updated datetime DEFAULT NULL,
  created datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'userrole'
--

CREATE TABLE userrole (
  id int(11) NOT NULL AUTO_INCREMENT,
  authority varchar(64) NOT NULL,
  role int(11) NOT NULL,
  itemtype varchar(32) NOT NULL,
  itemid int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'usertaskassignment'
--

CREATE TABLE usertaskassignment (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid varchar(64) NOT NULL,
  taskid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
