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
<table style="clear:both; text-align:top" cellpadding="0" cellspacing="0" border="0">
	<tr style="vertical-align:top">
		<td>
			<b>Online</b><br>
			<span style="white-space: nowrap"><input name="ns144" type="checkbox" value="1" $checked_data[144] id="mw-search-ns144" />&#160;<label for="mw-search-ns144">Online</label></span><br>
			<span style="white-space: nowrap"><input name="ns148" type="checkbox" value="1" $checked_data[148] id="mw-search-ns148" />&#160;<label for="mw-search-ns148">ESOMod</label></span>
		</td><td>
			<b>Skyrim</b><br>
			<span style="white-space: nowrap"><input name="ns134" type="checkbox" value="1" $checked_data[134] id="mw-search-ns134" />&#160;<label for="mw-search-ns134">Skyrim</label></span><br>
			<span style="white-space: nowrap"><input name="ns166" type="checkbox" value="1" $checked_data[166] id="mw-search-ns166" />&#160;<label for="mw-search-ns166">VSE</label></span><br>
			<span style="white-space: nowrap"><input name="ns142" type="checkbox" value="1" $checked_data[142] id="mw-search-ns142" />&#160;<label for="mw-search-ns142">Tes5Mod</label></span>
		</td><td>
			<b>Oblivion</b><br>
			<span style="white-space: nowrap"><input name="ns116" type="checkbox" value="1" $checked_data[116] id="mw-search-ns116" />&#160;<label for="mw-search-ns116">Oblivion</label></span><br>
			<span style="white-space: nowrap"><input name="ns126" type="checkbox" value="1" $checked_data[126] id="mw-search-ns126" />&#160;<label for="mw-search-ns126">Shivering</label></span><br>
			<span style="white-space: nowrap"><input name="ns124" type="checkbox" value="1" $checked_data[124] id="mw-search-ns124" />&#160;<label for="mw-search-ns124">Tes4Mod</label></span>
		</td><td>
			<b>Morrowind</b><br>
			<span style="white-space: nowrap"><input name="ns110" type="checkbox" value="1" $checked_data[110] id="mw-search-ns110" />&#160;<label for="mw-search-ns110">Morrowind</label></span><br>
			<span style="white-space: nowrap"><input name="ns112" type="checkbox" value="1" $checked_data[112] id="mw-search-ns112" />&#160;<label for="mw-search-ns112">Tribunal</label></span><br>
			<span style="white-space: nowrap"><input name="ns114" type="checkbox" value="1" $checked_data[114] id="mw-search-ns114" />&#160;<label for="mw-search-ns114">Bloodmoon</label></span><br>
			<span style="white-space: nowrap"><input name="ns122" type="checkbox" value="1" $checked_data[122] id="mw-search-ns122" />&#160;<label for="mw-search-ns122">Tes3Mod</label></span>
		</td><td>
			<b>Older Games</b><br>
			<span style="white-space: nowrap"><input name="ns108" type="checkbox" value="1" $checked_data[108] id="mw-search-ns108" />&#160;<label for="mw-search-ns108">Redguard</label></span><br>
			<span style="white-space: nowrap"><input name="ns106" type="checkbox" value="1" $checked_data[106] id="mw-search-ns106" />&#160;<label for="mw-search-ns106">Battlespire</label></span><br>
			<span style="white-space: nowrap"><input name="ns104" type="checkbox" value="1" $checked_data[104] id="mw-search-ns104" />&#160;<label for="mw-search-ns104">Daggerfall</label></span><br>
			<span style="white-space: nowrap"><input name="ns156" type="checkbox" value="1" $checked_data[156] id="mw-search-ns156" />&#160;<label for="mw-search-ns156">Tes2Mod</label></span><br>
			<span style="white-space: nowrap"><input name="ns102" type="checkbox" value="1" $checked_data[102] id="mw-search-ns102" />&#160;<label for="mw-search-ns102">Arena</label></span><br>
			<span style="white-space: nowrap"><input name="ns154" type="checkbox" value="1" $checked_data[154] id="mw-search-ns154" />&#160;<label for="mw-search-ns154">Tes1Mod</label></span>
		</td><td>
			<b>TES Travels</b><br>
			<span style="white-space: nowrap"><input name="ns136" type="checkbox" value="1" $checked_data[136] id="mw-search-ns136" />&#160;<label for="mw-search-ns136">OBMobile</label></span><br>
			<span style="white-space: nowrap"><input name="ns128" type="checkbox" value="1" $checked_data[128] id="mw-search-ns128" />&#160;<label for="mw-search-ns128">Shadowkey</label></span><br>
			<span style="white-space: nowrap"><input name="ns132" type="checkbox" value="1" $checked_data[132] id="mw-search-ns132" />&#160;<label for="mw-search-ns132">Dawnstar</label></span><br>
			<span style="white-space: nowrap"><input name="ns138" type="checkbox" value="1" $checked_data[138] id="mw-search-ns138" />&#160;<label for="mw-search-ns138">Stormhold</label></span>
		</td><td>
			<b>Other TES</b><br>
			<span style="white-space: nowrap"><input name="ns152" type="checkbox" value="1" $checked_data[152] id="mw-search-ns152" />&#160;<label for="mw-search-ns152">Blades</label></span><br>
			<span style="white-space: nowrap"><input name="ns158" type="checkbox" value="1" $checked_data[158] id="mw-search-ns158" />&#160;<label for="mw-search-ns158">Call to Arms</label></span><br>
			<span style="white-space: nowrap"><input name="ns150" type="checkbox" value="1" $checked_data[150] id="mw-search-ns150" />&#160;<label for="mw-search-ns150">Legends</label></span><br>
			<span style="white-space: nowrap"><input name="ns164" type="checkbox" value="1" $checked_data[164] id="mw-search-ns164" />&#160;<label for="mw-search-ns164">Pinball</label></span><br>
			<span style="white-space: nowrap"><input name="ns160" type="checkbox" value="1" $checked_data[160] id="mw-search-ns160" />&#160;<label for="mw-search-ns160">Other Mod</label></span>
		</td>
	</tr>
