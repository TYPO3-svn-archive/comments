# $Id$

#
# Table structure for table 'tx_comments_comments'
#
CREATE TABLE tx_comments_comments (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(1) unsigned DEFAULT '0' NOT NULL,
	approved int(1) DEFAULT '0' NOT NULL,
	external_ref varchar(255) DEFAULT '' NOT NULL,
	external_prefix varchar(255) DEFAULT '' NOT NULL,
	firstname varchar(255) DEFAULT '' NOT NULL,
	lastname varchar(255) DEFAULT '' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	homepage text NOT NULL,
	location varchar(255) DEFAULT '' NOT NULL,
	content text NOT NULL,
	remote_addr varchar(255) DEFAULT '' NOT NULL,
	double_post_check varchar(32) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
	KEY tcemainhook (external_ref(32),deleted)
);
