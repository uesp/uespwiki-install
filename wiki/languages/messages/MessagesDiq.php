<?php
/** Zazaki (Zazaki)
 *
 * To improve a translation please visit https://translatewiki.net
 *
 * @ingroup Language
 * @file
 *
 */

$namespaceNames = array(
	NS_MEDIA            => 'Medya',
	NS_SPECIAL          => 'Bağse',
	NS_TALK             => 'Vaten',
	NS_USER             => 'Karber',
	NS_USER_TALK        => 'Karber_vaten',
	NS_PROJECT_TALK     => '$1_vaten',
	NS_FILE             => 'Dosya',
	NS_FILE_TALK        => 'Dosya_vaten',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'MediaWiki_vaten',
	NS_TEMPLATE         => 'Şablon',
	NS_TEMPLATE_TALK    => 'Şablon_vaten',
	NS_HELP             => 'Desteg',
	NS_HELP_TALK        => 'Desteg_vaten',
	NS_CATEGORY         => 'Kategori',
	NS_CATEGORY_TALK    => 'Kategori_vaten',
);

$namespaceAliases = array(
	'Xısusi'               => NS_SPECIAL,
	'Werênayış'            => NS_TALK,
	'Mesac'                => NS_TALK,
	'Karber_werênayış'     => NS_USER_TALK,
	'Karber_mesac'         => NS_USER_TALK,
	'$1_werênayış'         => NS_PROJECT_TALK,
	'$1_mesac'             => NS_PROJECT_TALK,
	'Dosya_werênayış'      => NS_FILE_TALK,
	'Dosya_mesac'          => NS_FILE_TALK,
	'MediaWiki_werênayış'  => NS_MEDIAWIKI_TALK,
	'MediaWiki_mesac'      => NS_MEDIAWIKI_TALK,
	'Şablon_werênayış'     => NS_TEMPLATE_TALK,
	'Şablon_mesac'         => NS_TEMPLATE_TALK,
	'Desteg'               => NS_HELP,
	'Desteg_werênayış'     => NS_HELP_TALK,
	'Peşti'                => NS_HELP,
	'Peşti_mesac'          => NS_HELP_TALK,
	'Peşti_werênayış'      => NS_HELP_TALK,
	'Kategori'             => NS_CATEGORY,
	'Kategori_werênayış'   => NS_CATEGORY_TALK,
	'Kategoriye'           => NS_CATEGORY,
	'Kategoriye_mesac'     => NS_CATEGORY_TALK,
	'Kategoriye_werênayış' => NS_CATEGORY_TALK,
);

