<?php
/*
 * TODO:
 *	 	- Attribute icons?
 *
 */


require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");


class CUespLegendsCardDataViewer
{
	
	static $LEGENDS_TYPES = array(
			"Action", 
			"Creature",
			"Item", 
			"Support"
	);
	
	static $LEGENDS_SUBTYPES = array(
			"Argonian",
			"Animated Item",
			"Ash Creature",
			"Atronach",
			"Automaton",
			"Ayleid",
			"Beast", 
			"Breton", 
			"Centaur", 
			"Chaurus", 
			"Daedra", 
			"Dark Elf", 
			"Defense", 
			"Dragon", 
			"Dreugh", 
			"Dwemer", 
			"Egg",
			"Elytra",
			"Fabricant", 
			"Factotum", 
			"Falmer", 
			"Fish", 
			"Gargoyle", 
			"Giant", 
			"Goblin",
			"Grummite",
			"Harpy", 
			"High Elf",
			"Imp", 
			"Imperfect",
			"Imperial", 
			"Insect", 
			"Khajiit", 
			"Kwama", 
			"Lurcher", 
			"Mammoth", 
			"Mantikora",
			"Minotaur", 
			"Mudcrab", 
			"Mummy", 
			"Nereid", 
			"Nord",
			"Ogre", 
			"Orc", 
			"Pastry", 
			"Portal",
			"Reachman", 
			"Redguard", 
			"Reptile",
			"Skeever", 
			"Skeleton", 
			"Spider", 
			"Spirit", 
			"Spriggan",
			"Trap",
			"Troll", 
			"Undead", 
			"Vampire", 
			"Wamasu", 
			"Werewolf", 
			"Wolf", 
			"Wood Elf", 
			"Wraith" 
	);
	
	static $LEGENDS_ATTRIBUTES = array(
			"Agility", 
			"Endurance", 
			"Intelligence", 
			"Neutral", 
			"Strength", 
			"Willpower"
	);
	
	static $LEGENDS_RARITIES = array(
			"Common", 
			"Rare", 
			"Epic", 
			"Legendary"
	);
	
				/* Loaded from database */
	static $LEGENDS_SETS = null;
	
	static $LEGENDS_CLASSES = array(
			"Aldmeri Dominion",
			"Archer",
			"Assassin",
			"Battlemage",
			"Crusader",
			"Daggerfall Covenant",
			"Ebonheart Pact",
			"House Dagoth",
			"House Hlaalu",
			"House Redoran",
			"House Telvanni",
			"Mage",
			"Monk",
			"Scout",
			"Sorcerer",
			"Spellsword",
			"The Empire of Cyrodiil",
			"The Guildsword",
			"Tribunal Temple",
			"Warrior",			
	);
	
	public $inputParams = array();
	public $inputCardName = "";
	public $inputEditCard = "";
	public $inputRenameCard = "";
	public $inputDeleteCard = "";
	public $inputDeleteSet = "";
	public $inputAddSet = "";
	public $inputSaveCard = false;
	public $inputCreateCard = false;
	public $inputEditSets = false;
	public $inputEditDisambiguation = false;
	public $inputDeleteDisambiguation = "";
	public $inputAddDisambiguation = "";
	public $inputAddLinkSuffix = "";
	
	public $wikiContext = null;
	public $db = null;
	public $errorMsg = "";
	
	public $cards = array();
	public $singleCardData = null;
	public $inputCardData = array(
			'name' => '',
			'text' => '',
			'type' => '',
			'subtype' => '',
			'image' => '',
			'rarity' => '',
			'attribute' => '',
			'attribute2' => '',
			'attribute3' => '',
			'class' => '',
			'set' => '',
			'magicka' => 0,
			'power' => 0,
			'health' => 0,
			'uses' => '',
			'obtainable' => '',
			'unique' => '',
			'training1' => '',
			'trainingLevel1' => 0,
			'training2' => '',
			'trainingLevel2' => 0,
			'filter' => 0,
			'minMagicka' => '',
			'maxMagicka' => '',
			'minPower' => '',
			'maxPower' => '',
			'minHealth' => '',
			'maxHealth' => '',
		);
	public $totalCardCount = 0;


