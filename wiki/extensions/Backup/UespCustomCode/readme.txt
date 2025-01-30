Disabling/Enabling the extension
To disable this entire extension, simply remove its entry from LocalSettings.php

Many individual features can be disabled by commenting out individual lines of code below.  This
file contains all of the initialization functions that enable the various extension components,
and therefore is the best place to enable/disable extension features.

However, several parts of the code overlap, so some individual features cannot be individually disabled
What can or cannot be disabled:

Special:Wantedpages and Special:Lonelypages
- each can be individually disabled by commenting out relevant line in efSiteSpecialPageInit

Search features
- The special search page can be disabled by
- (a) commenting out relevant line in efSiteSpecialPageInit, and
- (b) undoing the change in Wiki.php (not necessary for MW1.14+)
- The search engine customizations can be disabled by commenting out the wgSearchType definition
- There are multiple interactions between the search page and the search engine, so they need to either
  both be enabled or both be disabled.  Some MW1.10/MW1.14 ugliness requires even more crosslinking
  than should really be necessary... once the site's updated to MW1.14, this code could be modified
  to provide better separation of features.