</table>
<div style="clear:both;"></div>
<table style="vertical-align:top" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td colspan="3" align="center"><b>General</b>
	</tr>
	<tr style="vertical-align:top">
		<td>
			<span style="white-space: nowrap"><input name="ns0" type="checkbox" value="1" $checked_data[0] id="mw-search-ns0" />&#160;<label for="mw-search-ns0">(Main)</label></span><br>
			<span style="white-space: nowrap"><input name="ns2" type="checkbox" value="1" $checked_data[2] id="mw-search-ns2" />&#160;<label for="mw-search-ns2">User</label></span><br>
			<span style="white-space: nowrap"><input name="ns4" type="checkbox" value="1" $checked_data[4] id="mw-search-ns4" />&#160;<label for="mw-search-ns4">UESPWiki</label></span><br>
			<span style="white-space: nowrap"><input name="ns6" type="checkbox" value="1" $checked_data[6] id="mw-search-ns6" />&#160;<label for="mw-search-ns6">File/Image</label></span><br>
			<span style="white-space: nowrap"><input name="ns8" type="checkbox" value="1" $checked_data[8] id="mw-search-ns8" />&#160;<label for="mw-search-ns8">MediaWiki</label></span>
		</td><td>
			<span style="white-space: nowrap"><input name="ns10" type="checkbox" value="1" $checked_data[10] id="mw-search-ns10" />&#160;<label for="mw-search-ns10">Template</label></span><br>
			<span style="white-space: nowrap"><input name="ns12" type="checkbox" value="1" $checked_data[12] id="mw-search-ns12" />&#160;<label for="mw-search-ns12">Help</label></span><br>
			<span style="white-space: nowrap"><input name="ns14" type="checkbox" value="1" $checked_data[14] id="mw-search-ns14" />&#160;<label for="mw-search-ns14">Category</label></span><br>
			<span style="white-space: nowrap"><input name="ns118" type="checkbox" value="1" $checked_data[118] id="mw-search-ns118" />&#160;<label for="mw-search-ns118">General</label></span><br>
			<span style="white-space: nowrap"><input name="ns120" type="checkbox" value="1" $checked_data[120] id="mw-search-ns120" />&#160;<label for="mw-search-ns120">Review</label></span>
		</td><td>
			<span style="white-space: nowrap"><input name="ns140" type="checkbox" value="1" $checked_data[140] id="mw-search-ns140" />&#160;<label for="mw-search-ns140">Books</label></span><br>
			<span style="white-space: nowrap"><input name="ns130" type="checkbox" value="1" $checked_data[130] id="mw-search-ns130" />&#160;<label for="mw-search-ns130">Lore</label></span><br>
			<span style="white-space: nowrap"><input name="ns162" type="checkbox" value="1" $checked_data[162] id="mw-search-ns162" />&#160;<label for="mw-search-ns162">Merchandise</label></span>
		</td>
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