	public function __construct ()
	{
		$this->inputParams = $_REQUEST;
		$this->ParseInputParams();	
	}
	
	
	public function ParseInputParams()
	{
		if ($this->inputParams['name'] != "") $this->inputCardName = $this->inputParams['name'];
		if ($this->inputParams['card'] != "") $this->inputCardName = $this->inputParams['card'];
		if ($this->inputParams['edit'] != "") $this->inputEditCard = $this->inputParams['edit'];
		if ($this->inputParams['rename'] != "") $this->inputRenameCard = $this->inputParams['rename'];
		if ($this->inputParams['delete'] != "") $this->inputDeleteCard = $this->inputParams['delete'];
		if ($this->inputParams['save'] != "") $this->inputSaveCard = intval($this->inputParams['save']) != 0;
		if ($this->inputParams['create'] != "") $this->inputCreateCard = intval($this->inputParams['create']) != 0;
		if ($this->inputParams['editsets'] != "") $this->inputEditSets = intval($this->inputParams['editsets']) != 0;
		if ($this->inputParams['deleteset'] != "") $this->inputDeleteSet = $this->inputParams['deleteset'];
		if ($this->inputParams['newset'] != "") $this->inputAddSet = $this->inputParams['newset'];
		if ($this->inputParams['editdisamb'] != "") $this->inputEditDisambiguation = intval($this->inputParams['editdisamb']);
		if ($this->inputParams['newdisamb'] != "") $this->inputAddDisambiguation = $this->inputParams['newdisamb'];
		if ($this->inputParams['newlinksuffix'] != "") $this->inputAddLinkSuffix = $this->inputParams['newlinksuffix'];
		if ($this->inputParams['deletedisamb'] != "") $this->inputDeleteDisambiguation = $this->inputParams['deletedisamb'];
		
		if ($this->inputCreateCard) $this->inputCardName = trim($this->inputCardName);
		
		$this->inputCardData['name'] = $this->inputCardName;
		
		if ($this->inputParams['type'] !== null) $this->inputCardData['type'] = $this->inputParams['type'];
		if ($this->inputParams['subtype'] !== null) $this->inputCardData['subtype'] = $this->inputParams['subtype'];
		if ($this->inputParams['race'] !== null) $this->inputCardData['subtype'] = $this->inputParams['race'];
		if ($this->inputParams['class'] !== null) $this->inputCardData['class'] = $this->inputParams['class'];
		if ($this->inputParams['set'] !== null) $this->inputCardData['set'] = $this->inputParams['set'];
		if ($this->inputParams['attribute'] !== null) $this->inputCardData['attribute'] = $this->inputParams['attribute'];
		if ($this->inputParams['attribute1'] !== null) $this->inputCardData['attribute'] = $this->inputParams['attribute1'];
		if ($this->inputParams['attribute2'] !== null) $this->inputCardData['attribute2'] = $this->inputParams['attribute2'];
		if ($this->inputParams['attribute3'] !== null) $this->inputCardData['attribute3'] = $this->inputParams['attribute3'];
		if ($this->inputParams['rarity'] !== null) $this->inputCardData['rarity'] = $this->inputParams['rarity'];
		if ($this->inputParams['magicka'] !== null) $this->inputCardData['magicka'] = intval($this->inputParams['magicka']);
		if ($this->inputParams['power'] !== null) $this->inputCardData['power'] = intval($this->inputParams['power']);
		if ($this->inputParams['health'] !== null) $this->inputCardData['health'] = intval($this->inputParams['health']);
		if ($this->inputParams['obtainable'] !== null && $this->inputParams['obtainable'] != '') $this->inputCardData['obtainable'] = intval($this->inputParams['obtainable']);
		if ($this->inputParams['unique'] !== null && $this->inputParams['unique'] != '') $this->inputCardData['unique'] = intval($this->inputParams['unique']);
		if ($this->inputParams['training1'] !== null) $this->inputCardData['training1'] = $this->inputParams['training1'];
		if ($this->inputParams['training2'] !== null) $this->inputCardData['training2'] = $this->inputParams['training2'];
		if ($this->inputParams['trainingLevel1'] !== null) $this->inputCardData['trainingLevel1'] = intval($this->inputParams['trainingLevel1']);
		if ($this->inputParams['trainingLevel2'] !== null) $this->inputCardData['trainingLevel2'] = intval($this->inputParams['trainingLevel2']);
		if ($this->inputParams['uses'] !== null) $this->inputCardData['uses'] = $this->inputParams['uses'];
		if ($this->inputParams['text'] !== null) $this->inputCardData['text'] = $this->inputParams['text'];
		if ($this->inputParams['image'] !== null) $this->inputCardData['image'] = $this->inputParams['image'];		
		if ($this->inputParams['filter'] !== null) $this->inputCardData['filter'] = intval($this->inputParams['filter']);
		
		if ($this->inputParams['minMagicka'] !== null) $this->inputCardData['minMagicka'] = $this->inputParams['minMagicka'];
		if ($this->inputParams['maxMagicka'] !== null) $this->inputCardData['maxMagicka'] = $this->inputParams['maxMagicka'];
		if ($this->inputParams['minPower'] !== null) $this->inputCardData['minPower'] = $this->inputParams['minPower'];
		if ($this->inputParams['maxPower'] !== null) $this->inputCardData['maxPower'] = $this->inputParams['maxPower'];
		if ($this->inputParams['minHealth'] !== null) $this->inputCardData['minHealth'] = $this->inputParams['minHealth'];
		if ($this->inputParams['maxHealth'] !== null) $this->inputCardData['maxHealth'] = $this->inputParams['maxHealth'];
	}
	

	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		
		//print($errorMsg);
		//if ($this->db) print($this->db->error);
		
