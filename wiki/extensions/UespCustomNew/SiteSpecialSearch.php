<?php
/*
 * Revamp search function to add extension-specific search page and alter multiple aspects of how search works
 */
global $IP;
require_once "$IP/includes/specials/SpecialSearch.php";


class SiteSpecialSearch extends SpecialSearch {

	protected $searchTalkPages;

	public function __construct() {
                parent::__construct( 'Search' );

		$this->searchTalkPages = false;
        }

	public function load()
	{
		parent::load();

		$request = $this->getRequest();
		$default = $request->getBool( 'profile') ? 0 : 1;
		$this->searchTalkPages = $request->getBool('talkpages', $default ) ? 1 : 0;

		if ( $this->searchTalkPages )
		{
			$this->enableTalkPageSearch();
		}
		else
		{
			$this->setExtraParam ( 'talkpages', '0' );
		}
	}

	protected function enableTalkPageSearch ()
	{
		$orignamespaces = $this->namespaces;
		//$talknamespaces = array();

		foreach ( $orignamespaces as $namespace => $name )
		{
			$this->namespaces[] = $name | 1;
		}

		//error_log("SearchLog: C1=" . count($this->namespaces) . "  C2=" . count($talknamespaces));

		//$this->namespaces = array_merge( $this->namespaces, $talknamespaces );
		//$this->namespaces = $this->name

		//error_log("SearchLog: C3=". count($this->namespaces));
	}

