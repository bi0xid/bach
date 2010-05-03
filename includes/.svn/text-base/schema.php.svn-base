<?php

return array( 
'threads' => "
-- --------------------------------------------------------

--
-- Table structure for table 'support_threads'
--

CREATE TABLE IF NOT EXISTS %s (
  `thread_id` bigint(20) NOT NULL auto_increment,
  `hash` char(32) NOT NULL,
  `dt` datetime NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `messages` int(11) NOT NULL default '1',
  `state` varchar(30) NOT NULL default 'open',
  PRIMARY KEY  (`thread_id`),
  KEY `hash` (`hash`,`email`),
  KEY `dt` (`dt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

'messages' => "
-- --------------------------------------------------------


--
-- Table structure for table `support_messages`
--

CREATE TABLE IF NOT EXISTS %s (
  `message_id` bigint(20) NOT NULL auto_increment,
  `hash` char(32) NOT NULL,
  `thread_id` bigint(20) NOT NULL,
  `dt` datetime NOT NULL,
  `email` varchar(150) NOT NULL,
  `from_user_id` int(10) unsigned NOT NULL,
  `content` longtext NOT NULL,
  `email_to` varchar(150) NOT NULL,
  `message_type` varchar(30) NOT NULL default 'support',
  PRIMARY KEY  (`message_id`),
  KEY `thread_id` (`thread_id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `dt` (`dt`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

'tags' => "
-- --------------------------------------------------------

--
-- Table structure for table 'support_tags'
--

CREATE TABLE IF NOT EXISTS %s (
  thread_id bigint(20) NOT NULL,
  tag_slug varchar(100) NOT NULL,
  dt datetime NOT NULL,
  KEY thread_id (thread_id,tag_slug)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

'predefined_messages' => "
-- --------------------------------------------------------

--
-- Table structure for table 'support_predefined_messages'
--

CREATE TABLE IF NOT EXISTS %s (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `message` text NOT NULL,
  `tag` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
);

?>
