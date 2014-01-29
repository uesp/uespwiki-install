#
# This clears the searchlog summary and deletes all log entries older than
# one week. 
#
# Change WIKIDB for your MediaWiki's database name
#

delete from WIKIDB.searchlog where WIKIDB.searchlog.searchdate < DATE_SUB(NOW(), INTERVAL 7 DAY);

truncate table WIKIDB.searchlog_summary;
