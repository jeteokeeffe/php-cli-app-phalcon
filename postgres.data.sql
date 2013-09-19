CREATE TABLE runtimeError (
	error_id SERIAL,
	title varchar(2048) NOT NULL DEFAULT '', 
	file varchar(1024) DEFAULT '', 
	line integer DEFAULT NULL,
	error_type integer NOT NULL DEFAULT '0',
	create_time timestamp DEFAULT NULL,
	server_name varchar(100) DEFAULT NULL,
	execution_script varchar(1024) NOT NULL DEFAULT '', 
	pid integer NOT NULL DEFAULT '0',
	ip_address varchar(16) DEFAULT NULL,
	user_id integer DEFAULT NULL
) ;


CREATE TABLE queryError (
  error_id SERIAL,
  query text,
  file varchar(1024) DEFAULT '',
  line integer  DEFAULT NULL,
  error_string varchar(1024) DEFAULT '',
  error_no integer  DEFAULT NULL,
  create_time TIMESTAMP DEFAULT NULL,
  execution_script varchar(1024) DEFAULT '',
  pid integer  NOT NULL DEFAULT '0',
  ip_address varchar(16) DEFAULT NULL,
  user_id integer  DEFAULT NULL
) ;

CREATE TYPE app_state AS ENUM('RUNNING', 'SUCCESSFUL', 'FAILED');
CREATE TABLE task (
  task_id SERIAL,
  script_name varchar(1024) NOT NULL DEFAULT '',
  params varchar(1024) DEFAULT '',
  server_name varchar(30) DEFAULT '',
  server_user varchar(30) DEFAULT '',
  start_time TIMESTAMP DEFAULT NULL,
  stop_time TIMESTAMP DEFAULT NULL,
  state app_state,
  exit_status integer  DEFAULT NULL,
  stdout text,
  stderr text,
  pid integer  NOT NULL DEFAULT '0'
) ;
