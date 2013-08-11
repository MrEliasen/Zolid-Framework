CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(40) NOT NULL,
  `data` text NOT NULL,
  `expire` int(12) NOT NULL DEFAULT '0',
  `agent` char(64) NOT NULL,
  `ip` char(64) NOT NULL,
  `host` char(64) NOT NULL,
  `acc` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `key` varchar(25) NOT NULL,
  `value` varchar(100) NOT NULL,
  `title` varchar(20) NOT NULL,
  `type` enum('text','select') NOT NULL DEFAULT 'text',
  `options` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL,
  `email` blob NOT NULL,
  `email_hash` char(128) NOT NULL,
  `password` char(128) NOT NULL,
  `local` varchar(5) NOT NULL,
  `group` int(10) unsigned NOT NULL,
  `reset_token` char(64) NOT NULL,
  `reset_time` int(11) NOT NULL,
  `active_key` char(32) NOT NULL,
  `session_id` char(64) NOT NULL,
  `acc_key` char(12) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email_hash` (`email_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `groups` (`id`, `title`, `permissions`) VALUES
(1, 'Member', ''),
(2, 'Admin', '{"admin":1}');