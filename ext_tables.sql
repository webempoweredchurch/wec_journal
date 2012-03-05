#
# Table structure for table 'tx_wecjournal_content'
#
CREATE TABLE tx_wecjournal_content (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	user_id int(11) DEFAULT '0' NOT NULL,
	content text,
	topic tinytext,
	subtopic tinytext,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);