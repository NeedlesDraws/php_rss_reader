CREATE TABLE `kd036`.`rss_table` (
`server_no` INT NOT NULL ,
`entry_no` INT NOT NULL ,
`user_name` VARCHAR( 255 ) NOT NULL ,
`url` TEXT,
`date_time` DATETIME,
`title` TEXT,
`description` TEXT,
PRIMARY KEY ( `server_no` , `entry_no` , `user_name` )
) ENGINE = MYISAM ;
