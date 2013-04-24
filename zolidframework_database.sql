CREATE TABLE IF NOT EXISTS `sessions` (
  `id` char(64) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `expire` int(12) NOT NULL DEFAULT '0',
  `agent` char(64) NOT NULL,
  `ip` char(64) NOT NULL,
  `host` char(64) NOT NULL,
  `acc` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(25) NOT NULL,
  `email` blob NOT NULL,
  `email_hash` char(128) NOT NULL,
  `password` char(128) NOT NULL,
  `local` char(5) NOT NULL DEFAULT 'en',
  `mail_admins` tinyint(1) NOT NULL DEFAULT '1',
  `mail_members` tinyint(1) NOT NULL,
  `reset_token` char(64) NOT NULL,
  `reset_time` int(11) NOT NULL,
  `active_key` char(32) NOT NULL,
  `session_id` char(64) NOT NULL,
  `acc_key` char(12) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email_hash` (`email_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;