$specialPageAliases = array(
	'Activeusers'               => array( 'KarberéAktivi' ),
	'Allmessages'               => array( 'MesaciPéro' ),
	'AllMyUploads'              => array( 'DosyeyMı' ),
	'Allpages'                  => array( 'PeriPéro' ),
	'Ancientpages'              => array( 'PeréKehani' ),
	'Badtitle'                  => array( 'SernameyoXirab' ),
	'Blankpage'                 => array( 'PeréVengi' ),
	'Block'                     => array( 'Bloke', 'BlokeIP', 'BlokeyéKarberi' ),
	'Booksources'               => array( 'ÇımeyéKıtabi' ),
	'BrokenRedirects'           => array( 'HetenayışoXırab' ),
	'Categories'                => array( 'Kategoriy' ),
	'ChangeEmail'               => array( 'EpostaVurnayış' ),
	'ChangePassword'            => array( 'ParolaBıvırn', 'ParolaResetk' ),
	'ComparePages'              => array( 'PeraAteberd' ),
	'Confirmemail'              => array( 'EpostaAraştk' ),
	'Contributions'             => array( 'Dekerdışi' ),
	'CreateAccount'             => array( 'HesabVıraz' ),
	'Deadendpages'              => array( 'PeréMerdey' ),
	'DeletedContributions'      => array( 'DekerdışékBesterneyayé' ),
	'DoubleRedirects'           => array( 'HetenayışoDilet' ),
	'EditWatchlist'             => array( 'VırnayışanéListeyaTemaşek' ),
	'Emailuser'                 => array( 'EpostayaKarberi' ),
	'ExpandTemplates'           => array( 'ŞablonaHerake' ),
	'Export'                    => array( 'Ateberd' ),
	'Fewestrevisions'           => array( 'TewrtaynRewizyoni' ),
	'FileDuplicateSearch'       => array( 'KopyadosyaCigérayış', 'DiletdosyaCigérayış' ),
	'Filepath'                  => array( 'RayaDosya', 'CayDosya' ),
	'Import'                    => array( 'Azerek' ),
	'Invalidateemail'           => array( 'EpostayaBetale' ),
	'BlockList'                 => array( 'ListeyaBloki', 'ListeyaBlokan', 'ListeyaBlokeyéIPi' ),
	'LinkSearch'                => array( 'GireCıgeyrayış' ),
	'Listadmins'                => array( 'ListeyaHeténkaran' ),
	'Listbots'                  => array( 'ListeyaBotan' ),
	'Listfiles'                 => array( 'ListeyDosyayan', 'DosyayaListek', 'ListeyResiman' ),
	'Listgrouprights'           => array( 'ListeyaHeqanéGruban', 'HeqéGrubéKarberan' ),
	'Listredirects'             => array( 'ListeyaArézekerdışan' ),
	'Listusers'                 => array( 'ListeyaKarberan', 'KarberaListek' ),
	'Lockdb'                    => array( 'DBKilitk' ),
	'Log'                       => array( 'Qeyd', 'Qeydi' ),
	'Lonelypages'               => array( 'PeréBéwayıri' ),
	'Longpages'                 => array( 'PeréDergi' ),
	'MergeHistory'              => array( 'VerénanPétewrke' ),
	'MIMEsearch'                => array( 'NIMECıgeyrayış' ),
	'Mostcategories'            => array( 'TewrvéşiKategoriyıni' ),
	'Mostimages'                => array( 'DosyeyékeCırévéşiGreDeyayo' ),
	'Mostinterwikis'            => array( 'TewrvéşiTeberwiki' ),
	'Mostlinked'                => array( 'PerékeCırévéşiGreDeyayo' ),
	'Mostlinkedcategories'      => array( 'KategoriyayékeCırévéşiGreDeyayo' ),
	'Mostlinkedtemplates'       => array( 'ŞablonékeCırévéşiGreDeyayo' ),
	'Mostrevisions'             => array( 'TewrvéşiRevizyon' ),
	'Movepage'                  => array( 'PelerBeré' ),
	'Mycontributions'           => array( 'DekerdenéMe' ),
	'MyLanguage'                => array( 'ZıwaneMe' ),
	'Mypage'                    => array( 'PeréMe' ),
	'Mytalk'                    => array( 'VatenayışéMe' ),
	'Myuploads'                 => array( 'BarkerdışéMe' ),
	'Newimages'                 => array( 'DosyeyéNewey', 'ResiméNewey' ),
	'Newpages'                  => array( 'PeréNewey' ),
	'PasswordReset'             => array( 'ParolaResetkerdış' ),
	'PermanentLink'             => array( 'GreyoDaimi' ),
	'Popularpages'              => array( 'PeréPopuleri' ),
	'Preferences'               => array( 'Tercihi' ),
	'Prefixindex'               => array( 'SerVerole' ),
	'Protectedpages'            => array( 'PerékeStaryayé' ),
	'Protectedtitles'           => array( 'SernameyékeStaryayé' ),
	'Randompage'                => array( 'Raştameye', 'PelayakeRaştamé' ),
	'RandomInCategory'          => array( 'KategoriyaXoseri' ),
	'Randomredirect'            => array( 'HetenayışoRaştameye' ),
	'Recentchanges'             => array( 'VırnayışéPeyéni' ),
	'Recentchangeslinked'       => array( 'GreyéVırnayışéPeyénan' ),
	'Redirect'                  => array( 'Hetenayış' ),
	'Revisiondelete'            => array( 'RewizyoniBesterne' ),
	'Search'                    => array( 'Cıgeyre' ),
	'Shortpages'                => array( 'PeleyéKılmi' ),
	'Specialpages'              => array( 'PeréBexsey' ),
	'Statistics'                => array( 'İstatistiki' ),
	'Tags'                      => array( 'Etiketi' ),
	'Unblock'                   => array( 'BloqiWedarne' ),
	'Uncategorizedcategories'   => array( 'KategoriyayékeKategoriyanébiyé' ),
	'Uncategorizedimages'       => array( 'DosyeyékeKategoriyanébiyé' ),
	'Uncategorizedpages'        => array( 'PeleyékeKategoriyanébiyé' ),
	'Uncategorizedtemplates'    => array( 'ŞablonékeKategoriyanébiyé' ),
	'Undelete'                  => array( 'Peyserbiya' ),
	'Unlockdb'                  => array( 'DBKılitiAk' ),
	'Unusedcategories'          => array( 'KategoriyayékeNékariyayé' ),
	'Unusedimages'              => array( 'DosyeyékeNékariyayé' ),
	'Unusedtemplates'           => array( 'ŞablonékeNékariyayé' ),
	'Unwatchedpages'            => array( 'PeleyékeNéweyneyéné' ),
	'Upload'                    => array( 'Barkerdış' ),
	'UploadStash'               => array( 'BarkerdışéNımıtey' ),
	'Userlogin'                 => array( 'KarberCıkewtış' ),
	'Userlogout'                => array( 'KarberVıcyayış' ),
	'Userrights'                => array( 'HeqéKarberan', 'SysopKerdış', 'BotKerdış' ),
	'Version'                   => array( 'Versiyon' ),
	'Wantedcategories'          => array( 'KategoriyayékeWazéné' ),
	'Wantedfiles'               => array( 'DosyeyékeWazéné' ),
	'Wantedpages'               => array( 'PerékeWazéné' ),
	'Wantedtemplates'           => array( 'ŞablonékeWazéné' ),
	'Watchlist'                 => array( 'ListeySeyran' ),
	'Whatlinkshere'             => array( 'PerarêGre' ),
	'Withoutinterwiki'          => array( 'Béİnterwiki' ),
);

