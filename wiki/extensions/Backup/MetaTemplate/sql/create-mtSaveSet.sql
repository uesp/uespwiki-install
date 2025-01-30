
-- SQL to create the mtSaveSet table for MetaTemplate.

-- This is designed to be run from the install script or via update.php, ensuring that proper variable substitution is
-- done.

-- Typically, there will be one entry for each set of data on a page. In extremely rare instances, multiple sets will
-- be seen for the same setName/pageId as a result of contention when saving the data. Given the rarity of this, no
-- attempt is made to handle it while writing to the database. Instead, it should be handled by anything that reads the table -
-- the one with the highest revId should win. A multi-field primary key is used to cluster data for the same subsets
-- together. Clustering by subset prioritizes loading over saving, since that will occur way more often than saving.
-- Both #load and #listsaved will inherently request a specific subset, but #listsaved isn't page-specific. This will
-- benefit even more if specific set names are in use rather than the default empty string.

CREATE TABLE IF NOT EXISTS /*_*/mtSaveSet (
	setId INT UNSIGNED NOT NULL AUTO_INCREMENT,
	setName VARCHAR(50) NOT NULL DEFAULT '',
	pageId INT UNSIGNED NOT NULL,
	revId INT UNSIGNED NOT NULL,
	-- FOREIGN KEY (pageId) references page (page_id), -- Unable right now due to page being MyISAM and mtSaveSet being InnoDB
	PRIMARY KEY (setId),
	UNIQUE INDEX (pageId, setName, revId)
) /*$wgDBTableOptions*/;