		return false;
	}

	
	public function InitDatabase()
	{
		global $uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase;
		
		if ($this->db != null) return true;
		
		$this->db = new mysqli($uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		UpdateLegendsPageViews("cardDataViews");
	
		return true;
	}
	
	
	public function InitDatabaseWrite()
	{
		global $uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase;
	
		$this->db = new mysqli($uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		UpdateLegendsPageViews("cardDataEdits");
	
		return true;
	}
	
	
	public function DoesCardExist($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT name FROM cards WHERE name='$safeName';";
		$result = $this->db->query($query);
		if ($result === false || $result->num_rows <= 0) return false;
		return true;
	}
	
	
	public function CanEditCard()
	{
		if ($this->wikiContext == null) return false;
		
		$user = $this->wikiContext->getUser();
		if ($user == null) return false;
		
		if (!$user->isLoggedIn()) return false;
		if (strcasecmp($user->getName(), $this->characterData['wikiUserName']) == 0) return true;
		
		return $user->isAllowedAny('legendscarddata_edit');
	}
	
	
	public function CanCreateCard()
	{
		if ($this->wikiContext == null) return false;
		
		$user = $this->wikiContext->getUser();
		if ($user == null) return false;
		
		if (!$user->isLoggedIn()) return false;
		if (strcasecmp($user->getName(), $this->characterData['wikiUserName']) == 0) return true;
		
		return $user->isAllowedAny('legendscarddata_add');
	}
	
	
	public function CreateEditListOutput($list, $currentValue, $id)
	{
		$name = strtolower($id);
		$output = "<select id='eslegCardInput$id' name='$name'>";
		
		$selected = "";
		if ($currentValue == "") $selected = "selected";
		$output .= "<option value='' $selected>";
		
		foreach ($list as $item)
		{
			$selected = "";
			if ($currentValue == $item) $selected = "selected";
			$output .= "<option value=\"$item\" $selected>$item";
		}
		
		$output .= "</select>";
		return $output;
	}
	
	
	public function GetTotalCards()
	{
		$this->totalCardCount = 0;
		
		$result = $this->db->query("SELECT count(*) as count FROM cards;");
		if ($result === false) return 0;
		
		$row = $result->fetch_assoc();
		$this->totalCardCount = intval($row['count']); 
		
		return $this->totalCardCount; 
	}
	
	
	public function GetFilterCardDataQuery()
	{
		$query = "SELECT * FROM cards ";
		$where = array();
		
		if ($this->inputCardData['text'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['text']);
			$where[] = "MATCH(name, text) AGAINST('$safeValue' IN BOOLEAN MODE)";
		}
		
		if ($this->inputCardData['attribute'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['attribute']);
			$where[] = "(attribute='$safeValue' OR attribute2='$safeValue' OR attribute3='$safeValue')";
		}
		
		if ($this->inputCardData['type'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['type']);
			$where[] = "type='$safeValue'";
		}
		
		if ($this->inputCardData['subtype'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['subtype']);
			$where[] = "subtype='$safeValue'";
		}
		
		if ($this->inputCardData['class'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['class']);
			$where[] = "`class`='$safeValue'";
		}
		
		if ($this->inputCardData['set'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['set']);
			$where[] = "`set`='$safeValue'";
		}
		
		if ($this->inputCardData['rarity'] != "")
		{
			$safeValue = $this->db->real_escape_string($this->inputCardData['rarity']);
			$where[] = "rarity='$safeValue'";
		}
		
		if ($this->inputCardData['minMagicka'] != "")
		{
			$safeValue = intval($this->inputCardData['minMagicka']);
			$where[] = "magicka >= '$safeValue'";
		}
		
		if ($this->inputCardData['maxMagicka'] != "")
		{
			$safeValue = intval($this->inputCardData['maxMagicka']);
			$where[] = "magicka <= '$safeValue'";
		}
		
		if ($this->inputCardData['minPower'] != "")
		{
			$safeValue = intval($this->inputCardData['minPower']);
			$where[] = "power >= '$safeValue'";
		}
		
		if ($this->inputCardData['maxPower'] != "")
		{
			$safeValue = intval($this->inputCardData['maxPower']);
			$where[] = "power <= '$safeValue'";
		}
		
		if ($this->inputCardData['minHealth'] != "")
		{
			$safeValue = intval($this->inputCardData['minHealth']);
			$where[] = "health >= '$safeValue'";
		}
		
		if ($this->inputCardData['maxHealth'] != "")
		{
			$safeValue = intval($this->inputCardData['maxHealth']);
			$where[] = "health <= '$safeValue'";
		}
		
		if ($this->inputCardData['obtainable'] !== "")
		{
			$safeValue = intval($this->inputCardData['obtainable']);
			$where[] = "obtainable = '$safeValue'";
		}
		
		if ($this->inputCardData['unique'] !== "")
		{
			$safeValue = intval($this->inputCardData['unique']);
			$where[] = "`unique` = '$safeValue'";
		}
				
		if (count($where) > 0) $query .= " WHERE " . implode( " AND ", $where);		
		
		$query .= " ORDER BY name;";
		return $query;
	}
	
	
	public function GetCardDataQuery()
	{
		
		if ($this->inputCardName != "" && $this->inputRenameCard == "")
		{
			$safeName = $this->db->real_escape_string($this->inputCardName);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else if ($this->inputEditCard != "")
		{
			$safeName = $this->db->real_escape_string($this->inputEditCard);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else if ($this->inputRenameCard != "")
		{
			$safeName = $this->db->real_escape_string($this->inputRenameCard);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else if ($this->inputDeleteCard != "")
		{
			$safeName = $this->db->real_escape_string($this->inputDeleteCard);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else if ($this->inputCardData['filter'] > 0)
		{
			$query = $this->GetFilterCardDataQuery();
		}
		else
		{
			$query = "SELECT * FROM cards ORDER BY name;";
		}
				
		return $query;
	}
	
	
	public function LoadDisambiguationData()
	{
		LoadLegendsDisambiguationPages($this->db);
	}
	
	
	public function LoadSetData()
	{
		if (self::$LEGENDS_SETS != null) return true;
		
		self::$LEGENDS_SETS = array();
		
		if (!$this->InitDatabase()) return false;
		
		$query = "SELECT * FROM sets;";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("ERROR: Failed to load set data from table!");
		
		while (($set = $result->fetch_assoc()))
		{
			self::$LEGENDS_SETS[] = $set['name'];
		}
		
		sort(self::$LEGENDS_SETS);
		return true;
	}
	
	
	public function LoadCardData()
	{
		if (!$this->InitDatabase()) return false;
		
		$this->LoadSetData();
		$this->GetTotalCards();
		
		$query = $this->GetCardDataQuery();
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("ERROR: Failed to load card data from table!");
		
		$this->cards = array();
		
		while (($card = $result->fetch_assoc()))
		{
			$name = $card['name'];
			$this->cards[$name] = $card;
			$this->singleCardData = $card;
		}
	
		return true;
	}
	
	
	public function Escape($html)
	{
		return htmlspecialchars($html);	
	}
	
	
	public function EscapeAttribute($html)
	{
		return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');	
	}
	
	
	public function GetBreadcrumbTrail()
	{
		$output = "<div class='eslegBreadcrumb'>";
		
		if ($this->inputCardName != "" || $this->inputSaveCard || $this->inputCreateCard || $this->inputEditSets || $this->inputEditDisambiguation)
		{
			if ($this->inputRenameCard != "")
			{
				$safeName = urlencode($this->inputCardName);
				$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
				$output .= " : " . "<a href='/wiki/Special:LegendsCardData?card=$safeName'>View Card</a>";
			}
			else
			{
				$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
			}
		}
		else if ($this->inputDeleteCard != "")
		{
			$safeName = urlencode($this->inputDeleteCard);
			$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
			$output .= " : " . "<a href='/wiki/Special:LegendsCardData?card=$safeName'>View Card</a>";
		}
		else if ($this->inputRenameCard != "")
		{
			$safeName = urlencode($this->inputRenameCard);
			$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
			$output .= " : " . "<a href='/wiki/Special:LegendsCardData?card=$safeName'>View Card</a>";
		}
		else if ($this->inputEditCard != "")
		{
			$safeName = urlencode($this->inputEditCard);
			$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
			$output .= " : " . "<a href='/wiki/Special:LegendsCardData?card=$safeName'>View Card</a>";
		}
		
		$output .= "</div>";
		return $output;	
	}
	
	
	public function GetCardLink($card)
	{
		$name = $this->Escape($card);
		$encodeName = urlencode($card);
		$nameLink = "<a href=\"/wiki/Special:LegendsCardData?card=$encodeName\" class='legendsCardLink' card=\"$name\">$name</a>";
		return $nameLink;
	}
	
	
	public function GetCardOutputRow($card)
	{
		$output = "<tr>";
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$attribute3 = $this->Escape($card['attribute3']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$unique = $card['unique'];
		$training1 = $card['training1'];
		$training2 = $card['training2'];
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$training = "";
		if ($training1) $training .= $this->GetCardLink($training1) . " @ Lvl $trainingLevel1";
		if ($training2) $training .= "<br/>" . $this->GetCardLink($training2) . " @ Lvl $trainingLevel2";
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		
		$attribute = $attribute1;
		if ($attribute2 != "") $attribute .= "+$attribute2";
		if ($attribute3 != "") $attribute .= "+$attribute3";
		
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "No";
		
		if ($unique == 1)
			$unique = "Yes";
		else
			$unique = "No";
			
		$text = str_replace("\n", "<br/>", $text);
		
		$encodeName = urlencode($card['name']);
		$nameLink = $this->GetCardLink($card['name']);
		$wikiLink = "<a href=\"/wiki/Legends:$name\">Legends:$name</a>";
		
		$output .= "<td>$nameLink</td>";
		$output .= "<td>$type</td>";
		$output .= "<td>$subtype</td>";
		$output .= "<td>$magicka</td>";
		$output .= "<td>$power</td>";
		$output .= "<td>$health</td>";
		$output .= "<td>$attribute</td>";
		$output .= "<td>$class</td>";
		$output .= "<td>$set</td>";
		$output .= "<td>$rarity</td>";
		$output .= "<td>$obtainable</td>";
		$output .= "<td>$unique</td>";
		//$output .= "<td>$training</td>";
		$output .= "<td>$uses</td>";
		$output .= "<td>$text</td>";
		$output .= "<td><nobr>$wikiLink</nobr><br/><nobr>$imageLink</nobr></td>";
		
		$output .= "</tr>";
		return $output;
	}
	
	
	public function GetCardDeleteDisambiguationOutput()
	{
		global $UESP_LEGENDS_DISAMBIGUATION;
		
		if (!$this->CanEditCard()) return "You do not have permission to edit disambiguation data!";
		if (!$this->InitDatabaseWrite()) return "Failed to initialize database!";
		$this->LoadDisambiguationData();
		
		$deleteCard = $this->inputDeleteDisambiguation;
		$safeCard = $this->Escape($deleteCard);
		$safeCardDB = $this->db->real_escape_string($deleteCard);
		
		$query = "DELETE FROM disambiguation WHERE name='$safeCardDB';";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to delete disambiguation page '<em>$safeCard</em>'!<br/>" . $this->db->error;
		
		unset($UESP_LEGENDS_DISAMBIGUATION[$deleteCard]);
		
		$output = "Deleted disambiguation page '<em>$safeCard</em>'! ";
		
		$output .= $this->GetCardEditDisambiguationOutput();
		
		return $output;
	}
	
	
	public function GetCardAddDisambiguationOutput()
	{
		global $UESP_LEGENDS_DISAMBIGUATION;
		
		if (!$this->CanEditCard()) return "You do not have permission to edit disambiguation data!";
		if (!$this->InitDatabaseWrite()) return "Failed to initialize database!";
		$this->LoadDisambiguationData();
		
		$newCard = $this->inputAddDisambiguation;
		$newLinkSuffix = $this->inputAddLinkSuffix;
		$safeCard = $this->Escape($newCard);
		$safeSuffix = $this->Escape($newLinkSuffix);
		$safeNameDB = $this->db->real_escape_string($newCard);
		$safeLinkSuffixDB = $this->db->real_escape_string($newLinkSuffix);
		
		$query = "INSERT INTO disambiguation(name, linkSuffix) VALUES('$safeNameDB', '$safeLinkSuffixDB');";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to add new disambiguation page '<em>$safeCard</em>'!<br/>" . $this->db->error;
		
		$UESP_LEGENDS_DISAMBIGUATION[$newCard] = $newLinkSuffix;
		ksort($UESP_LEGENDS_DISAMBIGUATION);
		
		$output = "Added new disambiguation page '<em>$safeCard</em>'! ";
		
		$output .= $this->GetCardEditDisambiguationOutput();
		
		return $output;
	}
	
	
	public function GetCardEditDisambiguationOutput()
	{
		global $UESP_LEGENDS_DISAMBIGUATION;
		
		if (!$this->CanEditCard()) return "You do not have permission to edit disambiguation data!";
		$this->LoadDisambiguationData();
		
		$output = "";
		$count = count($UESP_LEGENDS_DISAMBIGUATION);
		$output .= "Editing $count disambiguation pages.<br/>";
		
		$output .= "<table class='eslegCardDetailsTable'>";
		$output .= "<tr><th>Card Name</th><th>Link Suffix</th></tr>";
		
		foreach ($UESP_LEGENDS_DISAMBIGUATION as $cardName => $linkSuffix)
		{
			$safeName = $this->Escape($cardName);
			$safeSuffix = $this->Escape($linkSuffix);
			
			$output .= "<tr><td>$safeName</td><td>$safeSuffix</td><td>";
			$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
			$output .= "<input type='hidden' value='1' name='editdisamb'>";
			$output .= "<input type='hidden' value=\"$safeName\" name='deletedisamb'>";
			$output .= "<input type='submit' value='Delete'>";
			$output .= "</form>";
			$output .= "</td></tr>";
		}
		
		$output .= "<tr>";
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='editdisamb'>";
		$output .= "<td><input type='text' name='newdisamb' value='' maxlength='36' /></td>";
		$output .= "<td><input type='text' name='newlinksuffix' value='card' maxlength='16' /></td>";
		$output .= "<td><input type='submit' value='Add New'></td>";
		$output .= "</form>";
		$output .= "</tr>";
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function GetCardDeleteSetOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit set data!";
		if (!$this->LoadSetData()) return "Failed to load set data!";
		if (!$this->InitDatabaseWrite()) return "Failed to initialize database!";
		
		$deleteSet = $this->inputDeleteSet;
		$safeSet = $this->Escape($deleteSet);
		$safeSetDB = $this->db->real_escape_string($deleteSet);
		
		$query = "DELETE FROM sets WHERE name='$safeSetDB';";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to delete set '<em>$safeSet</em>'!<br/>" . $this->db->error;
		
		foreach (self::$LEGENDS_SETS as $i => $set)
		{
			if ($set == $deleteSet) 
			{
				unset(self::$LEGENDS_SETS[$i]);
			}
		}
		
		$output = "Deleted set '<em>$safeSet</em>'! ";
		
		$output .= $this->GetCardEditSetsOutput();
		
		return $output;
	}
	
	
	public function GetCardAddSetOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit set data!";
		if (!$this->LoadSetData()) return "Failed to load set data!";
		if (!$this->InitDatabaseWrite()) return "Failed to initialize database!";
		
		$newSet = $this->inputAddSet;
		$safeSet = $this->Escape($newSet);
		$safeSetDB = $this->db->real_escape_string($newSet);
		
		$query = "INSERT INTO sets(name) VALUES('$safeSetDB');";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to add new set '<em>$safeSet</em>'!<br/>" . $this->db->error;
		
		self::$LEGENDS_SETS[] = $newSet;
		$output = "Added new set '<em>$safeSet</em>'! ";
		
		$output .= $this->GetCardEditSetsOutput();
		
		return $output;
	}
	
	
	public function GetCardEditSetsOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit set data!";
		if (!$this->LoadSetData()) return "Failed to load set data!";
		
		$output = "";
		$count = count(self::$LEGENDS_SETS);
		$output .= "Editing $count card sets.<br/>";
		
		$output .= "<table class='eslegCardDetailsTable'>";		
		
		foreach (self::$LEGENDS_SETS as $i => $set)
		{
			$safeSet = $this->Escape($set);
			$output .= "<tr><td>$safeSet</td><td>";
			$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
			$output .= "<input type='hidden' value='1' name='editsets'>";
			$output .= "<input type='hidden' value=\"$safeSet\" name='deleteset'>";
			$output .= "<input type='submit' value='Delete'>";
			$output .= "</form>";
			$output .= "</td></tr>";
		}
		
		$output .= "<tr><td>";
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='editsets'>";
		$output .= "<input type='text' name='newset' value='' maxlength='36' /></td><td><input type='submit' value='Add New'>";
		$output .= "</form>";
		$output .= "</td></tr>";
		$output .= "</table>";		
		
		return $output;
	}
	
	
	public function GetCardEditOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		$this->LoadSetData();
		
		$output = "";
		$safeName = $this->Escape($this->inputEditCard);
		$card = $this->singleCardData;
		
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		if ($this->inputCreateCard)
			$output .= "Creating new card.";
		else
			$output .= "Editing card $safeName.";
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$attribute3 = $this->Escape($card['attribute3']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$unique = $card['unique'];
		$training1 = $this->Escape($card['training1']);
		$training2 = $this->Escape($card['training2']);
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		$imageSrc = "//legends.uesp.net/$name.png";
		
		if ($image == "")
		{
			$imageLink = "";
			$imageSrc = "";
		}
		
		if ($obtainable == 1)
			$obtainable = "checked";
		else
			$obtainable = "";
		
		if ($unique == 1)
			$unique = "checked";
		else
			$unique = "";
		
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='save'>";
		
		if ($this->inputCreateCard)
		{
			$output .= "<input type='hidden' value='1' name='create'>";
		}
		else
		{
			$output .= "<input type='hidden' value=\"$name\" name='name' id='eslegCardInputName' maxlength='100'>";
		}
		
		$output .= "<img src=\"$imageSrc\" class='eslegCardDetailsImage'><p/>";
		$output .= "<table class='eslegCardDetailsTable'>";
		
		$typeList = $this->CreateEditListOutput(self::$LEGENDS_TYPES, $card['type'], "Type");
		$raceList = $this->CreateEditListOutput(self::$LEGENDS_SUBTYPES, $card['subtype'], "Subtype");
		$attr1List = $this->CreateEditListOutput(self::$LEGENDS_ATTRIBUTES, $card['attribute'], "Attribute1");
		$attr2List = $this->CreateEditListOutput(self::$LEGENDS_ATTRIBUTES, $card['attribute2'], "Attribute2");
		$attr3List = $this->CreateEditListOutput(self::$LEGENDS_ATTRIBUTES, $card['attribute3'], "Attribute3");
		$classList = $this->CreateEditListOutput(self::$LEGENDS_CLASSES, $card['class'], "Class");
		$setList = $this->CreateEditListOutput(self::$LEGENDS_SETS, $card['set'], "Set");
		$rarityList = $this->CreateEditListOutput(self::$LEGENDS_RARITIES, $card['rarity'], "Rarity");
		
		if ($this->inputCreateCard)
		{
			$output .= "<tr><th>Name</th><td><input type='text' value=\"$name\" name='name' id='eslegCardInputName' maxlength='100'> <small>Must be unique.</small></td></tr>";
		}
		else
		{
			$output .= "<tr><th>Name</th><td>$name</td></tr>";
		}
		
		$output .= "<tr><th>Type</th><td>$typeList</td></tr>";
		$output .= "<tr><th>Race</th><td>$raceList</td></tr>";
		$output .= "<tr><th>Magicka</th><td><input type='text' value='$magicka' name='magicka' id='eslegCardInputMagicka' maxlength='10'></td></tr>";
		$output .= "<tr><th>Power</th><td><input type='text' value='$power' name='power' id='eslegCardInputPower' maxlength='10'></td></tr>";
		$output .= "<tr><th>Health</th><td><input type='text' value='$health' name='health' id='eslegCardInputHealth' maxlength='10'></td></tr>";
		$output .= "<tr><th>Attribute 1</th><td>$attr1List</td></tr>";
		$output .= "<tr><th>Attribute 2</th><td>$attr2List</td></tr>";
		$output .= "<tr><th>Attribute 3</th><td>$attr3List</td></tr>";
		$output .= "<tr><th>Class</th><td>$classList</td></tr>";
		$output .= "<tr><th>Set</th><td>$setList</td></tr>";
		$output .= "<tr><th>Rarity</th><td>$rarityList</td></tr>";
		$output .= "<tr><th>Obtainable</th><td><input type='checkbox' name='obtainable' value='1' id='eslegCardInputObtainable' $obtainable></td></tr>";
		$output .= "<tr><th>Unique</th><td><input type='checkbox' name='unique' value='1' id='eslegCardInputUnique' $unique></td></tr>";
		//$output .= "<tr><th>Training 1</th><td><input type='text' name='training1' value='$training1' id='eslegCardInputTraining1'> @ Level <input type='text' name='trainingLevel1' value='$trainingLevel1' id='eslegCardInputTrainingLevel1'></td></tr>";
		//$output .= "<tr><th>Training 2</th><td><input type='text' name='training2' value='$training2' id='eslegCardInputTraining2'> @ Level <input type='text' name='trainingLevel2' value='$trainingLevel2' id='eslegCardInputTrainingLevel2'></td></tr>";
		$output .= "<tr><th>Uses</th><td><input type='text' value='$uses' name='uses' id='eslegCardInputUses' maxlength='100'></td></tr>";
		$output .= "<tr><th>Text</th><td><textarea name='text' id='eslegCardInputText'>$text</textarea></td></tr>";
		$output .= "<tr><th>Wiki Image</th><td><input type='text' value=\"$imageName\" name='image' id='eslegCardInputImage' maxlength='100'> </td></tr>";
		
		$output .= "<tr class='eslegCardRowSave'><td colspan='2' class='eslegCardRowSave'><input type='submit' value='Save'></td></tr>";
		$output .= "</table>";
		$output .= "</form>";
		
		return $output;
	}
	
	
	public function GetCardRenameOutput()
	{
		$safeName = $this->Escape($this->inputRenameCard);
		$card = $this->singleCardData;
		
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		$name = $this->Escape($card['name']);
		
		$output = "<p>Renaming card.<p/>";
		
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='save'>";
		$output .= "<input type='hidden' value=\"$name\" name='rename'>";
		
		$output .= "<table class='eslegCardDetailsTable'>";
		$output .= "<tr><th>Current Name</th><td>$name</td></tr>";
		$output .= "<tr><th>New Name</th><td><input type='text' value=\"$name\" name='name' id='eslegCardInputName' maxlength='100'> <small>Must not currently exist.</small></td></tr>";
		$output .= "<tr class='eslegCardRowSave'><td colspan='2' class='eslegCardRowSave'><input type='submit' value='Rename'></td></tr>";
		$output .= "</table>";
		$output .= "</form>";
	
		return $output;
	}
	
	
	public function GetCardDeleteOutput()
	{
		$safeName = $this->EscapeAttribute($this->inputDeleteCard);
		$card = $this->singleCardData;
				
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		$name = $this->EscapeAttribute($card['name']);
		$safeAttr = urlencode($card['name']);
		
		$output = "";
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='save'>";
		$output .= "<input type='hidden' value=\"$name\" name='delete'>";
		
		$output .= "<br/>This will permanently delete the '<em>$name</em>' card!<br/>Are you sure you wish to proceed?<p/>";
		$output .= "<input type='submit' value='Yes, Delete It!' class='eslegCardDeleteButton'> &nbsp; ";
		$output .= "<button type='cancel' test='1' onclick=\"window.location='/wiki/Special:LegendsCardData?card=$safeAttr';return false;\">No, Cancel!</button> ";
		$output .= "</form>";
	
		return $output;
	}
	
	
	public function GetCardDetailsOutput()
	{
		$output = "";
		$safeName = $this->Escape($this->inputCardName);
		$card = $this->singleCardData;
		
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		$output .= "Showing data for card $safeName.";		
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$attribute3 = $this->Escape($card['attribute3']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$unique = $card['unique'];
		$training1 = $card['training1'];
		$training2 = $card['training2'];
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$training = "";
		if ($training1) $training .= $this->GetCardLink($training1) . " @ Lvl $trainingLevel1";
		if ($training2) $training .= "<br/>". $this->GetCardLink($training2) . " @ Lvl $trainingLevel2";
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href=\"/wiki/File:$image\">$imageName</a>";
		$imageSrc = "//legends.uesp.net/$name.png";
		
		$encodeName = urlencode($card['name']);
		$wikiLink = "<a href=\"/wiki/Legends:$name\">Legends:$name</a>";
				
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "No";
		
		if ($unique == 1)
			$unique = "Yes";
		else
			$unique = "No";

		$text = str_replace("\n", "<br/>", $text);
		
		$output .= "<img src=\"$imageSrc\" class='eslegCardDetailsImage'><p/>";
		$output .= "<table class='eslegCardDetailsTable'>";
		
		if ($this->CanEditCard())
		{
			$safeName = urlencode($card['name']);
			$output .= "<tr class='eslegCardRowSave'><td colspan='2' class='eslegCardDetailsEditRow'>";
			$output .= "<a href='/wiki/Special:LegendsCardData?delete=$safeName' class='eslegCardEditWarn'>Delete</a> &nbsp; ";
			$output .= "<a href='/wiki/Special:LegendsCardData?rename=$safeName' class='eslegCardEditWarn'>Rename</a> &nbsp; ";
			$output .= "<a href='/wiki/Special:LegendsCardData?edit=$safeName'>Edit Card</a>";
			$output .= "</td></tr>";
		}
		
		$output .= "<tr><th>Name</th><td>$name</td></tr>";
		$output .= "<tr><th>Type</th><td>$type</td></tr>";
		$output .= "<tr><th>Race</th><td>$subtype</td></tr>";
		$output .= "<tr><th>Magicka</th><td>$magicka</td></tr>";
		$output .= "<tr><th>Power</th><td>$power</td></tr>";
		$output .= "<tr><th>Health</th><td>$health</td></tr>";
		$output .= "<tr><th>Attribute 1</th><td>$attribute1</td></tr>";
		$output .= "<tr><th>Attribute 2</th><td>$attribute2</td></tr>";
		$output .= "<tr><th>Attribute 3</th><td>$attribute3</td></tr>";
		$output .= "<tr><th>Class</th><td>$class</td></tr>";
		$output .= "<tr><th>Set</th><td>$set</td></tr>";
		$output .= "<tr><th>Rarity</th><td>$rarity</td></tr>";
		$output .= "<tr><th>Obtainable</th><td>$obtainable</td></tr>";
		$output .= "<tr><th>Unique</th><td>$unique</td></tr>";
		//$output .= "<tr><th>Training</th><td>$training</td></tr>";
		$output .= "<tr><th>Uses</th><td>$uses</td></tr>";
		$output .= "<tr><th>Text</th><td>$text</td></tr>";
		$output .= "<tr><th>Wiki Link</th><td>$wikiLink</td></tr>";
		$output .= "<tr><th>Wiki Image</th><td>$imageLink</td></tr>";		
				
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function GetCardTableOutput()
	{	
		$output = "";
		$cardCount = count($this->cards);
		
		$output .= $this->GetCardFilterOutput();
				
		if ($this->CanCreateCard())
		{
			$output .= "<div class='eslegCardCreate'><a href='/wiki/Special:LegendsCardData?editdisamb=1'>Edit Disambiguation</a> &nbsp; <a href='/wiki/Special:LegendsCardData?editsets=1'>Edit Sets</a> &nbsp; <a href='/wiki/Special:LegendsCardData?create=1'>Create Card</a></div>";
		}
		
		if ($cardCount != $this->totalCardCount)
			$output .= "Showing data for $cardCount of {$this->totalCardCount} matching cards.<p/>";
		else
			$output .= "Showing data for $cardCount cards.<p/>";
		
		$output .= "<table class='eslegCardDataTable'>";
		$output .= "<tr>";
		$output .= "<th>Card</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Race</th>";
		$output .= "<th>Magicka</th>";
		$output .= "<th>Power</th>";
		$output .= "<th>Health</th>";
		$output .= "<th>Attribute</th>";
		$output .= "<th>Class</th>";
		$output .= "<th>Set</th>";
		$output .= "<th>Rarity</th>";
		$output .= "<th>Obtainable</th>";
		$output .= "<th>Unique</th>";
		//$output .= "<th>Training</th>";
		$output .= "<th>Uses</th>";
		$output .= "<th>Description</th>";
		$output .= "<th>Links</th>";
		$output .= "</tr>";
				
		foreach ($this->cards as $name => $card)
		{
			$output .= $this->GetCardOutputRow($card);
		}
		
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function UpdateCardImage($name, $image)
	{
		if ($image == null || $image == "") return true;
		
		$result = CreateLegendsPopupImage($name, $image, "/mnt/uesp/legendscards/");
		
		if (!$result)
		{
			$this->errorMsg = "Failed to update card popup image!";
			return true;
		}
		
		return true;
	}
	
	
	public function GetFullImagePath($inputImage)
	{
		if ($inputImage == null || $inputImage == "") return "";
		
		$image = "";
		$imageBase = $inputImage;
		$imageBase = preg_replace("# #", "_", $imageBase);
		$imageHash = GetLegendsImagePathHash($imageBase);
		if ($imageBase != "") $image = "/" . $imageHash . $imageBase;
		
		return $image;
	}
	
	
	public function SaveCard()
	{
		if (!$this->InitDatabaseWrite()) return false;
		
		$name = $this->db->real_escape_string($this->inputCardName);
		$type = $this->db->real_escape_string($this->inputCardData['type']);
		$subtype = $this->db->real_escape_string($this->inputCardData['subtype']);
		$text = $this->db->real_escape_string($this->inputCardData['text']);
		
		$image = $this->GetFullImagePath($this->inputCardData['image']);
		$safeImage = $this->db->real_escape_string($image);
		
		$class = $this->db->real_escape_string($this->inputCardData['class']);
		$set = $this->db->real_escape_string($this->inputCardData['set']);
		$rarity = $this->db->real_escape_string($this->inputCardData['rarity']);
		$uses = $this->db->real_escape_string($this->inputCardData['uses']);
		$attribute1 = $this->db->real_escape_string($this->inputCardData['attribute']);
		$attribute2 = $this->db->real_escape_string($this->inputCardData['attribute2']);
		$attribute3 = $this->db->real_escape_string($this->inputCardData['attribute3']);
		$training1 = $this->db->real_escape_string($this->inputCardData['training1']);
		$training2 = $this->db->real_escape_string($this->inputCardData['training2']);
		$trainingLevel1 = $this->inputCardData['trainingLevel1'];
		$trainingLevel2 = $this->inputCardData['trainingLevel2'];
		$obtainable = $this->inputCardData['obtainable'];
		$unique = $this->inputCardData['unique'];
		$magicka = $this->inputCardData['magicka'];
		$power = $this->inputCardData['power'];
		$health = $this->inputCardData['health'];
		
		$cardExists = $this->DoesCardExist($this->inputCardName);
		
		if ($this->inputCreateCard)
		{
			if ($cardExists) 
			{
				$this->errorMsg = "The card '{$this->inputCardName}' already exists!";
				return false;
			}
			
			$query = "INSERT INTO cards SET ";
			$query .= " name='$name',";
		}
		else
		{
			if (!$cardExists) 
			{
				$this->errorMsg = "The card '{$this->inputCardName}' does not exist!";
				return false;
			}
			
			$query = "UPDATE cards SET ";
		}
		
		$query .= " type='$type',";
		$query .= " subtype='$subtype',";
		$query .= " magicka='$magicka',";
		$query .= " power='$power',";
		$query .= " health='$health',";
		$query .= " uses='$uses',";
		$query .= " attribute='$attribute1',";
		$query .= " attribute2='$attribute2',";
		$query .= " attribute3='$attribute3',";
		$query .= " `class`='$class',";
		$query .= " `set`='$set',";
		$query .= " rarity='$rarity',";
		$query .= " text='$text',";
		$query .= " image='$safeImage',";
		$query .= " obtainable='$obtainable',";
		$query .= " `unique`='$unique',";
		$query .= " training1='$training1',";
		$query .= " training2='$training2',";
		$query .= " trainingLevel1='$trainingLevel1',";
		$query .= " trainingLevel2='$trainingLevel2'";
		
		if (!$this->inputCreateCard) $query .= " WHERE name='$name'";
		$query .= ";";
		
		$result = $this->db->query($query);
		if ($result === false) return false;
		
		return $this->UpdateCardImage($this->inputCardName, $image);
	}
	
	
	public function ShowCardFilters()
	{
		return $this->inputCardData['filter'] > 0;
	}
	
	
	public function GetCardFilterListInput($list, $currentValue, $name)
	{
		$output = "<select name='$name' id='eslegCardFilterList$name' class='eslegCardFilterList'>";
		$output .= "<option value=''>Any</option>";
		
		foreach ($list as $i => $value)
		{
			$selected = "";
			if ($value == $currentValue) $selected = "selected";
			
			$output .= "<option value=\"$value\" $selected>$value</option>";
		}		
		
		$output .= "</select>";
		return $output;
	}
	
	
	public function GetCardFilterOutput()
	{
		$this->LoadSetData();
		
		$filterContentDisplay = "none";
		$arrowChar = "&#x25BC";
		
		if ($this->ShowCardFilters()) 
		{
			$filterContentDisplay = "block";
			$arrowChat = "&#x25B2;";
		}
		
		$inputName = $this->Escape($this->inputCardData['text']);
		$inputMinMagicka = $this->Escape($this->inputCardData['minMagicka']);
		$inputMaxMagicka = $this->Escape($this->inputCardData['maxMagicka']);
		$inputMinPower = $this->Escape($this->inputCardData['minPower']);
		$inputMaxPower = $this->Escape($this->inputCardData['maxPower']);
		$inputMinHealth = $this->Escape($this->inputCardData['minHealth']);
		$inputMaxHealth = $this->Escape($this->inputCardData['maxHealth']);
		
		$output = "<div id='eslegCardFilterRoot'>";
		$output .= "<div class='eslegCardFilterTitle'>Filter Cards<div id='eslegCardFilterArrow'>$arrowChar</div></div>";
		$output .= "<div id='eslegCardFilterContent' style='display: $filterContentDisplay;'>";
		
		$output .= "<form method='get' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' class='eslegCardFilterInput' name='filter' value='1'>";
		
		$output .= "<div class='eslegCardFilterLabel'>Text</div><input type='text' class='eslegCardFilterInput' name='text' maxlength='32' value=\"$inputName\"> <div class='eslegCardFilterNote'>Find text in both the card name and description.</div> <br/>";
		
		$output .= "<div class='eslegCardFilterLabel'>Type</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_TYPES, $this->inputCardData['type'], 'type');
				
		$output .= "<div class='eslegCardFilterLabel'>Rarity</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_RARITIES, $this->inputCardData['rarity'], 'rarity');
		$output .= "<br/>";
				
		$output .= "<div class='eslegCardFilterLabel'>Race</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_SUBTYPES, $this->inputCardData['subtype'], 'race');
		
		$output .= "<div class='eslegCardFilterLabel'>Class</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_CLASSES, $this->inputCardData['class'], 'class');
		$output .= "<br/>";
		
		$output .= "<div class='eslegCardFilterLabel'>Attribute</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_ATTRIBUTES, $this->inputCardData['attribute'], 'attribute');
						
		$output .= "<div class='eslegCardFilterLabel'>Set</div>";
		$output .= $this->GetCardFilterListInput(self::$LEGENDS_SETS, $this->inputCardData['set'], 'set');
		$output .= "<br/>";		
		
		$output .= "<div class='eslegCardFilterLabel'>Magicka</div>";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='minMagicka' maxlength='4' value=\"$inputMinMagicka\" placeholder='min'>";
		$output .= " to ";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='maxMagicka' maxlength='4' value=\"$inputMaxMagicka\" placeholder='max'>";
		
		$output .= "<div class='eslegCardFilterLabel'>Power</div>";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='minPower' maxlength='4' value=\"$inputMinPower\" placeholder='min'>";
		$output .= " to ";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='maxPower' maxlength='4' value=\"$inputMaxPower\" placeholder='max'>";
		$output .= "<br/>";
		
		$output .= "<div class='eslegCardFilterLabel'>Health</div>";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='minHealth' maxlength='4' value=\"$inputMinHealth\" placeholder='min'>";
		$output .= " to ";
		$output .= "<input type='text' class='eslegCardFilterInputShort' name='maxHealth' maxlength='4' value=\"$inputMaxHealth\" placeholder='max'>";
		$output .= "<br/>";
		
		$output .= "<div class='eslegCardFilterLabel'>Obtainable</div>";
		$output .= "<select class='eslegCardFilterList' name='obtainable' id='eslegCardFilterListobtainable'>";
		$selectAny = $this->inputCardData['obtainable'] === '' ? "selected" : "";
		$selectYes = $this->inputCardData['obtainable'] === 1 ? "selected" : "";
		$selectNo = $this->inputCardData['obtainable'] === 0 ? "selected" : "";
		$output .= "<option value='' $selectAny>Any</option>";
		$output .= "<option value='1' $selectYes>Yes</option>";
		$output .= "<option value='0' $selectNo>No</option>";
		$output .= "</select>";
				
		$output .= "<div class='eslegCardFilterLabel'>Unique</div>";
		$output .= "<select class='eslegCardFilterList' name='unique' id='eslegCardFilterListunique'>";
		$selectAny = $this->inputCardData['unique'] === '' ? "selected" : "";
		$selectYes = $this->inputCardData['unique'] === 1 ? "selected" : "";
		$selectNo = $this->inputCardData['unique'] === 0 ? "selected" : "";
		$output .= "<option value='' $selectAny>Any</option>";
		$output .= "<option value='1' $selectYes>Yes</option>";
		$output .= "<option value='0' $selectNo>No</option>";
		$output .= "</select>";
		$output .= "<br/>";
				
		$output .= "<input type='submit' value='Search'>";
		$output .= " &nbsp; &nbsp; <input type='button' value='Reset' onclick='OnLegendsCardFormReset();'>";
				
		$output .= "</form>";
		$output .= "</div>";
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetCardSaveRenameOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		if (!$this->InitDatabaseWrite()) return "Database error!";
		
		if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
		
		$output = "";
		
		$origName = $this->inputRenameCard;
		$newName = $this->inputCardName;
		$safeOrigName = $this->Escape($origName);
		$safeNewName = $this->Escape($newName);
		
		if ($this->DoesCardExist($newName))
		{
			$output .= "<div class='eslegCardRenameWarning'>Error: Could not rename card '<em>$safeOrigName</em>'! The card '<em>$safeNewName</em>' already exists!</div>";
			$this->LoadCardData();
			$output .= $this->GetCardRenameOutput();
			return $output;
		}
		
		$safeNewNameDB = $this->db->real_escape_string($newName);
		$safeOrigNameDB = $this->db->real_escape_string($origName);
		$query = "UPDATE cards SET name='$safeNewNameDB' WHERE name='$safeOrigNameDB';";
		
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to rename the card '<em>$safeOrigName</em>' to '<em>$safeNewName</em>'!<br/>" . $this->db->error;
		
		$image = $this->singleCardData['image'];
		$output .= "{$this->singleCardData['name']} : $image : $newName<br/>";
		$result = $this->UpdateCardImage($newName, $image);
		if (!$result) $output .= "Failed to update card image!<br/>";
		
		$output .= "Successfully renamed the card '<em>$safeOrigName</em>' to '<em>$safeNewName</em>'!";
		return $output;
	}
	
	
	public function GetCardSaveDeleteOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		if (!$this->InitDatabaseWrite()) return "Database error!";
		
		$name = $this->inputDeleteCard;
		$safeName = $this->Escape($name);
		$output = "";
		
		if (!$this->DoesCardExist($name))
		{
			$output .= "<div class='eslegCardRenameWarning'>Error: Could not delete card '<em>$safeName</em>' as it doesn't exist!</div>";
			return $output;
		}
		
		$safeNameDB = $this->db->real_escape_string($name);
		$query = "INSERT INTO deletedCards SELECT *, CURRENT_TIMESTAMP() FROM cards WHERE name='$safeNameDB';";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to delete the card '<em>$safeName</em>'!<br/>" . $this->db->error;
		
		$query = "DELETE FROM cards WHERE name='$safeNameDB';";
		$result = $this->db->query($query);
		if ($result === false) return "Error: Failed to delete the card '<em>$safeName</em>'!<br/>" . $this->db->error;
		
		$date = new DateTime(null, new DateTimeZone('America/New_York'));
		$timestamp = $date->format("Y-m-d G:i:s");
		$output .= "Successfully deleted the card '<em>$safeName</em> at $timestamp'! This card can be manually restored using this name and time.";
		
		return $output;
	}
	
	
	public function GetCardSaveOutput()
	{
		$output = "";
		
		if ($this->inputCreateCard && !$this->CanCreateCard()) return "You do not have permission to create card data!";
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		
		if ($this->inputCreateCard && $this->inputCardName == "")
		{
			$output .= "<b>Missing required card name!</b> ";
			
			$this->inputEditCard = $this->inputCardName;
			$this->singleCardData = $this->inputCardData;
			
			$output .= $this->GetCardEditOutput();
			
			return $output;
		}
				
		if (!$this->SaveCard())
		{
			$output .= "<b>Error saving card data!</b> " . $this->errorMsg . " ";
			if ($this->db->error) $output .= "<p>DB Error: " . $this->db->error . "<p>";
			
			$this->inputEditCard = $this->inputCardName;
			$this->singleCardData = $this->inputCardData;
			
			$output .= $this->GetCardEditOutput();
		}
		else
		{
			$this->singleCardData = $this->inputCardData;
			
			if ($this->inputCreateCard)
				$output .= "<b>Saved new card data!</b> " . $this->errorMsg . " ";
			else
				$output .= "<b>Saved card data!</b> " . $this->errorMsg . " ";
			
			$output .= $this->GetCardDetailsOutput();
		}
		
		return $output;
	}
	
	
	public function getOutput()
	{
		$output = $this->GetBreadcrumbTrail();
		
		if ($this->inputEditSets)
		{
			if ($this->inputDeleteSet)
				$output .= $this->GetCardDeleteSetOutput();
			else if ($this->inputAddSet)
				$output .= $this->GetCardAddSetOutput();
			else
				$output .= $this->GetCardEditSetsOutput();
		}
		else if ($this->inputEditDisambiguation)
		{
			if ($this->inputDeleteDisambiguation)
				$output .= $this->GetCardDeleteDisambiguationOutput();
			else if ($this->inputAddDisambiguation)
				$output .= $this->GetCardAddDisambiguationOutput();
			else
				$output .= $this->GetCardEditDisambiguationOutput();
		}
		else if ($this->inputSaveCard)
		{
			if ($this->inputRenameCard != "")
				$output .= $this->GetCardSaveRenameOutput();
			else if ($this->inputDeleteCard != "")
				$output .= $this->GetCardSaveDeleteOutput();
			else
				$output .= $this->GetCardSaveOutput();
		}
		else if ($this->inputCreateCard)
		{
			if (!$this->CanCreateCard()) return "Error: You do not have permission to create card data!";
			$this->singleCardData = $this->inputCardData;
			$output .= $this->GetCardEditOutput();
		}
		else if ($this->inputCardName != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardDetailsOutput();
		}
		else if ($this->inputEditCard != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardEditOutput();
		}
		else if ($this->inputRenameCard != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardRenameOutput();
		}
		else if ($this->inputDeleteCard != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardDeleteOutput();
		}
		else
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardTableOutput();
		}
		
		return $output;
	}
	
};