	protected function powerSearchBox ($term, $opts)
	{
		$checked_data = array();
		$checked_talkpages = "";

		if ( $this->searchTalkPages ) $checked_talkpages = 'checked="checked"';

			//See which namespace boxes to check
		foreach ( SearchEngine::searchableNamespaces() as $namespace => $name) {

			if ( in_array( $namespace, $this->namespaces ) ) 
				$checked_data[$namespace] = 'checked="checked"';
			else
				$checked_data[$namespace] = '';

		}

		$remember_token = "";
		$user = $this->getUser();
		if ( $user->isLoggedIn() ) {
			$remember_token = $user->getEditToken(
				'searchnamespace',
				$this->getRequest()
			);
		}
		
		$result = <<< UESP_EOT

<fieldset id="mw-searchoptions" style="margin:0em;"><legend>Advanced search</legend>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>Online</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns144" type="checkbox" value="1" $checked_data[144] id="mw-search-ns144" />&#160;<label for="mw-search-ns144">Online</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns148" type="checkbox" value="1" $checked_data[148] id="mw-search-ns148" />&#160;<label for="mw-search-ns148">ESOMod</label></td>
	</tr><tr>
		<td>&nbsp;</td>
	</tr><tr>
		<td><b>Other TES</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns152" type="checkbox" value="1" $checked_data[152] id="mw-search-ns152" />&#160;<label for="mw-search-ns152">Blades</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns150" type="checkbox" value="1" $checked_data[150] id="mw-search-ns150" />&#160;<label for="mw-search-ns150">Legends</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns130" type="checkbox" value="1" $checked_data[130] id="mw-search-ns130" />&#160;<label for="mw-search-ns130">Lore</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns140" type="checkbox" value="1" $checked_data[140] id="mw-search-ns140" />&#160;<label for="mw-search-ns140">Books</label></td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>Skyrim</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns134" type="checkbox" value="1" $checked_data[134] id="mw-search-ns134" />&#160;<label for="mw-search-ns134">Skyrim</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns146" type="checkbox" value="1" $checked_data[146] id="mw-search-ns146" />&#160;<label for="mw-search-ns146">Dragonborn</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns142" type="checkbox" value="1" $checked_data[142] id="mw-search-ns142" />&#160;<label for="mw-search-ns142">Tes5Mod</label></td>		
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>Oblivion</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns116" type="checkbox" value="1" $checked_data[116] id="mw-search-ns116" />&#160;<label for="mw-search-ns116">Oblivion</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns126" type="checkbox" value="1" $checked_data[126] id="mw-search-ns126" />&#160;<label for="mw-search-ns126">Shivering</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns124" type="checkbox" value="1" $checked_data[124] id="mw-search-ns124" />&#160;<label for="mw-search-ns124">Tes4Mod</label></td>		
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>Morrowind</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns110" type="checkbox" value="1" $checked_data[110] id="mw-search-ns110" />&#160;<label for="mw-search-ns110">Morrowind</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns112" type="checkbox" value="1" $checked_data[112] id="mw-search-ns112" />&#160;<label for="mw-search-ns112">Tribunal</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns114" type="checkbox" value="1" $checked_data[114] id="mw-search-ns114" />&#160;<label for="mw-search-ns114">Bloodmoon</label></td>
	</tr><tr>				
		<td style="white-space: nowrap"><input name="ns122" type="checkbox" value="1" $checked_data[122] id="mw-search-ns122" />&#160;<label for="mw-search-ns122">Tes3Mod</label></td>		
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>Older Games</b></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns108" type="checkbox" value="1" $checked_data[108] id="mw-search-ns108" />&#160;<label for="mw-search-ns108">Redguard</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns106" type="checkbox" value="1" $checked_data[106] id="mw-search-ns106" />&#160;<label for="mw-search-ns106">Battlespire</label></td>		
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns104" type="checkbox" value="1" $checked_data[104] id="mw-search-ns104" />&#160;<label for="mw-search-ns104">Daggerfall</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns156" type="checkbox" value="1" $checked_data[156] id="mw-search-ns156" />&#160;<label for="mw-search-ns156">Tes2Mod</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns102" type="checkbox" value="1" $checked_data[102] id="mw-search-ns102" />&#160;<label for="mw-search-ns102">Arena</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns154" type="checkbox" value="1" $checked_data[154] id="mw-search-ns154" />&#160;<label for="mw-search-ns154">Tes1Mod</label></td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><b>TES Travels</b></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns136" type="checkbox" value="1" $checked_data[136] id="mw-search-ns136" />&#160;<label for="mw-search-ns136">OBMobile</label></td>				
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns128" type="checkbox" value="1" $checked_data[128] id="mw-search-ns128" />&#160;<label for="mw-search-ns128">Shadowkey</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns132" type="checkbox" value="1" $checked_data[132] id="mw-search-ns132" />&#160;<label for="mw-search-ns132">Dawnstar</label></td>
	</tr><tr>		
		<td style="white-space: nowrap"><input name="ns138" type="checkbox" value="1" $checked_data[138] id="mw-search-ns138" />&#160;<label for="mw-search-ns138">Stormhold</label></td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td colspan="2" align="center"><b>General</b></td>		
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns0" type="checkbox" value="1" $checked_data[0] id="mw-search-ns0" />&#160;<label for="mw-search-ns0">(Main)</label></td>
		<td style="white-space: nowrap"><input name="ns10" type="checkbox" value="1" $checked_data[10] id="mw-search-ns10" />&#160;<label for="mw-search-ns10">Template</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns2" type="checkbox" value="1" $checked_data[2] id="mw-search-ns2" />&#160;<label for="mw-search-ns2">User</label></td>
		<td style="white-space: nowrap"><input name="ns12" type="checkbox" value="1" $checked_data[12] id="mw-search-ns12" />&#160;<label for="mw-search-ns12">Help</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns4" type="checkbox" value="1" $checked_data[4] id="mw-search-ns4" />&#160;<label for="mw-search-ns4">UESPWiki</label></td>
		<td style="white-space: nowrap"><input name="ns14" type="checkbox" value="1" $checked_data[14] id="mw-search-ns14" />&#160;<label for="mw-search-ns14">Category</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns6" type="checkbox" value="1" $checked_data[6] id="mw-search-ns6" />&#160;<label for="mw-search-ns6">File/Image</label></td>
		<td style="white-space: nowrap"><input name="ns118" type="checkbox" value="1" $checked_data[118] id="mw-search-ns118" />&#160;<label for="mw-search-ns118">General</label></td>
	</tr><tr>
		<td style="white-space: nowrap"><input name="ns8" type="checkbox" value="1" $checked_data[8] id="mw-search-ns8" />&#160;<label for="mw-search-ns8">MediaWiki</label></td>
		<td style="white-space: nowrap"><input name="ns120" type="checkbox" value="1" $checked_data[120] id="mw-search-ns120" />&#160;<label for="mw-search-ns120">Review</label></td>
	</tr>
</table>

<div class="divider"></div>
<input name="talkpages" type="checkbox" value="1" $checked_talkpages id="mw-search-ns-talkpages" />&#160;<label for="talkpages">Talk Pages</label>
&nbsp;	
<input name="nsRemember" type="checkbox" value=$remember_token id="mw-search-powersearch-remember" />&#160;<label for="mw-search-powersearch-remember">Remember selection for future searches</label>
<input type="hidden" value="advanced" name="profile" />
<div id="mw-search-togglebox"></div>
</fieldset>

UESP_EOT;
		return $result;
	}

};

?>
