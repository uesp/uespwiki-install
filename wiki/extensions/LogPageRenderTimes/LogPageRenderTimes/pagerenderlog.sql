CREATE TABLE /*$wgDBprefix*/pagerenderlog (
	page_title TINYTEXT NOT NULL,
	timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	rendertimems INTEGER NOT NULL DEFAULT 0
) /*$wgDBTableOptions*/;