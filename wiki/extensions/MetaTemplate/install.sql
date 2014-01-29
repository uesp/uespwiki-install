-- SQL to create tables specific to MetaTemplate
-- Designed to be run from the install script or via update.php, which
-- ensure that proper variable substitution is done

-- Table providing the save_set number corresponding to a given page and/or rev_id
-- timestamp also saved to help identify out-of-date data
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/mt_save_set (
  mt_set_id int(8) unsigned NOT NULL auto_increment,
  mt_set_time timestamp NOT NULL default CURRENT_TIMESTAMP,
  mt_set_page_id int(8) unsigned default NULL,
  mt_set_subset varchar(20) default '',
  mt_set_rev_id int(8) unsigned NOT NULL,
  PRIMARY KEY mt_set_id (mt_set_id),
  INDEX (mt_set_page_id),
  INDEX (mt_set_subset),
  INDEX (mt_set_rev_id)
) /*$wgDBTableOptions*/;

-- Table that actually saves the requested data
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/mt_save_data (
mt_save_id int(8) unsigned NOT NULL,
mt_save_varname varchar(50) NOT NULL,
mt_save_value mediumblob,
mt_save_parsed boolean default TRUE,
PRIMARY KEY (mt_save_id, mt_save_varname),
INDEX varvalue (mt_save_varname(10),mt_save_value(15))
) /*$wgDBTableOptions*/;

