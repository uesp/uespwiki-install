<?php

/**
 * Internationalisation file for extension TorBlock.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English
 * @author Andrew Garrett
 */
$messages['en'] = array(
	'torblock-desc'    => 'Allows tor exit nodes to be blocked from editing a wiki',
	'torblock-blocked' => 'Your IP address, <strong>$1</strong>, has been automatically identified as a tor exit node.
Editing through tor is blocked to prevent abuse.',
	'torblock-isexitnode' => 'The IP address <strong>$1</strong> is blocked as a Tor exit node.',
	'right-torunblocked' => 'Bypass automatic blocks of tor exit nodes',
	'tag-tor-description' => 'If this tag is set, an edit was made from a Tor exit node.',
	'tag-tor' => 'Made through tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Whether or not the change was made through a tor exit node',
);

/** Message documentation (Message documentation)
 * @author Fryed-peach
 * @author Mormegil
 * @author Purodha
 * @author Shirayuki
 * @author The Evil IP address
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'torblock-desc' => '{{desc|name=Tor Block|url=http://www.mediawiki.org/wiki/Extension:TorBlock}}',
	'torblock-blocked' => 'Used as error message. Paramters:
* $1 - IP address',
	'torblock-isexitnode' => 'Used as the tor blocking status message. Paramters:
* $1 - IP address',
	'right-torunblocked' => '{{doc-right|torunblocked}}

Users with this right are not affected by automatic blocking by [[mw:Extension:TorBlock|Extension:TorBlock]] (and can therefore edit using tor).',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 * @author පසිඳු කාවින්ද
 */
$messages['af'] = array(
	'torblock-desc' => "Tor afrit nodes te geblokkeer word van die wysiging van 'n wiki",
	'right-torunblocked' => 'Verbypad outomatiese blokke van Tor afrit nodes',
	'tag-tor-description' => "Indien hierdie merker is, was 'n verandering gemaak van' n Tor afrit knoop.",
	'tag-tor' => 'Gemaak met tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Of die verandering is gemaak deur middel van 'n tor afrit knoop",
);

/** Aragonese (aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'torblock-desc' => 'Premite que os nodos de salida tor sían bloqueyatos ta editar una wiki',
	'torblock-blocked' => "A suya adreza IP, <strong>$1</strong>, s'ha identificato automaticament como un nodo de salida tor.
Ye vedato d'editar con tor ta privar abusos.",
	'torblock-isexitnode' => "L'adreza IP <strong>$1</strong> ye bloquiada por estar un nodo de salida Tor.",
	'right-torunblocked' => "Privar os bloqueyos automaticos d'os nodos tor",
	'tag-tor-description' => 'Si ista marca ye present, una edición ha estau realizada por un nodo de salida Tor.',
	'tag-tor' => 'Feito por meyo de tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Si o cambeo s'ha feito u no a traviés d'un nodo de salida tor",
);

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'torblock-desc' => 'يسمح بمنع عقد خروج التور من تعديل ويكي',
	'torblock-blocked' => 'عنوان الأيبي الخاص بك، <strong>$1</strong>، تم التعرف عليه تلقائيا كعقدة خروج تور.
التعديل من خلال التور ممنوع لمنع التخريب.',
	'torblock-isexitnode' => 'عنوان الأيبي <strong>$1</strong> ممنوع كعقدة خروج تور.',
	'right-torunblocked' => 'تفادي عمليات المنع التلقائية لعقد خروج التور',
	'tag-tor-description' => 'لو أن هذا الوسم مضبوط، فإن تعديلا قد حدث من عقدة خروج تور.',
	'tag-tor' => 'معمولة من خلال تور',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'ما إذا كان التعديل قد تم عمله من خلال عقدة خروج تور',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 * @author Ramsis II
 */
$messages['arz'] = array(
	'torblock-desc' => 'بيسمح بمنع عقد خروج التور من تعديل ويكي',
	'torblock-blocked' => 'عنوان الاى بى بتاعك, <strong>$1</strong>, اتعرف عليه اوتوماتيكى كعقدة خروج تور.
التعديل عن طريق التور مقفول علشان نمنع اساءة الاستعمال.',
	'right-torunblocked' => 'اتفادى عمليات المنع الاوتوماتيكية لعقد خروج التور',
	'tag-tor-description' => 'اذا كان الوسم دا مظبوط ، ففى تعديل اتعمل من عقدة خروج Tor.',
	'tag-tor' => 'اتعمل من خلال tor',
);

/** Assamese (অসমীয়া)
 * @author Chaipau
 * @author Gitartha.bordoloi
 */
$messages['as'] = array(
	'torblock-desc' => "ট'ৰ প্ৰস্থান ন'ডক সম্পাদনাৰ পৰা বাৰণ কৰিবলৈ অনুমতি দিয়ে",
	'torblock-blocked' => "আপোনাৰ আইপি ঠিকনা <strong>$1</strong>ক স্বয়ংক্ৰিয়ভাৱে এক ট'ৰ প্ৰস্থান ন'ড হিচাপে চিনাক্ত কৰা হৈছে। খেলিমেলি ৰোধ কৰিবলৈ ট'ৰৰ পৰা সম্পাদনা বাৰণ কৰা হৈছে।",
	'torblock-isexitnode' => "আইপি ঠিকনা <strong>$1</strong>ক ট'ৰ প্ৰস্থান ন'ড হিচাপে বাৰণ কৰা হৈছে।",
	'right-torunblocked' => "ট'ৰ প্ৰস্থান ন'ডৰ স্বয়ংক্ৰিয় প্ৰতিবন্ধক এৰাই চলক",
	'tag-tor-description' => "এই টেগ্‌টো ছেট কৰিলে ট'ৰ প্ৰস্থান ন'ডৰপৰা এটা সম্পাদনা কৰা হৈছিল।",
	'tag-tor' => "ট'ৰ দ্বাৰা নিৰ্মিত",
	'abusefilter-edit-builder-vars-tor-exit-node' => "ট'ৰ প্ৰস্থান ন'ডৰ পৰা এই সালসলনি কৰা হৈছিল নে নাই",
);

/** Asturian (asturianu)
 * @author Esbardu
 * @author Xuacu
 */
$messages['ast'] = array(
	'torblock-desc' => "Permite que los nodos de salida tor torguen la edición d'una wiki",
	'torblock-blocked' => "La to direición IP, <strong>$1</strong>, foi identificada automáticamente como un nodiu de salida tor.
La edición al traviés de tor ta bloquiada pa prevenir l'abusu.",
	'torblock-isexitnode' => 'La dirección IP <strong>$1</strong> ta bloquiada por ser un nodiu de salida Tor.',
	'right-torunblocked' => 'Evita los bloqueos automáticos de los nodos de salida tor',
	'tag-tor-description' => 'Si tien puesta esta etiqueta, ye que se fizo una edición dende un nodiu de salida Tor.',
	'tag-tor' => 'Fecho al traviés de Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Si ta fechu'l cambiu al traviés d'un nodiu de salida tor",
);

/** Bashkir (башҡортса)
 * @author Assele
 */
$messages['ba'] = array(
	'torblock-desc' => 'Tor селтәренең сығыу төйөндәрен викины мөхәррирләүҙән бикләргә мөмкинлек бирә',
	'torblock-blocked' => 'Һеҙҙең IP адресығыҙ, <strong>$1</strong>, үҙенән-үҙе Tor селтәренең сығыу төйөнө тип билдәләнде.
Tor аша мөхәррирләү урынһыҙ ҡулланыуҙы булдырмау маҡсатында тыйылған.',
	'torblock-isexitnode' => '<strong>$1</strong> IP адресы Tor селтәренең сығыу төйөнө булараҡ бикләнгән.',
	'right-torunblocked' => 'Tor селтәренең сығыу төйөндәрен бикләүҙе урап үтеү',
	'tag-tor-description' => 'Әгәр был билдә ҡуйылған булһа, мөххәрирләү Tor селтәренең сығыу төйөнөнән башҡарылған.',
	'tag-tor' => 'Tor аша башҡарылған',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Мөххәрирләү Tor селтәренең сығыу төйөнөнән башҡарылғанмы, юҡмы',
);

/** Southern Balochi (بلوچی مکرانی)
 * @author Mostafadaneshvar
 */
$messages['bcc'] = array(
	'torblock-desc' => 'اجازت دن په در بیگ گرهنان په محدود بوتن چه اصلاح یک ویکیء',
	'torblock-blocked' => 'شمی آدرس آی پی, <strong>$1</strong>,اتوماتیکی په داب یک گرهن خروجی سنگ نشان بوتت.
اصلاح کتن چه طرق سنگ(tor) په خاطر جلوگرگ سوء استفاده بند بوتت.',
);

/** Bikol Central (Bikol Central)
 * @author Geopoet
 */
