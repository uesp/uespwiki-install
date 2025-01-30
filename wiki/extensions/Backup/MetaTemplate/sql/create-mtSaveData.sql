-- SQL to create the mtSaveData table for MetaTemplate.

-- This is designed to be run from the install script or via update.php, ensuring that proper variable substitution is
-- done.

-- There will be one row for each variable in each set, hence the primary key being on those two columns. The setId is
-- the link back to mtSaveSet.

CREATE TABLE IF NOT EXISTS /*_*/mtSaveData (
	setId INT UNSIGNED NOT NULL,
	varName VARCHAR(50) NOT NULL,
	varValue BLOB NOT NULL,
	FOREIGN KEY (setId)
		REFERENCES /*_*/mtSaveSet (setId)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	PRIMARY KEY (setId, varName),
	INDEX (varName)
) /*$wgDBTableOptions*/;
