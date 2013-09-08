CREATE TABLE `runtimeError` (
  `error_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(2048) NOT NULL DEFAULT '',
  `file` varchar(1024) DEFAULT '',
  `line` int(10) unsigned DEFAULT NULL,
  `error_type` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `server_name` varchar(100) DEFAULT NULL,
  `execution_script` varchar(1024) NOT NULL DEFAULT '',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(16) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`error_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `queryError` (
  `error_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `query` text,
  `file` varchar(1024) DEFAULT '',
  `line` int(10) unsigned DEFAULT NULL,
  `error_string` varchar(1024) DEFAULT '',
  `error_no` int(10) unsigned DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `execution_script` varchar(1024) DEFAULT '',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(16) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`error_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `task` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `script_name` varchar(1024) NOT NULL DEFAULT '',
  `params` varchar(1024) DEFAULT '',
  `server_name` varchar(30) DEFAULT '',
  `server_user` varchar(30) DEFAULT '',
  `start_time` datetime DEFAULT NULL,
  `stop_time` datetime DEFAULT NULL,
  `state` enum('RUNNING','SUCCESSFUL','FAILED') DEFAULT 'RUNNING',
  `exit_status` int(10) unsigned DEFAULT NULL,
  `stdout` text,
  `stderr` text,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