$magicWords = array(
	'redirect'                  => array( '0', '#HETENAYIŞ', '#REDIRECT' ),
	'notoc'                     => array( '0', '__ESTENÇINO__', '__NOTOC__' ),
	'nogallery'                 => array( '0', '__GALERİÇINO__', '__NOGALLERY__' ),
	'forcetoc'                  => array( '0', '__ESTENZARURET__', '__FORCETOC__' ),
	'toc'                       => array( '0', '__ESTEN__', '__TOC__' ),
	'noeditsection'             => array( '0', '__TİMARKERDIŞÇINO__', '__NOEDITSECTION__' ),
	'currentmonth'              => array( '1', 'AŞMİYANEWKİ', 'MEWCUDAŞMİ2', 'CURRENTMONTH', 'CURRENTMONTH2' ),
	'currentmonth1'             => array( '1', 'AŞMİYANEWKİ1', 'CURRENTMONTH1' ),
	'currentmonthname'          => array( '1', 'NAMEYAŞMDANEWKİ', 'CURRENTMONTHNAME' ),
	'currentmonthnamegen'       => array( '1', 'AŞMACIYANEWKİ', 'CURRENTMONTHNAMEGEN' ),
	'currentmonthabbrev'        => array( '1', 'AŞMİYANEWKİKILMKERDIŞ', 'CURRENTMONTHABBREV' ),
	'currentday'                => array( '1', 'ROCENEWKİ', 'CURRENTDAY' ),
	'currentday2'               => array( '1', 'ROCENEWKİ2', 'CURRENTDAY2' ),
	'currentdayname'            => array( '1', 'NAMEYÊROCENEWKİ', 'CURRENTDAYNAME' ),
	'currentyear'               => array( '1', 'SERRENEWKİ', 'CURRENTYEAR' ),
	'currenttime'               => array( '1', 'DEMENEWKİ', 'CURRENTTIME' ),
	'currenthour'               => array( '1', 'SEHATNEWKİ', 'CURRENTHOUR' ),
	'localmonth'                => array( '1', 'WAREYAŞMİ', 'WAREYAŞMİ2', 'LOCALMONTH', 'LOCALMONTH2' ),
	'localmonth1'               => array( '1', 'WAREYAŞMİ1', 'LOCALMONTH1' ),
	'localmonthname'            => array( '1', 'NAMEYÊWAREYAŞMİ', 'LOCALMONTHNAME' ),
	'localmonthnamegen'         => array( '1', 'NAMEYWAREDÊAŞMİDACI', 'LOCALMONTHNAMEGEN' ),
	'localmonthabbrev'          => array( '1', 'WAREYAŞMİKILMKERDIŞ', 'LOCALMONTHABBREV' ),
	'localday'                  => array( '1', 'WAREYROCE', 'LOCALDAY' ),
	'localday2'                 => array( '1', 'WAREYROCE2', 'LOCALDAY2' ),
	'localdayname'              => array( '1', 'NAMEYÊWAREYROCE', 'LOCALDAYNAME' ),
	'localyear'                 => array( '1', 'WAREYSERRE', 'LOCALYEAR' ),
	'localtime'                 => array( '1', 'WAREYDEME', 'LOCALTIME' ),
	'localhour'                 => array( '1', 'WAREYSEHAT', 'LOCALHOUR' ),
	'numberofpages'             => array( '1', 'AMARİYAPELAN', 'NUMBEROFPAGES' ),
	'numberofarticles'          => array( '1', 'AMARİYAWESİQAN', 'NUMBEROFARTICLES' ),
	'numberoffiles'             => array( '1', 'AMARİYADOSYAYAN', 'NUMBEROFFILES' ),
	'numberofusers'             => array( '1', 'AMARİYAKARBERAN', 'NUMBEROFUSERS' ),
	'numberofactiveusers'       => array( '1', 'AMARİYAAKTİVKARBERAN', 'NUMBEROFACTIVEUSERS' ),
	'numberofedits'             => array( '1', 'AMARİYAVURNAYIŞAN', 'NUMBEROFEDITS' ),
	'numberofviews'             => array( '1', 'AMARİYAMOCNAYIŞAN', 'NUMBEROFVIEWS' ),
	'pagename'                  => array( '1', 'NAMEYPELA', 'PAGENAME' ),
	'pagenamee'                 => array( '1', 'NAMEYPELAA', 'PAGENAMEE' ),
	'namespace'                 => array( '1', 'CANAME', 'NAMESPACE' ),
	'namespacee'                => array( '1', 'CANAMEE', 'NAMESPACEE' ),
	'namespacenumber'           => array( '1', 'AMARİYACANAME', 'NAMESPACENUMBER' ),
	'talkspace'                 => array( '1', 'CAYÊWERÊNAYIŞİ', 'TALKSPACE' ),
	'talkspacee'                => array( '1', 'CAYÊWERÊNAYIŞAN', 'TALKSPACEE' ),
	'subjectspace'              => array( '1', 'CAYÊMESEL', 'CAYÊWESİQE', 'SUBJECTSPACE', 'ARTICLESPACE' ),
	'subjectspacee'             => array( '1', 'CAYÊMESELAN', 'CAYÊWESİQAN', 'SUBJECTSPACEE', 'ARTICLESPACEE' ),
	'fullpagename'              => array( '1', 'NAMEYPELAPÊRO', 'FULLPAGENAME' ),
	'fullpagenamee'             => array( '1', 'NAMEYPELAPÊRON', 'FULLPAGENAMEE' ),
	'subpagename'               => array( '1', 'NAMEYBINPELA', 'SUBPAGENAME' ),
	'subpagenamee'              => array( '1', 'NAMEYBINPELAA', 'SUBPAGENAMEE' ),
	'basepagename'              => array( '1', 'NAMEYSERPELA', 'BASEPAGENAME' ),
	'basepagenamee'             => array( '1', 'NAMEYSERPELAA', 'BASEPAGENAMEE' ),
	'talkpagename'              => array( '1', 'NAMEYPELAWERÊNAYIŞ', 'TALKPAGENAME' ),
	'talkpagenamee'             => array( '1', 'NAMEYPELAWERÊNAYIŞAN', 'TALKPAGENAMEE' ),
	'subjectpagename'           => array( '1', 'NAMEYPELAMESEL', 'NAMEYPELAWESİQE', 'SUBJECTPAGENAME', 'ARTICLEPAGENAME' ),
	'subjectpagenamee'          => array( '1', 'NAMEYPELAMESELER', 'NAMEYPELAQESİQER', 'SUBJECTPAGENAMEE', 'ARTICLEPAGENAMEE' ),
	'msg'                       => array( '0', 'MSC', 'MSG:' ),
	'subst'                     => array( '0', 'KOPYAKE', 'ATEBERDE', 'SUBST:' ),
	'safesubst'                 => array( '0', 'EMELEYATEBERDE', 'SAFESUBST:' ),
	'msgnw'                     => array( '0', 'MSJNW:', 'MSGNW:' ),
	'img_thumbnail'             => array( '1', 'resmoqıckek', 'qıckek', 'thumbnail', 'thumb' ),
	'img_manualthumb'           => array( '1', 'resmoqıckek=$1', 'qıckek=$1', 'thumbnail=$1', 'thumb=$1' ),
	'img_right'                 => array( '1', 'raşt', 'right' ),
	'img_left'                  => array( '1', 'çep', 'left' ),
	'img_none'                  => array( '1', 'çıniyo', 'none' ),
	'img_width'                 => array( '1', '$1pik', '$1piksel', '$1px' ),
	'img_center'                => array( '1', 'werte', 'miyan', 'center', 'centre' ),
	'img_framed'                => array( '1', 'çerçeweya', 'çerçeweniyo', 'çerçewe', 'framed', 'enframed', 'frame' ),
	'img_frameless'             => array( '1', 'bêçerçewe', 'frameless' ),
	'img_page'                  => array( '1', 'pela=$1', 'pela_$1', 'page=$1', 'page $1' ),
	'img_upright'               => array( '1', 'disleg', 'disleg=$1', 'disleg_$1', 'upright', 'upright=$1', 'upright $1' ),
	'img_border'                => array( '1', 'sinor', 'border' ),
	'img_baseline'              => array( '1', 'Sinorêerdi', 'baseline' ),
	'img_sub'                   => array( '1', 'bın', 'sub' ),
	'img_super'                 => array( '1', 'corên', 'cor', 'super', 'sup' ),
	'img_top'                   => array( '1', 'gedug', 'top' ),
	'img_text_top'              => array( '1', 'gedug-metin', 'text-top' ),
	'img_middle'                => array( '1', 'merkez', 'middle' ),
	'img_bottom'                => array( '1', 'erd', 'bottom' ),
	'img_text_bottom'           => array( '1', 'erd-metin', 'text-bottom' ),
	'img_link'                  => array( '1', 'gre=$1', 'link=$1' ),
	'int'                       => array( '0', 'İNT:', 'INT:' ),
	'sitename'                  => array( '1', 'NAMEYSİTA', 'SITENAME' ),
	'ns'                        => array( '0', 'CN', 'NS:' ),
	'nse'                       => array( '0', 'CNV', 'NSE:' ),
	'localurl'                  => array( '0', 'LOKALGRE', 'LOCALURL:' ),
	'localurle'                 => array( '0', 'LOKALGREV', 'LOCALURLE:' ),
	'articlepath'               => array( '0', 'SOPAWESİQAN', 'ARTICLEPATH' ),
	'pageid'                    => array( '0', 'NIMREYPELA', 'PAGEID' ),
	'server'                    => array( '0', 'ARDEN', 'SERVER' ),
	'servername'                => array( '0', 'NAMEYARDEN', 'SERVERNAME' ),
	'scriptpath'                => array( '0', 'RAYASCRIPTİ', 'SCRIPTPATH' ),
	'stylepath'                 => array( '0', 'TERZÊTEWRİ', 'STYLEPATH' ),
	'grammar'                   => array( '0', 'GRAMER:', 'GRAMMAR:' ),
	'gender'                    => array( '0', 'CİNSİYET:', 'GENDER:' ),
	'notitleconvert'            => array( '0', '__SERNAMEVURNAYIŞÇINO__', '__SVÇ__', '__NOTITLECONVERT__', '__NOTC__' ),
	'nocontentconvert'          => array( '0', '__ZERREVURNAYIŞÇINO__', '__ZVÇ__', '__NOCONTENTCONVERT__', '__NOCC__' ),
	'currentweek'               => array( '1', 'MEVCUDHEFTE', 'CURRENTWEEK' ),
	'currentdow'                => array( '1', 'MEVCUDWAREYHEFTİ', 'CURRENTDOW' ),
	'localweek'                 => array( '1', 'WAREYHEFTİ', 'LOCALWEEK' ),
	'localdow'                  => array( '1', 'WAREYROCAHEFTİ', 'LOCALDOW' ),
	'revisionid'                => array( '1', 'NIMREYREVİZYONİ', 'REVISIONID' ),
	'revisionday'               => array( '1', 'ROCAREVİZYONİ', 'REVISIONDAY' ),
	'revisionday2'              => array( '1', 'ROCAREVİZYON1', 'REVISIONDAY2' ),
	'revisionmonth'             => array( '1', 'AŞMAREVİZYONİ', 'REVISIONMONTH' ),
	'revisionmonth1'            => array( '1', 'AŞMAREVİZYONİ1', 'REVISIONMONTH1' ),
	'revisionyear'              => array( '1', 'SERRAREVİZYONİ', 'REVISIONYEAR' ),
	'revisiontimestamp'         => array( '1', 'MELUMATÊREVİZYONÊDEMİ', 'REVISIONTIMESTAMP' ),
	'revisionuser'              => array( '1', 'REVİZYONKARBER', 'REVISIONUSER' ),
	'plural'                    => array( '0', 'ZAFEN:', 'PLURAL:' ),
	'fullurl'                   => array( '0', 'GREPÊRO:', 'FULLURL:' ),
	'fullurle'                  => array( '0', 'GREYOPÊRON:', 'FULLURLE:' ),
	'canonicalurl'              => array( '0', 'GREYÊKANONİK:', 'CANONICALURL:' ),
	'canonicalurle'             => array( '0', 'GREYOKANONİK:', 'CANONICALURLE:' ),
	'lcfirst'                   => array( '0', 'KHİLK:', 'LCFIRST:' ),
	'ucfirst'                   => array( '0', 'BHİLK:', 'UCFIRST:' ),
	'lc'                        => array( '0', 'KH:', 'LC:' ),
	'uc'                        => array( '0', 'BH:', 'UC:' ),
	'raw'                       => array( '0', 'XAM:', 'RAW:' ),
	'displaytitle'              => array( '1', 'SERNAMİBIMOCNE', 'DISPLAYTITLE' ),
	'newsectionlink'            => array( '1', '__GREYÊSERNAMEDÊNEWİ__', '__NEWSECTIONLINK__' ),
	'nonewsectionlink'          => array( '1', '__GREYÊSERNAMEDÊNEWİÇINO__', '__NONEWSECTIONLINK__' ),
	'currentversion'            => array( '1', 'VERSİYONÊNEWKİ', 'CURRENTVERSION' ),
	'currenttimestamp'          => array( '1', 'WAREYSEHATÊNEWKİ', 'CURRENTTIMESTAMP' ),
	'localtimestamp'            => array( '1', 'MALUMATÊWAREYSEHAT', 'LOCALTIMESTAMP' ),
	'directionmark'             => array( '1', 'HETANIŞANKERDIŞ', 'HETNIŞAN', 'DIRECTIONMARK', 'DIRMARK' ),
	'language'                  => array( '0', '#ZIWAN', '#LANGUAGE:' ),
	'contentlanguage'           => array( '1', 'ZIWANÊESTİN', 'ZIWESTEN', 'CONTENTLANGUAGE', 'CONTENTLANG' ),
	'pagesinnamespace'          => array( '1', 'PELEYÊKECADÊNAMİDEYÊ', 'PELECN', 'PAGESINNAMESPACE:', 'PAGESINNS:' ),
	'numberofadmins'            => array( '1', 'AMARİYAXİZMETKARAN', 'NUMBEROFADMINS' ),
	'formatnum'                 => array( '0', 'BABETNAYIŞ', 'FORMATNUM' ),
	'padleft'                   => array( '0', 'ÇEPİPIRKE', 'PADLEFT' ),
	'padright'                  => array( '0', 'RAŞTİPIRKE', 'PADRIGHT' ),
	'special'                   => array( '0', 'xısusi', 'special' ),
	'speciale'                  => array( '0', 'xısusiye', 'speciale' ),
	'defaultsort'               => array( '1', 'RATNAYIŞOHESBNAYIŞ', 'SIRMEYRATNAYIŞOHESBNAYIŞ', 'KATEGORİYARATNAYIŞOHESBNAYIŞ', 'DEFAULTSORT:', 'DEFAULTSORTKEY:', 'DEFAULTCATEGORYSORT:' ),
	'filepath'                  => array( '0', 'RAYADOSYA:', 'FILEPATH:' ),
	'tag'                       => array( '0', 'etiket', 'tag' ),
	'hiddencat'                 => array( '1', '__KATEGORİYANIMITİ__', '__HIDDENCAT__' ),
	'pagesincategory'           => array( '1', 'PELEYÊKEKATEGORİDEYÊ', 'KATDÊPELEY', 'PAGESINCATEGORY', 'PAGESINCAT' ),
	'pagesize'                  => array( '1', 'EBATÊPELA', 'PAGESIZE' ),
	'index'                     => array( '1', '__SERSIQ__', '__INDEX__' ),
	'noindex'                   => array( '1', '__SERSIQÇINYO__', '__NOINDEX__' ),
	'numberingroup'             => array( '1', 'GRUBDEAMARE', 'AMARİYAGRUBER', 'NUMBERINGROUP', 'NUMINGROUP' ),
	'staticredirect'            => array( '1', '__STATİKHETENAYIŞ__', '__STATICHETENAYIŞ__', '__STATICREDIRECT__' ),
	'protectionlevel'           => array( '1', 'SEWİYEYÊSTARE', 'PROTECTIONLEVEL' ),
	'formatdate'                => array( '0', 'demêformati', 'formatdate', 'dateformat' ),
	'url_path'                  => array( '0', 'RAY', 'PATH' ),
	'url_wiki'                  => array( '0', 'WİKİ', 'WIKI' ),
	'url_query'                 => array( '0', 'PERSİYE', 'QUERY' ),
	'defaultsort_noerror'       => array( '0', 'xırabinçıniya', 'noerror' ),
	'defaultsort_noreplace'     => array( '0', 'cewabçıniyo', 'noreplace' ),
	'pagesincategory_all'       => array( '0', 'pêro', 'all' ),
	'pagesincategory_pages'     => array( '0', 'peley', 'pages' ),
	'pagesincategory_subcats'   => array( '0', 'bınkati', 'subcats' ),
	'pagesincategory_files'     => array( '0', 'dosyey', 'files' ),
);