$messages['bcl'] = array(
	'torblock-desc' => 'Minatugot kan tor na mga boko nin pagluwas tanganing pagkukubkubon sa pagliliwat nin sarong wiki',
	'torblock-blocked' => 'An saimong estada kan IP, <strong>$1</strong>, awtomatikong pinagbibisto bilang sarong tor na boko nin pagluwas.
An pagliliwat sa paagi kan tor pinagkubkob na tanganing maprebentaran an abuso.',
	'torblock-isexitnode' => 'An estada kan IP na <strong>$1</strong> pinagkubkob bilang sarong Tor na boko nin pagluwas.',
	'right-torunblocked' => 'Sampawan an awtomatikong mga kubkob kan tor na mga boko nin pagluwas',
	'tag-tor-description' => 'Kun ining tatak an kinaag, sarong pagliwat pinaghimo gikan sa sarong Tor na boko nin pagluwas.',
	'tag-tor' => 'Hinimo sa paagi kan tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Nangyari man o bako an kaliwatan hinimo sa paagi kan tor na boko nin pagluwas',
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author Cesco
 * @author EugeneZelenko
 * @author Jim-by
 * @author Red Winged Duck
 */
$messages['be-tarask'] = array(
	'torblock-desc' => 'Дазваляе блякаваць магчымасьць рэдагаваньняў для ананімных карыстальнікаў, якія карыстаюцца сеткай Tor',
	'torblock-blocked' => 'Ваш ІР-адрас <strong>$1</strong> быў аўтаматычна ідэнтыфікаваны як выхадны вузел сеткі Tor.
Рэдагаваньне праз Тor заблякаванае для прадухіленьня злоўжываньняў.',
	'torblock-isexitnode' => 'IP-адрас <strong>$1</strong> заблякаваны, таму што адносіцца да сеткі Tor.',
	'right-torunblocked' => 'Абыход аўтаматычнага блякаваньня вузлоў сеткі Tor',
	'tag-tor-description' => 'Калі гэты тэг быў дададзены, рэдагаваньне было зроблена праз выходны вузел Tor.',
	'tag-tor' => 'Зроблена праз Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Ці рэдагаваньне адбылася праз вузел Tor',
);

/** Bulgarian (български)
 * @author Spiritia
 * @author Turin
 */
$messages['bg'] = array(
	'torblock-desc' => 'Позволява да се блокира редактирането от изходни възли на TOR-мрежа',
	'torblock-blocked' => 'Вашият IP-адрес, <strong>$1</strong>, е бил автоматично идентифициран като изходен възел на TOR-мрежа.
Редактирането през такива адреси се ограничава с цел предотвратяване на евентуални злоупотреби.',
	'torblock-isexitnode' => 'IP адресът <strong>$1</strong> е блокиран като изходящ възел на Tor мрежа.',
	'right-torunblocked' => 'Пренебрегване автоматичните блокирания на изходните възли на tor',
	'tag-tor' => 'Направено през Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Дали промяната е направена от адрес, явяващ се изходен възел на Tor',
);

/** Breton (brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'torblock-desc' => 'Talvezout a ra da stankañ kemmoù ur wiki adalek skoulmoù ezmont Tor',
	'torblock-blocked' => "Detektet eo bet ho chomlec'h IP <strong>$1</strong> ent emgefre evel ur skoulm ezmont Tor.
Stnaket eo ar c'hemmoù dre Tor, kuit da zegas trubuilh.",
	'torblock-isexitnode' => "Stanket eo ar chomlec'h UP <strong>$1</strong> rak ur skoulm ermaeziañ Tor eo.",
	'right-torunblocked' => 'Tremen dreist da stankadennoù emgefre skoulmoù ezmont Tor',
	'tag-tor-description' => "Mard eo gweredekaet ar valizenn-mañ, ez eus bet degaset ur c'hemm adalek ur skoulm ezmont Tor.",
	'tag-tor' => 'Graet dre Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Mard eo bet graet ur c'hemm adalek ur skolm ezmont Tor pe get",
);

/** Bosnian (bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'torblock-desc' => 'Omogućuje da se blokiraju tor izlazni čvorovi za uređivanje wikija',
	'torblock-blocked' => 'Vaša IP adresa, <strong>$1</strong>, je automatski otkrivena i označena kao tor izlazni čvor.
Uređivanje preko tora je blokirano da bi se spriječila zloupotreba.',
	'torblock-isexitnode' => 'IP adresa <strong>$1</strong> je blokirana kao Tor izlazni čvor.',
	'right-torunblocked' => 'Zaobilaženje automatskih blokada tor izlaznih čvorova',
	'tag-tor-description' => 'Ako je ova oznaka postavljenja, izmjena je načinjena od izlaznog Tor čvora.',
	'tag-tor' => 'Napravljeno kroz tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Da li je izmjena izvršena kroz tor izlazni čvor ili ne',
);

/** Catalan (català)
 * @author El libre
 * @author SMP
 * @author Vriullop
 */
$messages['ca'] = array(
	'torblock-desc' => "Permet que els nodes de sortida tor siguin bloquejats des de l'edició d'un wiki",
	'torblock-blocked' => "La vostra adreça IP <strong>$1</strong> ha estat identificada automàticament com un node de sortida de la xarxa Tor. L'edició a través de Tor està prohibida per a prevenir abusos.",
	'torblock-isexitnode' => "L'adreça IP <strong>$1</strong> és bloquejada com a node de sortida Tor.",
	'right-torunblocked' => 'Evitar els blocatges automàtics de nodes Tor',
	'tag-tor-description' => "Si aquesta etiqueta s'estableix, una edició va ser feta des d'un node de sortida Tor.",
	'tag-tor' => 'Realitzat a través de tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Si el canvi ha estat fet, o no, a través d'un node de sortida tor",
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'right-torunblocked' => 'Tor олучу зӀен шедан автоматически блоктохаран чекхбовлар',
	'tag-tor-description' => 'ХӀара билгало хӀоттина елахь нисдарш дина Tor гӀонца.',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Нисдар Tor тӀера дина дуй',
);

/** Sorani Kurdish (کوردی)
 * @author Calak
 */
$messages['ckb'] = array(
	'right-torunblocked' => 'لادان لە بەربەستنە خۆگەڕەکانی گرێکانی دەرچووی تۆر',
);

/** Czech (česky)
 * @author Mormegil
 */
$messages['cs'] = array(
	'torblock-desc' => 'Umožňuje blokovat editace pocházející z výstupních uzlů sítě Tor',
	'torblock-blocked' => 'Vaše IP adresa (<strong>$1</strong>) byla automaticky rozpoznána jako výstupní uzel sítě Tor.
Editace prostřednictvím sítě Tor je kvůli prevenci zneužití zablokována.',
	'torblock-isexitnode' => 'IP adresa <strong>$1</strong> je zablokována jako výstupní uzel sítě Tor.',
	'right-torunblocked' => 'Obcházení automatického blokování výstupních uzlů sítě Tor',
	'tag-tor-description' => 'Pokud je nastavena tato značka, byla editace provedena z výstupního uzlu sítě Tor.',
	'tag-tor' => 'Prostřednictvím Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Zda byla tato editace provedena z výstupního uzlu sítě Tor',
);

/** Danish (dansk)
 * @author Masz
 * @author Peter Alberti
 */
$messages['da'] = array(
	'torblock-desc' => 'Gør det muligt at blokere Tor udgangsknuder fra at redigere en wiki',
	'torblock-blocked' => 'Din IP-adresse, <strong>$1</strong>, er automatisk blevet identificeret som en udgangsknude fra Tor.
Redigering gennem Tor er blokeret for at forhindre misbrug.',
	'torblock-isexitnode' => 'IP-adressen <strong>$1</strong> er blokeret som en Tor udgangsknude.',
	'right-torunblocked' => 'Redigere fra blokerede TOR-noder',
	'tag-tor-description' => 'Hvis dette mærkat er angivet, blev en redigering udført via en Tor udgangsknude.',
	'tag-tor' => 'Foretaget gennem tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Hvorvidt ændringen blev foretaget gennem en Tor udgangsknude',
);

/** German (Deutsch)
 * @author Kghbln
 * @author Raimond Spekking
 * @author Umherirrender
 */
$messages['de'] = array(
	'torblock-desc' => 'Ermöglicht das Sperren des Schreibzugriffs über Tor-Ausgangsknoten',
	'torblock-blocked' => 'Deine IP-Adresse <strong>$1</strong> wurde automatisch als Tor-Ausgangsknoten identifiziert. Die Seitenbearbeitung in Verbindung mit dem Tor-Netzwerk ist unerwünscht, da die Missbrauchsgefahr sehr hoch ist.',
	'torblock-isexitnode' => 'Die IP-Adresse <strong>$1</strong> ist als Tor-Ausgangsknoten gesperrt.',
	'right-torunblocked' => 'Umgehung der automatischen Sperre von Tor-Ausgangsknoten',
	'tag-tor-description' => 'Wenn dieses Tag gesetzt ist, erfolgte die Bearbeitung durch einen Tor-Ausgangsknoten.',
	'tag-tor' => 'Durch einen Tor-Ausgangsknoten',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Änderung erfolgte durch einen Torausgangsknoten',
);

/** German (formal address) (Deutsch (Sie-Form)‎)
 * @author MichaelFrey
 */
$messages['de-formal'] = array(
	'torblock-blocked' => 'Ihre IP-Adresse <strong>$1</strong> wurde automatisch als Tor-Ausgangsknoten identifiziert. Die Seitenbearbeitung in Verbindung mit dem Tor-Netzwerk ist unerwünscht, da die Missbrauchsgefahr sehr hoch ist.',
);

/** Zazaki (Zazaki)
 * @author Aspar
 * @author Mirzali
 * @author Xoser
 */
$messages['diq'] = array(
	'torblock-desc' => 'nêrvedano nodê teberbiyayişê tori, wiki bıvurn',
	'torblock-blocked' => 'IP adresa şıma, <strong>$1</strong>, nodê otomotoik hesıbya.
qey ver-gırewtışê suistimali tor ser o vurnayiş qedexe biy.',
	'torblock-isexitnode' => 'IP-adresa <strong>$1</strong>i zey yew gırey veciyayışiê Tori kılit bena.',
	'right-torunblocked' => 'otomatik ver-gırewtışê nodê tori raviyer',
	'tag-tor-description' => 'eke no sername eyar nêbiyo, vurnayiş nod ser o biyo.',
	'tag-tor' => 'serê tor ra vıraziyayo',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'serê nodê tor ra biyo nê nêbiyo',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'torblock-desc' => 'Zajźujo, aby wuchadne suki TOR wobźěłowali wiki',
	'torblock-blocked' => 'Twója IP-adresa, <strong>$1</strong>, jo se awtomatiski awtentificěrowała ako wuchadny suk TOR.
Wobźěłowanje pśez TOR jo blokěrowane, aby zajźowało znjewužiwanju.',
	'torblock-isexitnode' => 'IP-adresa <strong>$1</strong> jo ako wuchadny suk Tor zablokěrowana.',
	'right-torunblocked' => 'Awtomatiske blokěrowanja wuchadnych sukow TOR sw wobinuś',
	'tag-tor-description' => 'Jolic toś ta toflicka jo stajona, jo se změna z wuchadnego modusa TOR pśewjadła.',
	'tag-tor' => 'Pśez TOR pśewjeźony',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Lěc změna jo se cyniła pśez wuchadny suk TOR abo nic',
);

/** Greek (Ελληνικά)
 * @author Dead3y3
 * @author Glavkos
 */
$messages['el'] = array(
	'torblock-desc' => 'Επιτρέπει να φραγούν κόμβοι εξόδου tor από το να επεξεργράζονται ένα wiki',
	'torblock-blocked' => 'Η διεύθυνση IP σας, <strong>$1</strong>, ταυτοποιήθηκε αυτόματα ως ένας κόμβος εξόδου tor. Η επεξεργασία μέσω tor έχει φραγεί για την αποτροπή κατάχρησης.',
	'torblock-isexitnode' => 'Η διεύθυνση IP <strong>$1</strong> είναι αποκλεισμένη ως κόμβος εξόδου Tor.',
	'right-torunblocked' => 'Παράκαμψη αυτομάτων φραγών για κόμβους εξόδου tor',
	'tag-tor-description' => 'Αν αυτή η ετικέτα έχει τεθεί, μια επεξεργασία έγινε από έναν κόμβο εξόδου Tor.',
	'tag-tor' => 'Έγινε μέσω Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Ανεξάρτητα από το αν η αλλαγή έγινε διαμέσου ενός κόμβου εξόδου tor',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'torblock-desc' => 'Permesas por elignodoj esti forbaritaj kontraŭ redaktado de vikio',
	'torblock-blocked' => "Via IP-adreso, <strong>$1</strong> estis aŭtomate identigita kiel elignodo ''tor''.
Redaktado per ''tor'' estas forbarita por preventi misuzo.",
	'torblock-isexitnode' => 'La IP-adreso <strong>$1</strong> estas forbarita kiel Tor elira pordo.',
	'right-torunblocked' => "Preterpasi aŭtomatajn blokojn de elignodoj ''tor''.",
	'tag-tor-description' => 'Se ĉi tiu etikedo estas ŝalta, redakto estis farita de Tor-a elira nodo.',
	'tag-tor' => 'Farita per tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se la ŝanĝo estis farita per elira nodo de tor aŭ ne',
);

/** Spanish (español)
 * @author Crazymadlover
 * @author Dferg
 * @author MarcoAurelio
 * @author Remember the dot
 * @author Sanbec
 */
$messages['es'] = array(
	'torblock-desc' => 'Permite bloquear nodos de salida tor',
	'torblock-blocked' => 'Su dirección IP, <strong>$1</strong>, ha sido identificada automáticamente como un nodo de salida tor.
Se bloquea editar por tor para prevenir abusos.',
	'torblock-isexitnode' => 'La dirección IP <strong>$1</strong> está bloqueada por ser un nodo de salida Tor.',
	'right-torunblocked' => "Eludir bloqueos automáticos de nodos de salida ''tor''",
	'tag-tor-description' => 'Si esta marca está presente, una edición ha sido realizada por un nodo de salida Tor.',
	'tag-tor' => 'Hecho por medio de tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Si el cambio fue hecho o no a través de un nodo de salida tor',
);

/** Estonian (eesti)
 * @author Pikne
 */
$messages['et'] = array(
	'torblock-desc' => 'Lubab Tor-võrgu lõppsõlme jaoks viki toimetamise keelata.',
	'torblock-blocked' => 'Sinu IP-aadress <strong>$1</strong> on automaatselt kindlaks tehtud kui Tor-võrgu lõppsõlm.
Tor-võrgu kaudu toimetamine on väärtarvituse vältimiseks keelatud.',
	'torblock-isexitnode' => 'IP-aadress <strong>$1</strong> Tor-võrgu lõppsõlmena on blokeeritud.',
	'right-torunblocked' => 'Mööduda automaatsetest Tor-võrgu lõppsõlme blokeeringutest',
	'tag-tor-description' => 'Kui see märgis on asetatud, tehti muudatus Tor-võrgu lõppsõlme kaudu.',
	'tag-tor' => 'Tehtud Tor-võrgu kaudu',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Kas muudatus on Tor-võrgu lõppsõlme kaudu tehtud',
);

/** Persian (فارسی)
 * @author Huji
 * @author Wayiran
 */
$messages['fa'] = array(
	'torblock-desc' => 'قطع دسترسی خروجی‌های TOR از ویرایش در یک ویکی را ممکن می‌کند',
	'torblock-blocked' => 'نشانی آی‌پی شما، <strong>$1</strong>، به طور خودکار به عنوان یک خروجی TOR شناسایی شده‌است.
ویرایش از طریق این نشانی برای جلوگیری از سوء استفاده ممکن نیست.',
	'torblock-isexitnode' => 'نشانی آی‌پی <strong>$1</strong> به عنوان گرهٔ خروجی Tor مسدود شده است.',
	'right-torunblocked' => 'گذر از قطع دسترسی خودکار خروجی‌های TOR',
	'tag-tor-description' => 'اگر این تگ تنظیم شده، ویرایشی توسط گرهٔ خروجی Tor صورت گرفته است.',
	'tag-tor' => 'انجام شده از طریق tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'آیا تغییر توسط یک گرهٔ خروجی tor انجام شده؟',
);

/** Finnish (suomi)
 * @author Crt
 * @author Nike
 * @author Str4nd
 * @author ZeiP
 */
$messages['fi'] = array(
	'torblock-desc' => 'Mahdollistaa tor-poistumissolmujen estämisen.',
	'torblock-blocked' => 'IP-osoitteesi <strong>$1</strong> on tunnistettu Tor-verkon poistumispisteeksi. Muokkaaminen Tor-verkon kautta on estetty väärinkäytösten välttämiseksi.',
	'torblock-isexitnode' => 'IP-osoite <strong>$1</strong> on estetty Tor-päätepisteenä.',
	'right-torunblocked' => 'Ohittaa automaattiset Tor-poistumispisteiden estot',
	'tag-tor-description' => 'Jos tämä tägi on asetettu, muokkaus on tehty Tor exit noden kautta.',
	'tag-tor' => 'Tehty torin kautta',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Onko muutos tehty tor exit -solmun kautta',
);

/** French (français)
 * @author Grondin
 * @author IAlex
 * @author PieRRoMaN
 * @author Sherbrooke
 * @author Urhixidur
 * @author Verdy p
 */
$messages['fr'] = array(
	'torblock-desc' => 'Permet de bloquer les modifications d’un wiki depuis les nœuds de sortie Tor',
	'torblock-blocked' => 'Votre adresse IP, <strong>$1</strong>, a été détectée automatiquement comme un nœud de sortie Tor.
Les modifications via Tor sont bloquées pour éviter les abus.',
	'torblock-isexitnode' => 'L’adresse IP <strong>$1</strong> est bloquée en tant que nœud de sortie Tor.',
	'right-torunblocked' => 'Contourner le blocage automatique des nœuds de sortie Tor',
	'tag-tor-description' => 'Si cette balise est activée, une modification a été effectuée depuis un nœud de sortie Tor.',
	'tag-tor' => 'Effectué via Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Si la modification a été faite via un nœud de sortie de Tor',
);

/** Franco-Provençal (arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'torblock-desc' => 'Pèrmèt de blocar los changements d’un vouiqui dês los nuods de sortia Tor.',
	'torblock-blocked' => 'Voutra adrèce IP, <strong>$1</strong>, at étâ dècelâ ôtomaticament coment un nuod de sortia Tor.
Los changements avouéc Tor sont blocâs por èvitar los abus.',
	'torblock-isexitnode' => 'L’adrèce IP <strong>$1</strong> est blocâ coment un nuod de sortia Tor.',
	'right-torunblocked' => 'Contornar lo blocâjo ôtomatico des nuods de sortia Tor',
	'tag-tor-description' => 'Se ceta balisa est activâ, un changement at étâ fêt dês un nuod de sortia Tor.',
	'tag-tor' => 'Fêt avouéc Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se lo changement at étâ fêt avouéc un nuod de sortia Tor',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'torblock-desc' => 'Permite que os nodos de saída Tor sexan bloqueados fronte á edición nun wiki',
	'torblock-blocked' => 'O seu enderezo IP, <strong>$1</strong>, foi identificado automaticamente como un nodo de saída Tor.
A edición a través de Tor está bloqueada para previr o abuso.',
	'torblock-isexitnode' => 'O enderezo IP <strong>$1</strong> está bloqueado como nodo de saída Tor.',
	'right-torunblocked' => 'Evitar os bloqueos automáticos dos nodos de saída Tor',
	'tag-tor-description' => 'Se a etiqueta está presente, é que unha edición se realizou desde un nodo de saída Tor.',
	'tag-tor' => 'Feito a través de Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se o cambio se realizou a través dun nodo de saída Tor ou non',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'torblock-desc' => 'Macht d Schryybsperri fir Tor-Usgangs-Chnote megli',
	'torblock-blocked' => 'Dyyni IP-Adräss <strong>$1</strong> isch automatisch as Tor-Usgangs-Chnote identifiziert wore. D Sytebearbeitig in Verbindig mit em Tor-Netzwärch isch nit gwinscht, wel d Gfohr vun eme Missbruuch seli hoch isch.',
	'torblock-isexitnode' => 'D IP-Adräss <strong>$1</strong> isch as Tor-Uusgangs-Chnote gsperrt.',
	'right-torunblocked' => 'Di automatisch Sperri vu Tor-Usgangs-Chnote umgoh',
	'tag-tor-description' => 'Wänn des Tag gsetzt wird, isch e Bearbeitig gmacht wore vun eme Tor-Exit-Chnote.',
	'tag-tor' => 'Gmacht dur Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Eb d Änderig dur e TorExit-Chnote gmacht woren isch',
);

/** Gujarati (ગુજરાતી)
 * @author KartikMistry
 * @author Sushant savla
 */
$messages['gu'] = array(
	'torblock-desc' => 'વિકિમાં ફેરફાર કરવામાંથી ટોર એક્ઝિટ નોડ્સને પ્રતિબંધિત કરવાની પરવાનગી આપો',
	'torblock-blocked' => 'તમારો IP એડ્રેસ <strong>$1</strong> ને ટોર એક્ઝિટ નોડ તરીકે ઓળખાયો છે.
અપવ્યય ટાળવા ટોર દ્વારા ફેરફાર કરવા પર પ્રતિબંધ છે',
	'torblock-isexitnode' => 'IP સરનામું <strong>$1</strong> એ ટોર એક્ઝિટ નોડ તરીકે પ્રતિબંધિત છે.',
	'right-torunblocked' => 'ટોર ઍક્ઝીટ નોડ ના સ્વયંચાલી પ્રતિબંધને અવગણો',
	'tag-tor-description' => 'જો આ ટેગ ગોઠવેલ હશે તો, ફેરફાર એ ટોર એક્ઝિટ નોડ વડે કરવામાં આવ્યો હશે.',
	'tag-tor' => 'ટોર  માંથી બનાવાયું',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'ટોર્ ઍક્ઝીટ નોડ દ્વારા ફેરફારો થયા હતા કે નહીં',
);

/** Hebrew (עברית)
 * @author Rotem Liss
 * @author YaronSh
 */
$messages['he'] = array(
	'torblock-desc' => 'אפשרות לחסימת נקודות יציאה של רשת TOR מעריכה בוויקי',
	'torblock-blocked' => 'כתובת ה־IP שלכם, <strong>$1</strong>, זוהתה אוטומטית כנקודת יציאה של רשת TOR. עריכה דרך TOR חסומה כדי למנוע ניצול לרעה.',
	'torblock-isexitnode' => 'כתובת ה־IP <strong>$1</strong> נחסמה כצומת יציאה של Tor.',
	'right-torunblocked' => 'עקיפת חסימות אוטומטיות של נקודות יציאה ברשת TOR',
	'tag-tor-description' => 'אם תגית זו מוגדרת, בוצעה עריכה מנקודת יציאה של רשת TOR',
	'tag-tor' => 'בוצעה דרך רשת TOR',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'האם השינוי בוצע דרך נקודת יציאה של רשת TOR או לא',
);

/** Hindi (हिन्दी)
 * @author Kaustubh
 */
$messages['hi'] = array(
	'torblock-desc' => 'टॉर एक्झीट नोड्सको विकि संपादनसे ब्लॉक करने की अनुमति देता हैं',
	'torblock-blocked' => 'आपका आईपी एड्रेस, <strong>$1</strong>, अपनेआप टॉर एक्झीट नोड करके पहचाना गया हैं।
गलत इस्तेमाल से बचने के लिये इसे अपनेआप ब्लॉक कर दिया गया हैं।',
	'right-torunblocked' => 'टॉर एक्झीट नोड्सके अपनेआप आये हुए प्रतिबंधोंको नजर अंदाज करें',
);

/** Croatian (hrvatski)
 * @author Dalibor Bosits
 * @author Roberta F.
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'torblock-desc' => 'Omogućava blokiranje tor izlaznih servera od uređivanja na wiki',
	'torblock-blocked' => 'Vaša IP adresa, <strong>$1</strong>, je automatski prepoznata kao tor izlaznog servera.
Uređivanje kroz tor je onemogućeno kako bi se spriječila zloupotreba.',
	'torblock-isexitnode' => 'IP adresa <strong>$1</strong> blokirana je kao Tor izlazni čvor.',
	'right-torunblocked' => 'Premošćivanje automatskih blokiranja tor izlaznih servera',
	'tag-tor-description' => 'Ako je ova kartica označena, uređivanje je vršeno s Tor mreže.',
	'tag-tor' => 'Uređivano preko tor-mreže',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Bez obzira je li uređivanje učinjeno preko Tor izlaznog čvora',
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'torblock-desc' => 'Blokuje wobdźěłanje wikija přez wuchadne suki TOR',
	'torblock-blocked' => 'Twoja IP-adresa, <strong>$1</strong>, je so awtomatisce jako wuchadny suk TOR identifikowała.
Wobdźěłanje přez TOR bu zablokowane, zo by znjewužiću zadźěwało.',
	'torblock-isexitnode' => 'IP-adresa <strong>$1</strong> je jako wuchadny suk Tor zablokowana.',
	'right-torunblocked' => 'Awtomatiske blokowanja wuchadnych sukow TOR wobeńć',
	'tag-tor-description' => 'Jeli tuta taflička je stajeny, je so změna z wuchadneho suka Tor činiła.',
	'tag-tor' => 'Přez TOR sčinjeny',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Hač změna je so přez wuchadny suk TOR činiła abo nic',
);

/** Hungarian (magyar)
 * @author BáthoryPéter
 * @author Dani
 */
$messages['hu'] = array(
	'torblock-desc' => 'Lehetővé teszi a tor kilépési csomópontokról való szerkesztés blokkolását',
	'torblock-blocked' => 'Az IP-címed (<strong>$1</strong>) automatikusan blokkolva lett, mivel tor végpontként azonosítottuk.
Toron keresztül nem lehet szerkeszteni a visszaélések megakadályozása céljából.',
	'torblock-isexitnode' => 'A(z) <strong>$1</strong> IP-cím blokkolva van, mivel Tor kilépési csomópont.',
	'right-torunblocked' => 'bejelentkezés automatikusan blokkolt torvégpontokról',
	'tag-tor-description' => 'Ha ez a címke be van állítva, a szerkesztés egy Tor-csomópontról történt',
	'tag-tor' => 'TOR-on keresztüli szerkesztés',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'A szerkesztés egy tor-csomópontról készült vagy sem.',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'torblock-desc' => 'Permitte que le nodos de exito de Tor sia blocate de facer modificationes in un wiki',
	'torblock-blocked' => 'Tu adresse IP, <strong>$1</strong>, ha essite automaticamente identificate como un nodo de exito de Tor.
Le modification via Tor es prohibite pro impedir le abuso.',
	'torblock-isexitnode' => 'Le adresse IP <strong>$1</strong> es blocate como nodo de exito Tor.',
	'right-torunblocked' => 'Contornar le blocadas automatic de nodos de exito de Tor',
	'tag-tor-description' => 'Si iste etiquetta es presente, un modification esseva facite ab un nodo de exito de Tor.',
	'tag-tor' => 'Facite via tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Si le modification ha essite facite via un nodo de exito de Tor',
);

/** Indonesian (Bahasa Indonesia)
 * @author Bennylin
 * @author Farras
 * @author IvanLanin
 * @author Rex
 */
$messages['id'] = array(
	'torblock-desc' => 'Mengijinkan "exit node" Tor untuk diblokir dari menyunting wiki',
	'torblock-blocked' => 'Alamat IP Anda, <strong>$1</strong> telah diidentifikasi secara otomatis sebagai sebuah simpul akhir tor.
Penyuntingan melalui tor saat ini sedang diblokir untuk mencegah penyalahgunaan.',
	'torblock-isexitnode' => 'Alamat IP <strong>$1</strong> diblokir sebagai suatu simpul akhir Tor.',
	'right-torunblocked' => 'Mengabaikan pemblokiran otomatis terhadap "exit nodes" tor',
	'tag-tor-description' => 'Jika tag ini dipasang, sebuah suntingan dibuat dari sebuah "exit node" Tor.',
	'tag-tor' => 'Dibuat melalui tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Menampilkan apakah suatu perubahan dibuat melalui "exit node" tor atau tidak',
);

/** Iloko (Ilokano)
 * @author Lam-ang
 */
$messages['ilo'] = array(
	'torblock-desc' => 'Agpalubos ti tor exit nodes a maserraan manipud ti panagurnos ti wiki',
	'torblock-blocked' => 'Ti IPa pagtaengam, <strong>$1</strong>, ket na-automatikoa nainaganan a kas tor exit node.
Ti panagurnos babaen ti tor ket naserraan tapno masalakniban ti panag-abuso.',
	'torblock-isexitnode' => 'Ti Ip a pagtaengan <strong>$1</strong> ket naserraan a kasla Tor exit node.',
	'right-torunblocked' => 'Labsan ti automatiko a panagserra ti tor exit nodes',
	'tag-tor-description' => 'No maidisso daytoy nga etiketa, maysa a panag-urnos ket maaramid manipud iti Tor exit node.',
	'tag-tor' => 'Naaramid babaen ti tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'No man wenno saan a ti sinukatan ket naaramid babaen ti tor exit node',
);

/** Italian (italiano)
 * @author BrokenArrow
 * @author Darth Kule
 * @author OrbiliusMagister
 */
$messages['it'] = array(
	'torblock-desc' => 'Permette di bloccare in scrittura exit node tor su una wiki',
	'torblock-blocked' => 'Il tuo indirizzo IP, <strong>$1</strong>, è stato automaticamente identificato come un exit node tor.
La possibilità di editare utilizzando tor è bloccata per impedire abusi.',
	'torblock-isexitnode' => "L'indirizzo IP <strong>$1</strong> è bloccato in quanto è un exit node Tor",
	'right-torunblocked' => 'Ignora i blocchi automatici degli exit node tor',
	'tag-tor-description' => 'Se questo tag è impostato, la modifica è stata effettuata da un nodo di uscita della rete Tor.',
	'tag-tor' => 'Effettuato tramite tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Indica se la modifica è stata fatta da un nodo di uscita TOR o meno',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Muttley
 * @author Shirayuki
 */
$messages['ja'] = array(
	'torblock-desc' => 'Tor の末端ノードからのウィキの編集をブロックできるようにする',
	'torblock-blocked' => 'あなたの IP アドレス <strong>$1</strong> は、Tor の末端ノードであると自動的に認識されました。
不正利用を防止するため、Tor を通じての編集を禁止しています。',
	'torblock-isexitnode' => 'IP アドレス <strong>$1</strong> は Tor 末端ノードであるためブロックされています。',
	'right-torunblocked' => 'Tor 末端ノードの自動ブロックを回避',
	'tag-tor-description' => 'このタグが設定されている場合、Tor 末端ノードを通じてなされた編集です。',
	'tag-tor' => 'Tor を通じてなされた',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Tor 末端ノードを通じてなされた変更かどうか',
);

/** Javanese (Basa Jawa)
 * @author NoiX180
 * @author Pras
 */
$messages['jv'] = array(
	'torblock-desc' => "Mblokir titik alamat ''tor'' kanggo nyunting kaca wiki",
	'torblock-blocked' => "Alamat IP panjenengan, <strong>$1</strong> wis diidhèntifikasi sacara otomatis minangka sawijining titik alamat ''tor''.
Panyuntingan liwat ''tor'' diblokir kanggo nyegah salahguna.",
	'torblock-isexitnode' => 'Alamat IP <strong>$1</strong> diblokir minangka nodè akir Tor.',
	'right-torunblocked' => "Lirwakna pamblokiran otomatis marang alamat ''tor''",
	'tag-tor-description' => 'Yèn tag iki dipasang, suntingan digawé saka nodé akir Tor.',
	'tag-tor' => 'Digawé liwat tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Mesthèkaké yèn owahan digawé saka nodé akir tor iya apa ora.',
);

/** Georgian (ქართული)
 * @author David1010
 * @author გიორგიმელა
 */
$messages['ka'] = array(
	'torblock-desc' => 'Tor ხაზიდან ბლოკირების გასვლის ნებართვა',
	'torblock-blocked' => 'თქვენი IP-მისამართი, <strong>$1</strong>, იდენტიფიცირებულია როგორც Tor-ი.
რედაქტირება Tor-ის მეშვეობით აკრძალულია ბოროტად გამოყენებების .',
	'torblock-isexitnode' => 'IP მისამართი <strong>$1</strong> დაბლოკილია, როგორც Tor-ის ქსელის გამოსავალი კვანძი.',
	'right-torunblocked' => 'Tor-ის ავტომატური ბლოკირების გავლა',
	'tag-tor-description' => 'იმ შემთხვევაში თუ ეს მინიშნება დაყენებულია, იგი გაკეთებულია Tor-ის მეშვეობით.',
	'tag-tor' => 'გაკეთებულია Tor-ის მეშვეობით',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'თუ იყო ცვლილება გაკეთებული Tor-ის მეშვეობით',
);

/** Korean (한국어)
 * @author Ilovesabbath
 * @author Kwj2772
 * @author 아라
 */
$messages['ko'] = array(
	'torblock-desc' => '토르 끝 노드로 위키를 편집하는 것을 차단합니다',
	'torblock-blocked' => '<strong>$1</strong> IP 주소는 토르 끝 노드로 자동적으로 확인되었습니다.
토르를 사용한 편집은 악용을 방지하기 위해 차단되어 있습니다.',
	'torblock-isexitnode' => '<strong>$1</strong> IP 주소는 토르 끝 노드이기 때문에 차단되었습니다.',
	'right-torunblocked' => '토르 끝 노드의 자동 차단을 적용하지 않음',
	'tag-tor-description' => '이 태그가 설정되어 있으면 토르 끝 노드를 통해 편집한 것입니다.',
	'tag-tor' => '토르 사용',
	'abusefilter-edit-builder-vars-tor-exit-node' => '편집이 토르 끝 노드를 통해 바뀌었는 지의 여부',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'torblock-desc' => 'Kann et Ändere am Wiki ongerbenge för Metmaacher, di övver <tt>tor</tt> Ußjäng kumme.',
	'torblock-blocked' => 'Ding IP-Adress (<strong>$1</strong>) eß als_enne <tt>tor</tt> Ußjäng äkannt woode.
Änderunge aam Wiki dom_mer övver <tt>tor</tt> nit zolohße,
esu määt och Keiner Dreßß domet.',
	'torblock-isexitnode' => 'De <i lang="en">IP</i>-Addräß <strong>$1</strong> es jesperrt, weil se ene Ußjang vum Tor Näzwärrek es.',
	'right-torunblocked' => 'Et Ändere am Wiki övver <tt>tor</tt> Ußjäng zolohße',
	'tag-tor-description' => 'Wann dat Zeijshe jesaz es, kohm die Änderung övver ene <tt>tor</tt> Ußjang.',
	'tag-tor' => 'Övver <tt>tor</tt> Ußjang jemaat',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Of en Änderung övver ene <i lang="en">tor</i>-Ußjang jemaat woode es',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Les Meloures
 * @author Robby
 */
$messages['lb'] = array(
	'torblock-desc' => 'Erméiglecht et Ännerunge vun enger Wiki iwwer Tor-Ausgangskniet (tor exit nodes) ze spären',
	'torblock-blocked' => 'Är IP-Adress,  <strong>$1</strong>, gouf automatesch als Tor-ausgangsknuet erkannt.
Ännerungen iwwer Tor si gespaart fir Mëssbrauch ze verhënneren.',
	'torblock-isexitnode' => "D'IP-Adress <strong>$1</strong> ass als Ausgangspunkt vun engem Tor gespaart.",
	'right-torunblocked' => "Automatesch Spär fir Tor-Ausgangskniet z'ëmgoen",
	'tag-tor-description' => 'Wann dës Markéierung gesat ass da gouf eng Ännerung vun engem Tor-Ausgangsknuet gemaach.',
	'tag-tor' => "Iwwer ''Tor'' gemaach",
	'abusefilter-edit-builder-vars-tor-exit-node' => "Ob d'Ännerungen iwwer en Tor-Ausgangsknuet gemaach goufen",
);

/** Limburgish (Limburgs)
 * @author Matthias
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
	'torblock-desc' => 'Maakt bewerke onmeugelik veur tor exitnodes',
	'torblock-blocked' => 'Oew IP-adres, <strong>$1</strong>, is herkend as tor exitnode. Bewerke via tor es niet toegestaon om misbroek te veurkomme.',
	'torblock-isexitnode' => "'t IP-adres <strong>$1</strong> is geblok es Torexitnode.",
	'right-torunblocked' => 'Automatische blokkades van tor exitnodes omzeile',
	'tag-tor-description' => "Es dit label is ingesteldj, is de bewèrking gemaak via 'ne Tor exitnode.",
	'tag-tor' => 'Gemaak mid tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Óf de-n angering waal ódder neet is gemaak waenger 'ne tor exitnode",
);

/** Lithuanian (lietuvių)
 * @author Matasg
 */
$messages['lt'] = array(
	'torblock-desc' => 'Leidžia tor išėjimo mazgams būti užblokuotiems nuo wiki redagavimo',
	'torblock-blocked' => 'Jūsų IP adresas, <strong>$1</strong>, buvo automatiškai nustatytas kaip tor išėjimo mazgas.
Redagavimas per tor yra užblokuotas, kad nebūtų piktnaudžiaujama.',
	'right-torunblocked' => 'Praleisti automatinį tor išėjimo mazgų blokavimą',
	'tag-tor-description' => 'Jei žymė yra nustatyta, redagavimas buvo atliktas iš Tor išėjimo mazgo.',
	'tag-tor' => 'Atliktas per tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Bet kuriuo atveju, pakeitimas buvo atliktas per tor išėjimo mazgą',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'torblock-desc' => 'Овозможува блокирање на излезни Tor јазли за да не можат да го уредуваат некое вики',
	'torblock-blocked' => 'Вашата IP-адреса, <strong>$1</strong>, беше автоматски препознаена како излезен Tor јазол.
Уредувањето преку Tor е забрането со цел да се спречи злоупотреба.',
	'torblock-isexitnode' => 'IP-адресата <strong>$1</strong> е блокирана како излезен Tor јазол.',
	'right-torunblocked' => 'Заобиколување на автоматски блокови на излезни Tor јазли',
	'tag-tor-description' => 'Ако е поставена оваа ознака, тоа значи дека било направено уредување со Tor излезен јазол.',
	'tag-tor' => 'Направено преку Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Дали промената била направена преку излезен Tor јазол',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 */
$messages['ml'] = array(
	'torblock-desc' => 'ടോർ എക്സിറ്റ് നോഡുകൾ ഉപയോഗിച്ച് വിക്കി തിരുത്തുന്നത് തടയുക',
	'torblock-blocked' => 'താങ്കളുടെ ഐ.പി. വിലാസം, <strong>$1</strong>, ഒരു ടോർ എക്സിറ്റ് നോഡാണെന്ന് സ്വയം കണ്ടെത്തിയിട്ടുണ്ട്.
ദുരുപയോഗങ്ങൾ ഒഴിവാക്കാൻ ടോർ വഴി തിരുത്തുന്നത് നിരോധിച്ചിരിക്കുന്നു.',
	'torblock-isexitnode' => 'ടോർ എക്സിറ്റ് നോഡായതിനാൽ <strong>$1</strong> ഐ.പി. വിലാസം തിരുത്തലുകൾ നടത്തുന്നതിൽ നിന്നും തടയപ്പെട്ടിരിക്കുന്നു.',
	'right-torunblocked' => 'ടോറിന്റെ പുറത്തേയ്ക്കുള്ള കേന്ദ്രങ്ങൾക്കുള്ള തടയൽ പാർശ്വീകരിച്ചു കടക്കുക',
	'tag-tor-description' => 'ഈ ടാഗ് ഉണ്ടെങ്കിൽ, ടോറിന്റെ പുറത്തേയ്ക്കുള്ള കേന്ദ്രം വഴി തിരുത്തൽ നടത്തപ്പെട്ടിരിക്കുന്നു.',
	'tag-tor' => 'ടോർ വഴി നടത്തിയത്',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'തിരുത്തൽ നടന്നത് ടോർ ബാഹ്യ കേന്ദ്രം വഴിയാണോ അല്ലയോ എന്ന്',
);

/** Marathi (मराठी)
 * @author Kaajawa
 * @author Kaustubh
 * @author Rahuldeshmukh101
 */
$messages['mr'] = array(
	'torblock-desc' => 'टॉर एक्झीट नोड्सना विकि संपादनापासून ब्लॉक करण्याची परवानगी देते',
	'torblock-blocked' => 'तुमचा आयपी अंकपत्ता, <strong>$1</strong>, आपोआप टॉर एक्झीट नोड म्हणून ओळखला गेलेला आहे.
गैरवापर टाळण्यासाठी टॉर मधून संपादन करण्यावर बंदी घालण्यात आलेली आहे.',
	'torblock-isexitnode' => 'बहिशाल    म्हणून   <strong>$1</strong> हा अंकपत्ता प्रतिबंधित केला आहे',
	'right-torunblocked' => 'टॉर एक्झीट नोड्सच्या आपोआप आलेल्या प्रतिबंधांकडे दुर्लक्ष करा',
	'tag-tor' => 'टॉर-द्वारे (Tor) बनवले',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 * @author Aviator
 * @author Izzudin
 */
$messages['ms'] = array(
	'torblock-desc' => 'Membolehkan sekatan terhadap nod keluar Tor',
	'torblock-blocked' => 'Alamat IP anda, <strong>$1</strong>, telah dikenal pasti sebagai nod keluar Tor.
Penyuntingan melalui Tor disekat untuk mengelak penyalahgunaan.',
	'torblock-isexitnode' => 'Alamat IP <strong>$1</strong> disekat sebagai nod keluar Tor.',
	'right-torunblocked' => 'Melepasi sekatan nod keluar Tor',
	'tag-tor-description' => 'Label ini menandakan sebuah suntingan dibuat melalui nod keluar Tor.',
	'tag-tor' => 'Dibuat melalui tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Sama ada atau tidak perubahan dibuat melalui buku keluar tor',
);

/** Maltese (Malti)
 * @author Chrisportelli
 */
$messages['mt'] = array(
	'torblock-isexitnode' => "L-indirizz IP <strong>$1</strong> huwa mblukkat minħabba li huwa ''exit node'' Tor.",
);

/** Norwegian Bokmål (norsk bokmål)
 * @author Harald Khan
 * @author Nghtwlkr
 * @author Njardarlogar
 */
$messages['nb'] = array(
	'torblock-desc' => 'Gjør det mulig å blokkere Tors utgangsnoder fra å redigere en wiki',
	'torblock-blocked' => 'Din IP-adresse, <strong>$1</strong>, har blitt automatisk identifisert som en utgangsnode fra Tor.
Redigering via Tor er blokkert for å forhindre misbruk.',
	'torblock-isexitnode' => 'IP-adressen <strong>$1</strong> er blokkert som en utgangsnode for Tor.',
	'right-torunblocked' => 'Kan redigere fra automatisk blokkerte Tor-noder',
	'tag-tor-description' => 'Om denne taggen er på, ble redigeringen gjort via en Tor-node.',
	'tag-tor' => 'Gjort via Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Hvorvidt endringen ble gjort gjennom et Tor-utgangsknutepunkt.',
);

/** Low German (Plattdüütsch)
 * @author Slomox
 */
$messages['nds'] = array(
	'torblock-desc' => 'Verlöövt dat Sperren vun Tor-Utgangsknütten',
	'torblock-blocked' => 'Diene IP-Adress <strong>$1</strong> is automaatsch as Tor-Utgangsknütten kennt worrn. Dat Ännern vun Sieden över en Tor-Nettwark is sperrt, de Gefohr vun Missbruuk is to groot.',
	'right-torunblocked' => 'Ümgahn vun de automaatsche Sperr vun Tor-Utgangsknütten',
	'tag-tor-description' => 'Wenn disse Tag sett is, is de Ännern von en Tor-Utgangsknütten maakt worrn.',
	'tag-tor' => 'Maakt dör Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Wat de Ännern över en Tor-Utgangsknütten maakt worrn is',
);

/** Low Saxon (Netherlands) (Nedersaksies)
 * @author Servien
 */
$messages['nds-nl'] = array(
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Of de wieziging wel of niet mit n tor-uutgangsknope emaakt is',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'torblock-desc' => 'Maakt bewerken onmogelijk voor tor exitnodes',
	'torblock-blocked' => 'Uw IP-adres, <strong>$1</strong>, is herkend als tor exitnode.
Bewerken via tor is niet toegestaan om misbruik te voorkomen.',
	'torblock-isexitnode' => 'Het IP-adres <strong>$1</strong> is geblokkeerd als tor exitnode.',
	'right-torunblocked' => 'Automatische blokkades van tor exitnodes omzeilen',
	'tag-tor-description' => 'Als dit label is ingesteld, is de bewerking gemaakt via een Tor exitnode.',
	'tag-tor' => 'Gemaakt via tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Of de wijziging wel of niet is gemaakt via een tor exitnode',
);

/** Nederlands (informeel)‎ (Nederlands (informeel)‎)
 * @author Siebrand
 */
$messages['nl-informal'] = array(
	'torblock-blocked' => 'Jouw IP-adres, <strong>$1</strong>, is herkend als tor exitnode.
Bewerken via tor is niet toegestaan om misbruik te voorkomen.',
);

/** Norwegian Nynorsk (norsk nynorsk)
 * @author Frokor
 * @author Gunnernett
 * @author Harald Khan
 * @author Njardarlogar
 * @author Ranveig
 */
$messages['nn'] = array(
	'torblock-desc' => 'Gjer det mogeleg å blokkere Tor-utgangsnodar frå å gjere endringar i ein wiki',
	'torblock-blocked' => 'IP-adressa di, <strong>$1</strong>, har blitt automatisk identifisert som eit utgangsnode frå TOR.
Redigering via TOR er blokkert for å hindre misbruk.',
	'torblock-isexitnode' => 'IP-adressa <strong>$1</strong> er blokkert som ei utgangsnode på Tor.',
	'right-torunblocked' => 'Kan redigere frå automatisk blokkerte TOR-nodar',
	'tag-tor-description' => 'Om dette merket er på, vart endringa gjort frå eit Tor-utgangs-knutepunkt.',
	'tag-tor' => 'Gjort gjennom tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Om endringa vart gjort gjennom eit Tor-utgangsknutepunkt.',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'torblock-desc' => 'Permet als noses de sortida Tor d’èsser blocats en escritura sus un wiki',
	'torblock-blocked' => "Vòstra adreça ip, <strong>$1</strong>, es estada identificada automaticament coma un nos de sortida Tor.
L’edicion per aqueste mejan es blocada per evitar d'abuses.",
	'torblock-isexitnode' => "L'adreça IP <strong>$1</strong> es blocada en tant que nos de sortida Tor.",
	'right-torunblocked' => 'Passar al travèrs dels blocatges dels noses de sortida Tor.',
	'tag-tor-description' => 'Se aquesta balisa es utilizada, alara la modificacion es estada escafada dempuèi un nos de sortida de Tor.',
	'tag-tor' => 'Fach dempuèi tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se la modificacion es estada facha via un nos de sortida de Tor',
);

/** Oriya (ଓଡ଼ିଆ)
 * @author Psubhashish
 */
$messages['or'] = array(
	'torblock-desc' => 'tor ଏକ୍ଜିଟ ଅବସ୍ଥାରେ ଏକ ଉଇକିରେ ସମ୍ପାଦନାକୁ ବାରଣ କରିବା ନିମନ୍ତେ ଅନୁମତି ଦେଇଥାଏ',
	'torblock-blocked' => 'ଆପଣଙ୍କ IP ଠିକଣା <strong>$1</strong> ଟି ଆପେଆପେ ଏକ tor ଏକ୍ଜିଟ ଅବସ୍ଥା ଭାବରେ ଗଣାଯାଇଅଛି ।
ଅବ୍ୟବହାରକୁ ରୋକିବା ନିମନ୍ତେ tor ଦେଇ ବଦଳସବୁ ଅଟକାଯାଇଛି ।',
	'torblock-isexitnode' => 'IP ଠିକଣା <strong>$1</strong>ଟି ଏକ tor exit ଅବସ୍ଥା ଭାବରେ ଅଟକାଯାଇଛି ।',
	'right-torunblocked' => 'tor exit ଗଣ୍ଠିକୁ ଅଲଗା ଦିଗକୁ ବାଟ ଆଡ଼କୁ କଢ଼ାଇନେବେ',
	'tag-tor-description' => 'ଯଦି ଚିହ୍ନଟି ଥୟ କରାଯାଇଥାଏ, ତେବେ ଏହି ବଦଳଟି ଏକ Tor exit nodeରୁ କରାଯାଇଛି ।',
	'tag-tor' => 'tor ଦେଇ କରାଯାଇଛି',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'ଏକ tor exit node ଦେଇ ବଦଳାଯାଇଛି କି ନାହିଁ',
);

/** Polish (polski)
 * @author Beau
 * @author Sp5uhe
 */
$messages['pl'] = array(
	'torblock-desc' => 'Blokuje możliwość edycji dla użytkowników anonimowych łączących się przez serwery wyjściowe sieci Tor',
	'torblock-blocked' => 'Twój adres IP <strong>$1</strong> został automatycznie zidentyfikowany jako serwer wyjściowy sieci Tor.
Możliwość edycji z wykorzystaniem tej sieci jest zablokowana w celu zapobiegania nadużyciom.',
	'torblock-isexitnode' => 'Adres IP <strong>$1</strong> jest zablokowany ponieważ jest wyjściowym węzłem sieci Tor.',
	'right-torunblocked' => 'Obejście automatycznych blokad zakładanych na serwery wyjściowe sieci Tor',
	'tag-tor-description' => 'Jeżeli ten znacznik jest ustawiony, edycja została wykonana poprzez węzeł wyjściowy sieci TOR.',
	'tag-tor' => 'wykonane poprzez sieć TOR',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Czy zmiana została wprowadzona przez węzeł wyjściowy sieci tor',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'torblock-desc' => 'A përmëtt ëd bloché exit node tor an dzora a na wiki',
	'torblock-blocked' => "Soa adrëssa IP, <strong>$1</strong>, a l'é stàita automaticament identificà com neu 'd surtìa tor.
La modìfica con tor a l'é blocà për evité dj'abus.",
	'torblock-isexitnode' => "L'adrëssa IP <strong>$1</strong> a l'é blocà com neu ëd surtìa Tor.",
	'right-torunblocked' => "Sàuta ij blòch automàtich dj'exit node tor",
	'tag-tor-description' => "Se sto tag sì a l'é ampostà, na modìfica a l'é stàita fàita da n'exit node Tor.",
	'tag-tor' => 'Fàit a travers tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Se ël cambi a l'é stàit fàit o pa a travers un exit node tor",
);

/** Western Punjabi (پنجابی)
 * @author Khalid Mahmood
 */
$messages['pnb'] = array(
	'torblock-desc' => 'ٹار ایگزٹ نوڈز نوں اجازت دیو اک وکی نوں بدلن تے روکن نوں۔',
	'torblock-blocked' => 'تواڈا آئی پی پتہ <strong>$1</strong> ٹار ايگزٹ نوڈ لئی اپنے آپ پچھان لیا گیا اے۔
ٹار راہ تبدیلی کرنا روکیا گیا جے غلط کم توں بچن لئی۔',
	'torblock-isexitnode' => 'آئی پی پتہ <strong>$1</strong> ٹار ایگزٹ نوڈ لئی روک دتا گیا اے۔',
	'right-torunblocked' => 'ٹار ایگزٹ نوڈ دیاں اپنے آپ روکاں نوں بائی پاس کرو۔',
	'tag-tor-description' => 'اگر ایہ ٹیگ سیٹ اے، اک تبدیلی ٹار ایگزٹ نوڈ ولوں کیتی گئی اے۔',
	'tag-tor' => 'ٹار وجوں بدیا۔',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'تبدیلی اک ٹار ايگزٹ نوڈ  ولوں بنائی گئی اے یا نئیں۔',
);

/** Portuguese (português)
 * @author Hamilton Abreu
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'torblock-desc' => 'Permite que nós de saída Tor sejam impedidos de editar uma wiki',
	'torblock-blocked' => 'O seu endereço IP, <strong>$1</strong>, foi automaticamente identificado como um nó de saída Tor.
A edição através de Tor está bloqueada para prevenir abusos.',
	'torblock-isexitnode' => 'O endereço IP <strong>$1</strong> está bloqueado como nó de saída Tor.',
	'right-torunblocked' => 'Ultrapassar bloqueios automáticos de nós de saída Tor',
	'tag-tor-description' => 'Se esta marca estiver presente, uma edição foi feita de um nó de saída Tor.',
	'tag-tor' => 'Feita através de Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se mudança foi feita ou não através de um nó de saída Tor',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Eduardo.mps
 * @author Jesielt
 * @author Tuliouel
 */
$messages['pt-br'] = array(
	'torblock-desc' => 'Permite que nós de saída Tor sejam bloqueados para editar um wiki',
	'torblock-blocked' => 'O seu endereço IP, <strong>$1</strong>, foi automaticamente identificado como um nó de saída Tor.
A edição através de Tor está bloqueada para prevenir abusos.',
	'torblock-isexitnode' => 'O endereço de IP <strong>$1</strong> está bloqueado por ser nó de saída Tor.',
	'right-torunblocked' => 'Ultrapassar bloqueios automáticos de nós de saída Tor',
	'tag-tor-description' => 'Se esta marca estiver presente, uma edição foi feita de um nó de saída Tor.',
	'tag-tor' => 'Feita através de Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Se a mudança foi ou não feita através de um nó de saída Tor.',
);

/** Romanian (română)
 * @author AdiJapan
 * @author Stelistcristi
 */
$messages['ro'] = array(
	'torblock-desc' => 'Permite nodurile de ieșire tor ca să fie blocat de la modificarea unui wiki',
	'torblock-blocked' => 'Adresa dvs IP - <strong>$1</strong> - a fost identificată automat ca un nod de ieșire tor.
Editarea prin tor este blocată pentru a preveni abuzuri.',
	'torblock-isexitnode' => 'Adresa IP <strong>$1</strong> este blocată ca un nod de ieșire Tor.',
	'right-torunblocked' => "Nu este afectat de blocarea automată a nodurilor de ieșire ''tor''",
	'tag-tor-description' => "Dacă această baliză este activată, s-a făcut o modificare de la un nod de ieșire ''tor''.",
	'tag-tor' => 'Făcut prin tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Dacă modificarea s-a făcut printr-un nod de ieșire ''tor''",
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'torblock-desc' => "Permette 'a le node de uscite de tor de essere bloccate pe le cangiaminde sus a Uicchi",
	'torblock-blocked' => "L'indirizze IP tune, <strong>$1</strong>, ha state automaticamende idendificate cumme a 'nu node de uscite de tor.<br />
Le cangiaminde ausanne tor sonde bloccate pe prevenìe abbuse.",
	'torblock-isexitnode' => "L'indirizze IP <strong>$1</strong> jè bloccate cumme a 'nu node de uscite de Tor.",
	'right-torunblocked' => 'Zumbe le blocche automatiche de le node de uscite de tor',
	'tag-tor-description' => "Ce stu tag jè 'mbostate,  'nu cangiamende ha state fatte da 'nu node de uscite de Tor.",
	'tag-tor' => 'Fatte attraverse',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Tutte o nisciune de le cangiaminde onne state fatte ausanne 'nu node de uscite de tor",
);

/** Russian (русский)
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'torblock-desc' => 'Позволяет блокировать выходные узлы сети Tor',
	'torblock-blocked' => 'Ваш IP-адрес, <strong>$1</strong>, был автоматически определён как выходной узел сети Tor.
Редактирование посредством Tor запрещено во избежание злоупотреблений.',
	'torblock-isexitnode' => 'IP адрес <strong>$1</strong> заблокирован как выходной узел сети Tor.',
	'right-torunblocked' => 'обход автоматической блокировки узлов сети Tor',
	'tag-tor-description' => 'Если данная метка установлена, значит правка была сделана с выходного узла сети Tor.',
	'tag-tor' => 'Сделано через Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Была ли правка сделана через выходной узел сети Tor',
);

/** Rusyn (русиньскый)
 * @author Gazeb
 */
$messages['rue'] = array(
	'torblock-desc' => 'Доволює блоковати едітованя з выступный узлів сітї Tor',
	'torblock-blocked' => 'Ваша IP адреса (<strong>$1</strong>) была автоматішно розпознана як выступный узел мережы Tor.
Едітованя средством мережы Tor є про превенцію знеужыта блокованя.',
	'torblock-isexitnode' => 'IP адреса <strong>$1</strong> є блокованя як выступный узел мережы Tor.',
	'right-torunblocked' => 'Обходжіня автоматічного блокованя выступный узлів мережы Tor',
	'tag-tor-description' => 'Покы є наставлена тота значка, едітованя было выконане з выступного узла мережы Tor.',
	'tag-tor' => 'Зроблене через Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Ці было едітованя выконане з выступного узла мережы Tor',
);

/** Sanskrit (संस्कृतम्)
 * @author Shubha
 */
$messages['sa'] = array(
	'torblock-desc' => 'टार् एक्झीट् नोड्स विकि सम्पादने अवरोद्धुम् अनुमतिं यच्छति',
	'torblock-blocked' => 'भवतः ऐ पि सङ्केतः, <strong>$1</strong>, स्वयं टार् एक्सिट् नोड्-रूपेण अभिज्ञातमस्ति ।
दुरुपयोगस्य निग्रहणाय टार्-द्वारा सम्पादनम् अवरुद्धमस्ति ।',
	'torblock-isexitnode' => 'टार्-एक्सिट्-नोड् इति नाम्ना <strong>$1</strong> ऐ पि सङ्केतः अवरुद्धः अस्ति ।',
	'right-torunblocked' => 'टार्-एक्सिट्-नोड्स् इत्येतेषां स्वयंचालितावरोधं परिहरतु ।',
	'tag-tor-description' => 'इदम् अङ्कनं यदि व्यवस्थापितं तर्हि टार् एक्सिट् नोड्तः किञ्चन सम्पादनं कृतम् ।',
	'tag-tor' => 'टार् द्वारा निर्मितम्',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'टार् एक्सिट् नोड्तः वा अन्यथा वा परिवर्तनं कृतम्',
);

/** Sakha (саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'torblock-desc' => 'Tor-ситим таһаарар түмүктэрин (выходные узлы) бүөлүүргэ туттуллар',
	'torblock-blocked' => '<strong>$1</strong>, Эн IP-аадырыһыҥ Tor-ситим таһаарар түмүгүн (выходной узел) курдук көстүбүт.
Tor-ситими туһанан уларытыы манна бобуллар.',
	'torblock-isexitnode' => '<strong>$1</strong> IP аадырыс Tor ситимин тахсар түмүгэ буоларын быһыытынан бобуллубут.',
	'right-torunblocked' => 'Tor-ситим түмүктэрин аптамаатынан бобууну тумнуу',
	'tag-tor-description' => 'Бу бэлиэ турбут буоллаҕына уларытыы Tor ситимтэн оҥоһуллубут эбит.',
	'tag-tor' => 'Tor нөҥүө оҥоһуллубут',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Көннөрүү Tor ситим нөҥүө оҥоһуллубут дуу суох дуу',
);

/** Sicilian (sicilianu)
 * @author Melos
 * @author Santu
 */
$messages['scn'] = array(
	'torblock-desc' => "Pirmetti di bluccari 'n scrittura exit node tor sù na wiki",
	'torblock-blocked' => 'Lu tò nnirizzu IP, <strong>$1</strong>, vinni ricanusciutu autumaticamenti comu nu exit node tor.
La pussibbilitati di scrìviri utilizzannu tor è bluccata pi non putiri fari abbusi.',
	'right-torunblocked' => "Non pigghiari 'n cunziddirazzioni li blocchi automàtichi di li exit node tor",
	'tag-tor-description' => 'Si stu tag fu impustatu, lu canciamentu fu fattu da nu nodu di nisciuta dâ reti Tor.',
	'tag-tor' => 'Fattu attraversu tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => "Ndica si lu canciamentu fu fattu da nu nodu d'uscita TOR o menu",
);

/** Sinhala (සිංහල)
 * @author Budhajeewa
 * @author Singhalawap
 * @author තඹරු විජේසේකර
 */
$messages['si'] = array(
	'torblock-desc' => 'tor exit nodes මගින් විකියන් සැකසුම අවහිර කිරීමට ඉඩදෙයි.',
	'torblock-blocked' => 'ඔබේ IP ලිපිනය, <strong>$1</strong> tor exit node බව ස්වයංක්‍රීයව දැනගති.
අනිසි භාවිතයන් වළක්වාලීම සඳහා torක් තුලින් සංස්කරණය කිරීම  වාරණයකර ඇත.',
	'torblock-isexitnode' => 'Tor exit node ලෙස <strong>$1</strong> යන IP ලිපිනය වාරණිතයි.',
	'right-torunblocked' => 'toe exit nodes සඳහා වන ස්‍වයංක්‍රීය වාරණයන් මගහරියි',
	'tag-tor-description' => 'මෙම ටැගය තිබේ නම්, Tor exit node යක් මගින් සැකසුමක් කොට ඇත.',
	'tag-tor' => 'tor හරහා නිර්මාණිතයි',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'වෙනස tor exit node යක් හරහා සිදු කළ නොකළ බව',
);

/** Slovak (slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'torblock-desc' => 'Umožňuje bolovať úpravy pochádzajúce z výstupných uzlov siete TOR.',
	'torblock-blocked' => 'Vaša IP adresa, <strong>$1</strong>, bola automaticky identifikovaná ako výstupný uzol siete TOR.
Aby sa zabránilo zneužitiu, úpravy zo siete TOR sú blokované.',
	'torblock-isexitnode' => 'IP adresa <strong>$1</strong> je zablokovaná, pretože je to výstupný uzol siete Tor.',
	'right-torunblocked' => 'Obísť automatické blokovanie výstupných uzlov siete TOR',
	'tag-tor-description' => 'Ak je táto značka nastavená, úprava bola vykonaná z výstupného uzla Tor.',
	'tag-tor' => 'Prostredníctvom Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Či bola alebo nebola úprava vykonaná z koncového uzla TOR',
);

/** Slovenian (slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'torblock-desc' => 'Omogoča blokiranje izhodnih vozlišč tor pred urejanjem wikija',
	'torblock-blocked' => 'Vaš IP-naslov, <strong>$1</strong>, je bil samodejno prepoznan kot izhodno vozlišče tor.
Urejanje preko tor je blokirano za preprečitev zlorab.',
	'torblock-isexitnode' => 'IP-naslov <strong>$1</strong> je blokiran kot izhodno vozlišče tor.',
	'right-torunblocked' => 'Izogne se samodejnim blokadam izhodnih vozlišč tor',
	'tag-tor-description' => 'Če je ta oznaka določena, je bilo urejanje narejeno iz izhodnega vozlišča tor.',
	'tag-tor' => 'Storjeno skozi tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Ali je ali ni bila sprememba storjena skozi izhodno vozlišče tor',
);

/** Albanian (shqip)
 * @author Olsi
 */
$messages['sq'] = array(
	'torblock-desc' => 'Lejon nyjet dalëse tor të jenë të bllokuara nga redaktimi i një wiki',
	'torblock-blocked' => 'Adresa juaj IP, <strong>$1</strong>, është identifikuar automatikisht si një nyje dalëse tor.
Redaktimi nëpërmjet tor është i bllokuar për të parandaluar abuzimet.',
	'torblock-isexitnode' => 'Adresa IP <strong>$1</strong> është bllokuar si një nyje dalëse Tor.',
	'right-torunblocked' => 'Anashkaloni bllokimet e nyjeve dalëse tor.',
	'tag-tor-description' => 'Nëse kjo etiketë është vendosur, një redaktim u bë nga një nyje dalëse Tor.',
	'tag-tor' => 'Bërë nëpërmjet tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Nëse është apo jo ndryshimi i bërë nëpërmjet një nyje dalëse tor',
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Millosh
 * @author Nikola Smolenski
 * @author Rancher
 */
$messages['sr-ec'] = array(
	'torblock-desc' => 'Омогућава блокирање измена викија од стране излазних чворова Тора.',
	'torblock-blocked' => 'Твоја ИП адреса, <strong>$1</strong>, је аутоматски идентификована као излазни нод тора. Измене путем тора су онемогућене због могуће злоупотребе.',
	'right-torunblocked' => 'прескакање самосталних забрана излазних нодова',
);

/** Serbian (Latin script) (srpski (latinica)‎)
 * @author Michaello
 */
$messages['sr-el'] = array(
	'torblock-desc' => 'Omogućava blokiranje izmena na vikiju od strane izlaznih nodova tora.',
	'torblock-blocked' => 'Tvoja IP adresa, <strong>$1</strong>, je automatski identifikovana kao izlazni nod tora. Izmene putem tora su onemogućene zbog moguće zloupotrebe.',
	'right-torunblocked' => 'Preskoči automatske blokove izlaznih nodova tora.',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'torblock-desc' => 'Moaket ju Skrieuwspeere foar Poute-Uutgongsknätte muugelk',
	'torblock-blocked' => 'Dien IP-Adresse <strong>$1</strong> wuud automatisk as Poute-Uutgongsknät identifizierd. Ju Siedenbeoarbaidenge in Ferbiendenge mäd dät Poute-Nätwierk is nit wonsked, deer ju Misbruuksgefoar gjucht hooch is.',
	'right-torunblocked' => 'Uumgungen fon ju automatiske Speere fon Poute-Uutgongknätte',
	'tag-tor-description' => 'Wan dit Tag sät is, geböärde ju Beoarbaidenge truch n Tor-Uutgongsknät.',
	'tag-tor' => 'Truch n Tor-Uutgongsknät',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Annerenge waas moaked truch n Poute-Uutgongs-Knät',
);

/** Swedish (svenska)
 * @author Boivie
 * @author Lejonel
 */
$messages['sv'] = array(
	'torblock-desc' => 'Gör det möjligt att blockera Tors utgångsnoder från att redigera en wiki',
	'torblock-blocked' => 'Din IP-adress, <strong>$1</strong>, har automatiskt identifierats som en utgångsnod från Tor.
Redigering via Tor är blockerad för att förhindra missbruk.',
	'torblock-isexitnode' => 'IP-adressen <strong>$1</strong> är blockerad som en utgångsnod på Tor.',
	'right-torunblocked' => 'Får redigera från automatiskt blockerade Tor-noder',
	'tag-tor-description' => 'Om denna taggen är på, så är en redigering gjord från en Tor-nod.',
	'tag-tor' => 'Gjord genom tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Huruvida ändringen gjordes genom en tor utgångsnod',
);

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'torblock-isexitnode' => 'IP adresi <strong>$1</strong> Tor çykyş düwüni diýlip blokirlenipdir.',
	'right-torunblocked' => 'Tor çykyş düwünleriniň awtomatik blokirlemelerinden aýlanyp geç',
	'tag-tor-description' => 'Eger bu teg sazlanan bolsa, bir Tor çykyş düwüninden bir özgerdiş geçirildi.',
	'tag-tor' => 'Tor bilen edilen',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Üýtgeşmäniň bir tor çykyş düwüninden edilip edilmändigi',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'torblock-desc' => "Nagpapahintulot na hadlangan/harangin ang labasan ng mga bugkol/alimpuso (''node'') ng bunton ng mga nakikipagugnayang hindi nagpapakilala (mga ''tor'') mula sa paggawa ng pagbabago sa isang wiki",
	'torblock-blocked' => "Ang adres ng IP mo, <strong>$1</strong>, ay kusang nakilala bilang isang bugkol/alimpuso (''node'') ng bunton ng mga nakikipagugnayang hindi nagpapakilala (mga ''tor'') .
Ang paggawa ng pagbabago mula sa isang bunton ng mga nakikipagugnayang hindi nagpapakilala ay hinadlangan upang maiwasan ang pangaabuso.",
	'torblock-isexitnode' => 'Ang adres ng IP na <strong>$1</strong> ay hinadlangan bilang isang labasang buhol ng Tor.',
	'right-torunblocked' => "Laktawan ang kusang mga paghahadlang ng mga bugkol/alimpuso (''node'') ng bunton ng mga nakikipagugnayang hindi nagpapakilala (mga ''tor'').",
	'tag-tor-description' => 'Kapag nakatakda ang tatak na ito, may isang pagbabagong ginawa mula sa labasang bugkol ng isang Tor.',
	'tag-tor' => 'Ginawa sa pamamagitan ng Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Kung ang pagbabago ay nagawa o hindi sa pamamagitan ng isang labasang bugkol na tor',
);

/** Turkish (Türkçe)
 * @author Joseph
 */
$messages['tr'] = array(
	'torblock-desc' => 'Tor çıkış nodlarının bir vikiyi değiştirmesinin engellemesini sağlar',
	'torblock-blocked' => 'IP adresiniz, <strong>$1</strong>, otomatik olarak bir tor çıkış nodu olarak algılandı.
Suistimali önlemek için tor üzerinden değişiklik engellendi.',
	'torblock-isexitnode' => 'IP adresi <strong>$1</strong> Tor çıkış nodu olarak engellenmiş.',
	'right-torunblocked' => 'Tor çıkış nodlarının otomatik engelini atla',
	'tag-tor-description' => 'Eğer bu etiket ayarlanmışsa, bir Tor çıkış nodundan bir değişiklik yapılmıştır.',
	'tag-tor' => 'Tor üzerinden yapılmıştır',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Değişikliğin bir tor çıkış nodundan yapılıp yapılmadığı',
);

/** Ukrainian (українська)
 * @author AS
 * @author Ahonc
 * @author Prima klasy4na
 */
$messages['uk'] = array(
	'torblock-desc' => 'Дозволяє блокувати вихідні вузли мережі Tor',
	'torblock-blocked' => 'Ваша IP-адреса, <strong>$1</strong>, була автоматично визначена як вихідний вузол мережі Tor.
Редагування шляхом Tor заборонене з метою уникнення зловживань.',
	'torblock-isexitnode' => 'IP адреса <strong>$1</strong> заблокована як вихідний вузол мережі Tor.',
	'right-torunblocked' => 'Обхід автоматичного блокування вузлів мережі Tor',
	'tag-tor-description' => 'Якщо встановлена ця мітка, значить редагування зроблене з вихідного вузла мережі Tor.',
	'tag-tor' => 'Зроблене через Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Чи зроблене редагування через вихідний вузол мережі Tor',
);

/** Urdu (اردو)
 * @author පසිඳු කාවින්ද
 */
$messages['ur'] = array(
	'abusefilter-edit-builder-vars-tor-exit-node' => 'چاہے یا نہیں میں تبدیلی ایک tor باہر نکلیں نوڈ کے ذریعے بنایا گیا تھا',
);

/** vèneto (vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'torblock-desc' => 'Permeti de inpedirghe la modifica de la wiki ai nodi de uscita Tor',
	'torblock-blocked' => 'El to indirisso IP, <strong>$1</strong>, el xe stà identificà automaticamente come un nodo de uscita Tor.
Le modifiche tramite Tor le xe blocà par evitar abusi.',
	'torblock-isexitnode' => "L'indirisso IP <strong>$1</strong> el xe blocà in quanto el xe un nodo de uscita Tor.",
	'right-torunblocked' => 'Scavalca i blochi automatici dei nodi de uscita Tor',
	'tag-tor-description' => 'Se sto tag el xe presente, vol dir che xe stà fato na modifica atravero un nodo de uscita de Tor.',
	'tag-tor' => 'Fato via Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Indica se la modifica la xe stà fata da un nodo de uscita TOR opure no',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'torblock-desc' => 'Cho phép cấm các nút thoát tor sửa đổi wiki',
	'torblock-blocked' => 'Địa chỉ IP của bạn, <strong>$1</strong>, đã bị xác định là một nút thoát tor.
Sửa đổi thông qua tor đã bị cấm để tránh sai phạm.',
	'torblock-isexitnode' => 'Địa chỉ IP <strong>$1</strong> bị cấm vì là nốt đi ra của Tor.',
	'right-torunblocked' => 'Bỏ qua các lệnh cấm tự động các nút thoát tor',
	'tag-tor-description' => 'Nếu thẻ này được thiết lập, sửa đổi do một nút thoát Tor thực hiện.',
	'tag-tor' => 'Thực hiện nhờ tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => 'Có thay đổi nào thực hiện qua nút thoát tor hay không',
);

/** Cantonese (粵語)
 * @author Shinjiman
 */
$messages['yue'] = array(
	'torblock-desc' => '容許tor出口點封鎖響一個wiki嘅編輯',
	'torblock-blocked' => '你嘅IP地址，<strong>$1</strong>已經被自動認定為一個tor嘅出口點。
經tor嘅編輯已經封鎖以防止濫用。',
	'right-torunblocked' => '繞過tor出口點嘅自動封鎖',
	'tag-tor-description' => '如果呢個標籤設定咗，個編輯就會由Tor出口點完成。',
	'tag-tor' => '經tor編輯',
	'abusefilter-edit-builder-vars-tor-exit-node' => '睇個修改係咪經過一個tor出口點完成',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Bencmq
 * @author Chenxiaoqino
 * @author Gaoxuewei
 * @author Liuxinyu970226
 * @author PhiLiP
 * @author Shinjiman
 * @author Wmr89502270
 * @author Xiaomingyan
 */
$messages['zh-hans'] = array(
	'torblock-desc' => '允许封禁通过tor节点在wiki中的编辑',
	'torblock-blocked' => '您的IP地址，<strong>$1</strong>，已被自动识别为tor节点。
经tor的编辑已被封禁以防止滥用。',
	'torblock-isexitnode' => 'IP地址<strong>$1</strong>被认为是Tor出口节点而遭到封禁。',
	'right-torunblocked' => '绕过对tor出口节点的自动封禁',
	'tag-tor-description' => '如果标记此标签，则将允许通过Tor节点编辑。',
	'tag-tor' => '通过Tor',
	'abusefilter-edit-builder-vars-tor-exit-node' => '修改是否经过Tor节点完成',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Alexsh
 * @author Gaoxuewei
 * @author Justincheng12345
 * @author Mark85296341
 * @author Shinjiman
 */
$messages['zh-hant'] = array(
	'torblock-desc' => '封鎖使用tor位址發出的匿名編輯',
	'torblock-blocked' => '您的 IP 位址<strong>$1</strong>已被系統自動認定為 tor 的節點，為防止破壞，經由 tor 發出的匿名編輯已經被封鎖。',
	'torblock-isexitnode' => 'IP 位址 <strong>$1</strong> 被認為是 Tor 出口節點而遭到封禁。',
	'right-torunblocked' => '自動繞過 tor 的節點',
	'tag-tor-description' => '如果這個標籤已標記，一個編輯從一個 Tor 節點中製造。',
	'tag-tor' => '經 Tor 編輯',
	'abusefilter-edit-builder-vars-tor-exit-node' => '修改是否經過一個 Tor 節點完成',
);
