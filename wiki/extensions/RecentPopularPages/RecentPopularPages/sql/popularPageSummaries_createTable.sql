CREATE TABLE /*_*/popularPageSummaries(
	pageName VARCHAR(128) CHARACTER SET UTF8 COLLATE UTF8_GENERAL_CI NOT NULL,
	pageDate DATE NOT NULL,
	pageCount INTEGER NOT NULL,
	PRIMARY KEY (pageName(128), pageDate),
	INDEX dateIndex(pageDate)
) ENGINE=MYISAM /*$wgDBTableOptions*/;
