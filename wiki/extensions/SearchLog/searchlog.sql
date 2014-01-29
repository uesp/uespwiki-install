DROP TABLE IF EXISTS `searchlog`;
DROP TABLE IF EXISTS `searchlog_summary`;

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;

CREATE TABLE `searchlog` (
  `term` varchar(100) NOT NULL,
  `titlecount` int(11) NOT NULL,
  `textcount` int(11) NOT NULL,
  `searchdate` datetime default NULL,
  `searchtime` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `searchlog_summary` (
  `term` varchar(100) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY  (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET character_set_client = @saved_cs_client;

