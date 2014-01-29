<?php
/**
 * Internationalisation file for extension Patroller.
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>
 * @copyright © 2006 Rob Church
 * @license GNU General Public Licence 2.0
 */

$messages = array();

/** English
 * @author Rob Church
 */
$messages['en'] = array(
	'patrol'                 => 'Patrol edits',
	'patrol-desc'            => 'Enhanced patrolling interface with workload sharing',
	'patrol-endorse'         => 'Endorse',
	'patrol-revert'          => 'Revert',
	'patrol-revert-reason'   => 'Reason:',
	'patrol-skip'            => 'Skip',
	'patrol-reverting'       => 'Reverting: $1',
	'patrol-nonefound'       => 'No suitable edits could be found for patrolling.',
	'patrol-endorsed-ok'     => 'The edit was marked patrolled.',
	'patrol-endorsed-failed' => 'The edit could not be marked patrolled.',
	'patrol-reverted-ok'     => 'The edit was reverted.',
	'patrol-reverted-failed' => 'The edit could not be reverted.',
	'patrol-skipped-ok'      => 'Ignoring edit.',
	'patrol-reasons'         => "* Simple vandalism
* Newbie test
* See talk page",
	'patrol-another'         => 'Show another edit, if available.',
	'patrol-stopped'         => 'You have opted not to patrol another edit. $1',
	'patrol-resume'          => 'Click here to resume.',
	'group-patroller'        => 'Patrollers',
	'group-patroller-member' => '{{GENDER:$1|patroller}}',
	'grouppage-patroller'    => '{{ns:project}}:Patroller',
	'right-patroller'        => 'Patrol pages',
	'group-patroller.css'    => '/* CSS placed here will affect patrollers only */', # only translate this message to other languages if you have to change it
	'group-patroller.js'     => '/* JS placed here will affect patrollers only */', # only translate this message to other languages if you have to change it
);

/** Message documentation (Message documentation)
 * @author Aotake
 * @author EugeneZelenko
 * @author Ferrer
 * @author Jon Harald Søby
 * @author Purodha
 * @author The Evil IP address
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'patrol-desc' => '{{desc}}',
	'patrol-revert' => '{{Identical|Revert}}',
	'patrol-revert-reason' => '{{Identical|Reason}}',
	'patrol-skip' => '{{Identical|Skip}}',
	'patrol-reverting' => '{{Identical|Revert}}',
	'patrol-reverted-ok' => '{{Identical|Revert}}',
	'patrol-reverted-failed' => '{{Identical|Revert}}',
	'group-patroller' => '{{doc-group|patroller}}',
	'group-patroller-member' => '{{doc-group|patroller|member}}',
	'grouppage-patroller' => '{{doc-group|patroller|page}}',
	'right-patroller' => '{{doc-right|patroller}}',
);

/** Niuean (ko e vagahau Niuē)
 * @author Jose77
 */
$messages['niu'] = array(
	'patrol-revert-reason' => 'Kakano:',
);

/** Afrikaans (Afrikaans)
 * @author Arnobarnard
 * @author Naudefj
 */
$messages['af'] = array(
	'patrol-endorse' => 'Keur goed',
	'patrol-revert' => 'Rol terug',
	'patrol-revert-reason' => 'Rede:',
	'patrol-skip' => 'Slaan oor',
	'patrol-reverting' => '$1 aan die terugrol',
	'patrol-reverted-ok' => 'Die wysiging is teruggerol.',
	'patrol-reverted-failed' => 'Die wysiging kon nie teruggerol word nie.',
	'group-patroller' => 'Kontroleurs',
	'group-patroller-member' => 'Kontroleur',
);

/** Gheg Albanian (Gegë)
 * @author Mdupont
 */
$messages['aln'] = array(
	'patrol' => 'Patrol redaktimet',
	'patrol-desc' => 'ndërfaqe Enhanced patrullojnë me ndarjen e punës',
	'patrol-endorse' => 'Miratojë',
	'patrol-revert' => 'Rikthehem',
	'patrol-revert-reason' => 'Arsyeja:',
	'patrol-skip' => 'Skip',
	'patrol-reverting' => 'U kthye: $1',
	'patrol-nonefound' => 'Nuk ka të përshtatshme redaktimet mund të gjendet për patrullimin.',
	'patrol-endorsed-ok' => 'Redakto u shënua patrulluar.',
	'patrol-endorsed-failed' => 'Redakto nuk mund të shënohen patrulluar.',
	'patrol-reverted-ok' => 'I redaktimit ishte rikthyer.',
	'patrol-reverted-failed' => 'Redakto nuk mund të rikthyer.',
	'patrol-skipped-ok' => 'Injoruar redakto.',
	'patrol-reasons' => '* Vandalizmin Simple * Newbie * provë më të flasim faqe',
	'patrol-another' => 'Trego tjetër modifikoni, nëse është e mundur.',
	'patrol-stopped' => 'Ju nuk kanë vendosur për të patrulluar një tjetër redaktuar. $1',
	'patrol-resume' => 'Kliko këtu për të rifilluar.',
	'group-patroller' => 'Patrollers',
	'group-patroller-member' => 'patroller',
	'grouppage-patroller' => '{{ns:project}}: Patroller',
	'right-patroller' => 'faqe Patrol',
);

/** Amharic (አማርኛ)
 * @author Codex Sinaiticus
 */
$messages['am'] = array(
	'patrol-revert-reason' => 'ምክንያት:',
);

/** Aragonese (Aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'patrol-revert' => 'Revertir',
	'patrol-revert-reason' => 'Razón:',
	'patrol-reverting' => 'Revertindo: $1',
	'group-patroller' => 'Patrullers',
	'group-patroller-member' => '{{GENDER:$1|patrullero|patrullera}}',
);

/** Old English (Ænglisc)
 * @author Wōdenhelm
 */
$messages['ang'] = array(
	'group-patroller' => 'Ƿeardmenn',
	'group-patroller-member' => 'ƿeard',
);

/** Arabic (العربية)
 * @author AwamerT
 * @author Meno25
 * @author OsamaK
 */
$messages['ar'] = array(
	'patrol' => 'تعديلات مراجعة',
	'patrol-desc' => 'واجهة مراجعة محسنة مع تشارك ضغط العمل',
	'patrol-endorse' => 'تأكيد',
	'patrol-revert' => 'استرجع',
	'patrol-revert-reason' => 'السبب:',
	'patrol-skip' => 'تجاهل',
	'patrol-reverting' => 'استرجاع: $1',
	'patrol-nonefound' => 'لم يتم العثور على تعديلات مناسبة للمراجعة.',
	'patrol-endorsed-ok' => 'تم تعليم التعديل كمراجع.',
	'patrol-endorsed-failed' => 'هذا التعديل لم يمكن تعليمه كمراجع.',
	'patrol-reverted-ok' => 'التعديل تم استرجاعه.',
	'patrol-reverted-failed' => 'لم يمكن استرجاع التعديل.',
	'patrol-skipped-ok' => 'تجاهل التعديل.',
	'patrol-reasons' => '* تخريب بسيط
* تجربة مستخدم جديد
* انظر صفحة النقاش',
	'patrol-another' => 'اعرض تعديلا آخر، إن أمكن.',
	'patrol-stopped' => 'لقد اخترت عدم مراجعة تعديل آخر. $1',
	'patrol-resume' => 'اضغط هنا للمتابعة.',
	'group-patroller' => 'مراجعون',
	'group-patroller-member' => '{{GENDER:$1|مدقق}}',
	'grouppage-patroller' => '{{ns:project}}:مراجع',
	'right-patroller' => 'مراجعة الصفحات',
);

/** Aramaic (ܐܪܡܝܐ)
 * @author Basharh
 */
$messages['arc'] = array(
	'patrol-revert-reason' => 'ܥܠܬܐ:',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 */
$messages['arz'] = array(
	'patrol' => 'تعديلات مراجعة',
	'patrol-desc' => 'واجهة مراجعة محسنة مع تشارك ضغط العمل',
	'patrol-endorse' => 'تأكيد',
	'patrol-revert' => 'استرجاع',
	'patrol-revert-reason' => 'السبب:',
	'patrol-skip' => 'تجاهل',
	'patrol-reverting' => 'استرجاع: $1',
	'patrol-nonefound' => 'لم يتم العثور على تعديلات مناسبة للمراجعة.',
	'patrol-endorsed-ok' => 'تم تعليم التعديل كمراجع.',
	'patrol-endorsed-failed' => 'هذا التعديل لم يمكن تعليمه كمراجع.',
	'patrol-reverted-ok' => 'التعديل تم استرجاعه.',
	'patrol-reverted-failed' => 'لم يمكن استرجاع التعديل.',
	'patrol-skipped-ok' => 'تجاهل التعديل.',
	'patrol-reasons' => '* تخريب بسيط
* تجربة مستخدم جديد
* انظر صفحة النقاش',
	'patrol-another' => 'اعرض تعديلا آخر، إن أمكن.',
	'patrol-stopped' => 'لقد اخترت عدم مراجعة تعديل آخر. $1',
	'patrol-resume' => 'اضغط هنا للمتابعة.',
	'group-patroller' => 'مراجعون',
	'group-patroller-member' => 'مراجع',
	'grouppage-patroller' => '{{ns:project}}:مراجع',
	'right-patroller' => 'مراجعة الصفحات',
);

/** Asturian (Asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'group-patroller' => 'Patrulleros',
	'group-patroller-member' => '{{GENDER:$1|patrulleru|patrullera}}',
);

/** Azerbaijani (Azərbaycanca)
 * @author Cekli829
 * @author Vugar 1981
 */
$messages['az'] = array(
	'patrol-revert-reason' => 'Səbəb:',
	'group-patroller' => 'Patrullar',
	'group-patroller-member' => 'patrul',
);

/** Bashkir (Башҡортса)
 * @author Assele
 * @author Haqmar
 */
$messages['ba'] = array(
	'patrol' => 'Тикшереү',
	'patrol-desc' => 'Эш йөкләмәһен бүлеү менән яҡшыртылған тикшереү интерфейсы',
	'patrol-endorse' => 'Раҫларға',
	'patrol-revert' => 'Кире алырға',
	'patrol-revert-reason' => 'Сәбәп:',
	'patrol-skip' => 'Торғоҙоп ҡалдырырға',
	'patrol-reverting' => 'Кире алыу: $1',
	'patrol-nonefound' => 'Тикшереү өсөн кәрәкле өлгөләрҙе табып булмай.',
	'patrol-endorsed-ok' => 'Өлгө тикшерелгән тип билдәләнде.',
	'patrol-endorsed-failed' => 'Өлгөнө тикшерелгән тип билдәләп булмай.',
	'patrol-reverted-ok' => 'Өлгө кире алынды.',
	'patrol-reverted-failed' => 'Өлгөнө кире алып булманы.',
	'patrol-skipped-ok' => 'Өлгө торғоҙоп ҡалдырылды.',
	'patrol-reasons' => '* Ябай вандаллыҡ
* Яңы ҡатнашыусыны һынау
* Фекер алышыу битен ҡара',
	'patrol-another' => 'Мөмкин булһа, икенсе өлгөнө күрһәтергә.',
	'patrol-stopped' => 'Һеҙ икенсе өлгөнө тикшермәҫкә булдығыҙ. $1',
	'patrol-resume' => 'Дауам итер өсөн, ошонда баҫығыҙ.',
	'group-patroller' => 'Тикшереүселәр',
	'group-patroller-member' => '{{GENDER:$1|патруль}}',
	'grouppage-patroller' => '{{ns:project}}:Тикшерелеүсе',
	'right-patroller' => 'Биттәрҙе тикшереү',
);

/** Bikol Central (Bikol Central)
 * @author Filipinayzd
 */
$messages['bcl'] = array(
	'patrol-endorse' => 'Iindorso',
	'patrol-revert-reason' => 'Rason:',
	'patrol-skip' => 'Laktawan',
	'patrol-reverting' => 'Pigbabalik: $1',
	'patrol-resume' => 'Lagatik digdi tangarig magpadagos.',
);

/** Belarusian (Беларуская)
 * @author Тест
 */
$messages['be'] = array(
	'patrol-revert-reason' => 'Прычына:',
);

/** Belarusian (Taraškievica orthography) (‪Беларуская (тарашкевіца)‬)
 * @author EugeneZelenko
 * @author Jim-by
 * @author Wizardist
 */
$messages['be-tarask'] = array(
	'patrol' => 'Патруляваныя рэдагаваньні',
	'patrol-desc' => 'Пашыраны інтэрфэйс патруляваньня з падзелам працоўнай нагрузкі',
	'patrol-endorse' => 'Пацьвердзіць',
	'patrol-revert' => 'Адмяніць',
	'patrol-revert-reason' => 'Прычына:',
	'patrol-skip' => 'Прапусьціць',
	'patrol-reverting' => 'Адкат: $1',
	'patrol-nonefound' => 'Немагчыма знайсьці адпаведныя рэдагаваньні для патруляваньня.',
	'patrol-endorsed-ok' => 'Рэдагаваньне было пазначанае як патруляванае.',
	'patrol-endorsed-failed' => 'Рэдагаваньне ня можа быць пазначанае як патруляванае.',
	'patrol-reverted-ok' => 'Рэдагаваньне было адмененае.',
	'patrol-reverted-failed' => 'Рэдагаваньне ня можа быць адмененае.',
	'patrol-skipped-ok' => 'Рэдагаваньне прапушчана.',
	'patrol-reasons' => '* Просты вандалізм
* Тэст новага ўдзельніка
* Глядзіце старонку абмеркаваньня',
	'patrol-another' => 'Паказаць іншае рэдагаваньне, калі яно існуе.',
	'patrol-stopped' => 'Вы вырашылі не патруляваць іншае рэдагаваньне. $1',
	'patrol-resume' => 'Націсьніце тут, каб працягваць.',
	'group-patroller' => 'Патруліруючыя',
	'group-patroller-member' => '{{GENDER:$1|патрулюючы|патрулюючая}}',
	'grouppage-patroller' => '{{ns:project}}:Патруліруючыя',
	'right-patroller' => 'патруляваньне старонак',
);

/** Bulgarian (Български)
 * @author Borislav
 * @author DCLXVI
 * @author Spiritia
 */
$messages['bg'] = array(
	'patrol-revert' => 'Възвръщане',
	'patrol-revert-reason' => 'Причина:',
	'patrol-skip' => 'Пропускане',
	'patrol-reverting' => 'Възвръщане: $1',
	'patrol-endorsed-ok' => 'Редакцията беше отбелязана като проверена.',
	'patrol-endorsed-failed' => 'Редакцията не може да бъде отбелязана като проверена.',
	'patrol-reverted-ok' => 'Редакцията е възвърната',
	'patrol-reverted-failed' => 'Редакцията не може да бъде възвърната.',
	'patrol-skipped-ok' => 'Пренебрегване на редакцията.',
	'patrol-reasons' => '* Обикновен вандализъм
* Упражнения извън пясъчника
* Виж беседата',
	'patrol-another' => 'Показване на други евентуални редакции.',
	'patrol-resume' => 'Натиснете тук за продължаване.',
	'group-patroller' => 'Патрульори',
	'group-patroller-member' => 'Патрульор',
	'grouppage-patroller' => '{{ns:project}}:Патрулиращи',
);

/** Bengali (বাংলা)
 * @author Bellayet
 * @author Wikitanvir
 */
$messages['bn'] = array(
	'patrol' => 'সম্পাদনা যাচাই',
	'patrol-endorse' => 'সত্যায়িত',
	'patrol-revert' => 'বাতিল',
	'patrol-revert-reason' => 'কারণ:',
	'patrol-skip' => 'উপেক্ষা করো',
	'patrol-reverting' => 'বাতিল করা হচ্ছে: $1',
	'patrol-skipped-ok' => 'সম্পাদনা উপেক্ষা করুন।',
	'group-patroller' => 'পর্যবেক্ষক',
	'group-patroller-member' => 'পর্যবেক্ষক',
	'right-patroller' => 'পাতা পরীক্ষণ',
);

/** Breton (Brezhoneg)
 * @author Fohanno
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'patrol' => "Gwiriekadur ar c'hemmoù",
	'patrol-desc' => 'Etrefas patrouilhañ gwellaet evit rannañ ar garg labour',
	'patrol-endorse' => 'Aprouiñ',
	'patrol-revert' => 'Assevel',
	'patrol-revert-reason' => 'Abeg :',
	'patrol-skip' => 'Lezel a-gostez',
	'patrol-reverting' => 'O tisteuler : $1',
	'patrol-nonefound' => "N'eus bet kavet degasadenn genglotus ebet da wiriañ.",
	'patrol-endorsed-ok' => "Merket eo bet ar c'hemm evel gwiriekaet.",
	'patrol-endorsed-failed' => "Dibosupl eo bet gwiriañ ar c'hemm.",
	'patrol-reverted-ok' => "Distaolet eo bet ar c'hemm-se.",
	'patrol-reverted-failed' => "N'eus ket bet tu nullañ ar c'hemm-mañ.",
	'patrol-skipped-ok' => "O lezel ar c'hemm a-gostez.",
	'patrol-reasons' => '* Vandalerezh eeun
* Taol esae un den nevez
* Gwelet ar bajenn gaozeal',
	'patrol-another' => "Diskouez ur c'hemm all, ma 'z eo posupl.",
	'patrol-stopped' => "Dibabet hoc'h eus chom hep gwiriañ degasadenn ebet all. $1",
	'patrol-resume' => "Klikit amañ evit kenderc'hel.",
	'group-patroller' => 'Patrouilherien',
	'group-patroller-member' => '{{GENDER:$1|patrouilher|patrouilherez}}',
	'grouppage-patroller' => '{{ns:project}}:Patrouilher',
	'right-patroller' => 'Pajennoù patrouilhañ',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'patrol' => 'Patrolirane izmjene',
	'patrol-desc' => 'Napredni interfejs za kontrolu sa dijeljenim radom',
	'patrol-endorse' => 'Potvrdi',
	'patrol-revert' => 'Vrati',
	'patrol-revert-reason' => 'Razlog:',
	'patrol-skip' => 'Preskoči',
	'patrol-reverting' => 'Vraćanje: $1',
	'patrol-nonefound' => 'Nije mogla biti nađena odgovarajuća izmjena za patroliranje.',
	'patrol-endorsed-ok' => 'Izmjena je označena kao patrolirana.',
	'patrol-endorsed-failed' => 'Izmjena nije mogla biti označena kao patrolirana.',
	'patrol-reverted-ok' => 'Izmjena je vraćena.',
	'patrol-reverted-failed' => 'Izmjena nije mogla biti vraćena.',
	'patrol-skipped-ok' => 'Zanemarujem izmjenu.',
	'patrol-reasons' => '* Jednostavni vandalizam
* Početnička proba
* Vidi stranicu za razgovor',
	'patrol-another' => 'Prikaži slijedeću izmjenu, ako je moguće.',
	'patrol-stopped' => 'Vi ste odabrali da ne provjeravate još jednu izmjenu. $1',
	'patrol-resume' => 'Kliknite ovdje za nastavak.',
	'group-patroller' => 'Patroleri',
	'group-patroller-member' => 'patroler',
	'grouppage-patroller' => '{{ns:project}}:Patroler',
	'right-patroller' => 'Provjera stranica',
);

/** Catalan (Català)
 * @author El libre
 * @author SMP
 * @author Solde
 */
$messages['ca'] = array(
	'patrol-revert-reason' => 'Motiu:',
	'group-patroller' => 'Patrullers',
	'group-patroller-member' => '{{GENDER:$1|patruller|patrullera}}',
	'grouppage-patroller' => '{{ns:project}}:Patrullers',
);

/** Chechen (Нохчийн) */
$messages['ce'] = array(
	'patrol-revert-reason' => 'Бахьан:',
);

/** Sorani (کوردی) */
$messages['ckb'] = array(
	'patrol-revert-reason' => 'هۆکار:',
);

/** Czech (Česky)
 * @author Matěj Grabovský
 * @author Mormegil
 */
$messages['cs'] = array(
	'group-patroller' => 'Strážci',
	'group-patroller-member' => '{{GENDER:$1|strážce|strážkyně}}',
	'grouppage-patroller' => '{{ns:project}}:Strážci',
);

/** Welsh (Cymraeg)
 * @author Xxglennxx
 */
$messages['cy'] = array(
	'group-patroller' => 'Patrolwyr',
	'group-patroller-member' => 'patrolwr',
);

/** Danish (Dansk)
 * @author Jon Harald Søby
 * @author Masz
 * @author Peter Alberti
 */
$messages['da'] = array(
	'patrol-endorse' => 'Godkendt',
	'patrol-revert' => 'Tilbagerul',
	'patrol-revert-reason' => 'Begrundelse:',
	'patrol-skip' => 'Spring over.',
	'patrol-reverting' => 'Tilbageruller: $1',
	'patrol-nonefound' => 'Ingen passende redigeringer kunne findes til at blive patruljeret.',
	'patrol-endorsed-ok' => 'Redigeringen blev markeret som patruljeret.',
	'patrol-endorsed-failed' => 'Denne redigering kunne ikke blive markeret som patruljeret.',
	'patrol-reverted-ok' => 'Redigerinen er blevet tilbagestillet.',
	'patrol-reverted-failed' => 'Dene redigering kunne ikke tilbagestilles.',
	'patrol-skipped-ok' => 'Ignorér redigering.',
	'patrol-reasons' => '* Enkelt vandalisme
* Nybegynder-test
* Se diskussionssiden',
	'patrol-another' => 'Vis en anden redigering, hvis det er tilgængeligt.',
	'patrol-stopped' => 'Du har valgt ikke at patruljere en anden ændring. $1',
	'patrol-resume' => 'Tryk her for at fortsætte.',
	'group-patroller' => 'Patruljanter',
	'group-patroller-member' => '{{GENDER:$1|patruljant}}',
	'grouppage-patroller' => '{{ns:project}}:Patruljanter',
	'right-patroller' => 'Patruljere ssider',
);

/** German (Deutsch)
 * @author Kghbln
 * @author Raimond Spekking
 */
$messages['de'] = array(
	'patrol' => 'Kontrolle der Bearbeitungen',
	'patrol-desc' => 'Ermöglicht einer erweiterte Schnittstelle zur Kontrolle von Bearbeitungen',
	'patrol-endorse' => 'Bestätigen',
	'patrol-revert' => 'Zurücksetzen',
	'patrol-revert-reason' => 'Grund:',
	'patrol-skip' => 'Überspringen',
	'patrol-reverting' => 'Zurücksetzen: $1',
	'patrol-nonefound' => 'Es wurden keine passenden Bearbeitungen zum Kontrollieren gefunden.',
	'patrol-endorsed-ok' => 'Diese Bearbeitung wurde bereits als kontrolliert markiert.',
	'patrol-endorsed-failed' => 'Diese Bearbeitung kann nicht als kontrolliert markiert werden.',
	'patrol-reverted-ok' => 'Die Bearbeitung wurde zurückgesetzt.',
	'patrol-reverted-failed' => 'Die Bearbeitung kann nicht zurückgesetzt werden.',
	'patrol-skipped-ok' => 'Bearbeitung wird ignoriert.',
	'patrol-reasons' => '* Einfacher Vandalismus
* Test eines Neulings
* Siehe Diskussionsseite',
	'patrol-another' => 'Zeige eine weitere Bearbeitung, sofern verfügbar.',
	'patrol-stopped' => 'Du hast entschieden, keine weitere Bearbeitung zu kontrollieren. $1',
	'patrol-resume' => 'Klicke hier, um weiterzumachen.',
	'group-patroller' => 'Kontrolleure',
	'group-patroller-member' => '{{GENDER:$1|Kontrolleur|Kontrolleurin}}',
	'grouppage-patroller' => '{{ns:project}}:Kontrolleur',
	'right-patroller' => 'Seiten kontrollieren',
);

/** German (formal address) (‪Deutsch (Sie-Form)‬)
 * @author Raimond Spekking
 */
$messages['de-formal'] = array(
	'patrol-stopped' => 'Sie haben entschieden, keine weitere Bearbeitung zu kontrollieren. $1',
	'patrol-resume' => 'Klicken Sie hier zum Fortsetzen.',
);

/** Zazaki (Zazaki)
 * @author Aspar
 */
$messages['diq'] = array(
	'group-patroller' => 'dewriyeyi',
	'group-patroller-member' => 'dewriye',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'patrol' => 'Změny doglědowaś',
	'patrol-desc' => 'Pólěpšony doglědowański interfejs z rozdźělenim źěłoweje śěźe',
	'patrol-endorse' => 'Schwaliś',
	'patrol-revert' => 'Slědk stajiś',
	'patrol-revert-reason' => 'Pśicyna:',
	'patrol-skip' => 'Pśeskócyś',
	'patrol-reverting' => 'Slědkstajenje: $1',
	'patrol-nonefound' => 'Žedne góźbne změny za doglědowanje namakane.',
	'patrol-endorsed-ok' => 'Toś ta změna jo se markěrowała ako doglědowana.',
	'patrol-endorsed-failed' => 'Změna njejo se dała ako doglědowana markěrowaś.',
	'patrol-reverted-ok' => 'Změna jo se slědk stajiła.',
	'patrol-reverted-failed' => 'Změna njejo se dała slědk stajiś.',
	'patrol-skipped-ok' => 'Změna se ignorěrujo.',
	'patrol-reasons' => '* Jadnory wandalizm
* Test nowacka
* Glědaj diskusijny bok',
	'patrol-another' => 'Drugu změnu pokazaś, jolic k dispoziciji.',
	'patrol-stopped' => 'Sy rozsuźił žednu dalšnu změnu doglědowaś. $1',
	'patrol-resume' => 'Klikni sem, aby pokšacował.',
	'group-patroller' => 'Doglědowarje',
	'group-patroller-member' => '{{GENDER:$1|doglědowaŕ|doglědowarka}}',
	'grouppage-patroller' => '{{ns:project}}:Doglědowaŕ',
	'right-patroller' => 'Boki doglědowaś',
);

/** Greek (Ελληνικά)
 * @author Consta
 * @author Dada
 * @author Konsnos
 * @author ZaDiak
 */
$messages['el'] = array(
	'patrol' => 'Επεξεργασίες περιπολίας',
	'patrol-endorse' => 'Επικυρώστε',
	'patrol-revert' => 'Αναίρεση',
	'patrol-revert-reason' => 'Λόγος:',
	'patrol-skip' => 'Παράβλεψη',
	'patrol-reverting' => 'Επαναφορά: $1',
	'patrol-endorsed-ok' => 'Η επεξεργασία σημάνθηκε ως περιπολημένο.',
	'patrol-endorsed-failed' => 'Η επεξεργασία δεν μπορεί να σημανθεί ως υπό περιπολία.',
	'patrol-reverted-ok' => 'Η επεξεργασία αναστράφηκε.',
	'patrol-reverted-failed' => 'Η επεξεργασία δεν μπορούσε να αναστραφεί.',
	'patrol-skipped-ok' => 'Αγνόηση επεξεργασίας.',
	'patrol-reasons' => '* Βανδαλισμός 
* Δοκιμή νέου χρήστη 
* Βλέπε σελίδα συζήτησης',
	'patrol-another' => 'Προβολή άλλης επεξεργασίας, εάν είναι διαθέσιμη.',
	'patrol-resume' => 'Πατήστε εδώ για συνέχεια.',
	'group-patroller' => 'Περιπολητές',
	'group-patroller-member' => 'περιπολητής',
	'grouppage-patroller' => '{{ns:project}}:Περιπολητής',
	'right-patroller' => 'Σελίδες περιπολίας',
);

/** Esperanto (Esperanto)
 * @author Melancholie
 * @author Yekrats
 */
$messages['eo'] = array(
	'patrol' => 'Patroli redaktojn',
	'patrol-desc' => 'Progresa patrolada interfaco kun kunlaboriloj',
	'patrol-endorse' => 'Aprobi',
	'patrol-revert' => 'Restarigi',
	'patrol-revert-reason' => 'Kialo:',
	'patrol-skip' => 'Forlasi',
	'patrol-reverting' => 'Restarigante: $1',
	'patrol-nonefound' => 'Neniuj taŭgaj redaktoj estis troveblaj por patrolado.',
	'patrol-endorsed-ok' => 'La redakto estis markita kiel patrolita.',
	'patrol-endorsed-failed' => 'Ĉi tiu redakto ne povis esti markita kiel patrolita.',
	'patrol-reverted-ok' => 'La redakto estis restarigita.',
	'patrol-reverted-failed' => 'Ne eblis restarigi la redakton.',
	'patrol-skipped-ok' => 'Ignorante redakton.',
	'patrol-reasons' => '* Simpla vandalismo
* Novula testo
* Vidu diskuto-paĝon',
	'patrol-another' => 'Montri alian redakton, se haveblan.',
	'patrol-stopped' => 'Vi elektis ne patroli alian redakton. $1',
	'patrol-resume' => 'Klaku ĉi tie por reaktivigi.',
	'group-patroller' => 'Patrolantoj',
	'group-patroller-member' => '{{GENDER:$1|patrolanto|patrolantino}}',
	'grouppage-patroller' => '{{ns:project}}:Patrolanto',
	'right-patroller' => 'Patroli paĝojn',
);

/** Spanish (Español)
 * @author Armando-Martin
 * @author Crazymadlover
 * @author Dferg
 * @author Manuelt15
 * @author Sanbec
 * @author Titoxd
 * @author Translationista
 */
$messages['es'] = array(
	'patrol' => 'Verificación de ediciones',
	'patrol-desc' => 'Interfaz de verificación mejorada con compartición de carga de trabajo',
	'patrol-endorse' => 'Aprobar',
	'patrol-revert' => 'Revertir',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Omitir',
	'patrol-reverting' => 'Revirtiendo: $1',
	'patrol-nonefound' => 'No hay ediciones disponibles para verificar.',
	'patrol-endorsed-ok' => 'La edición fue marcada como verificada.',
	'patrol-endorsed-failed' => 'La edición no se pudo marcar como verificada.',
	'patrol-reverted-ok' => 'La edición fue revertida.',
	'patrol-reverted-failed' => 'La edición no pudo ser revertida.',
	'patrol-skipped-ok' => 'Ignorando la edición.',
	'patrol-reasons' => '* Vandalismo simple
* Prueba de usuario novato
* Ver la página de discusión',
	'patrol-another' => 'Mostrar otra edición (si está disponible).',
	'patrol-stopped' => 'Has optado no marcar otra edición como verificada. $1',
	'patrol-resume' => 'Haz click aquí para continuar.',
	'group-patroller' => 'Patrulleros',
	'group-patroller-member' => '{{GENDER:$1|patrullero|patrullera}}',
	'grouppage-patroller' => '{{ns:project}}:Verificador',
	'right-patroller' => 'Verificar páginas',
);

/** Estonian (Eesti)
 * @author Pikne
 */
$messages['et'] = array(
	'group-patroller' => 'Kontrollijad',
	'group-patroller-member' => 'kontrollija',
	'grouppage-patroller' => '{{ns:project}}:Kontrollijad',
);

/** Basque (Euskara)
 * @author Kobazulo
 * @author Unai Fdz. de Betoño
 */
$messages['eu'] = array(
	'patrol-revert-reason' => 'Arrazoia:',
	'group-patroller' => 'Patruilariak',
	'group-patroller-member' => 'patruilaria',
	'grouppage-patroller' => '{{ns:project}}:Patruilaria',
	'right-patroller' => 'Orriak patruilatu',
);

/** Persian (فارسی)
 * @author Ebraminio
 * @author Huji
 * @author Mjbmr
 * @author Sahim
 * @author ZxxZxxZ
 */
$messages['fa'] = array(
	'patrol' => 'گشت‌زنی ویرایش‌ها',
	'patrol-desc' => 'رابط بهبودیافتهٔ گشت با امکان به اشتراک گذاری پرونده بارگذاری‌شده',
	'patrol-endorse' => 'تأیید',
	'patrol-revert' => 'واگردانی',
	'patrol-revert-reason' => 'دلیل:',
	'patrol-skip' => 'گذشتن',
	'patrol-reverting' => 'واگردانی: $1',
	'patrol-nonefound' => 'ویرایش مناسب برای گشت‌زنی پیدا نشد.',
	'patrol-endorsed-ok' => 'ویرایش برچسب گشت خورد.',
	'patrol-endorsed-failed' => 'نشد به ویرایش برچسب گشت زد.',
	'patrol-reverted-ok' => 'ویرایش واگردانی شد.',
	'patrol-reverted-failed' => 'ویرایش را نمی‌شد واگردانی کرد.',
	'patrol-skipped-ok' => 'ویرایش نادیده گرفته شد.',
	'patrol-reasons' => '* خرابکاری ساده
* آزمایش تازه‌کار
* رجوع شود به صفحهٔ بحث',
	'patrol-another' => 'ویرایشی دیگر را در صورت وجود نمایش بده.',
	'patrol-stopped' => 'شما انتخاب کردید که ویرایشی دیگر را گشت نزنید. $1',
	'patrol-resume' => 'برای ادامه اینجا کلیک کنید.',
	'group-patroller' => 'گشت‌زنان',
	'group-patroller-member' => 'گشت‌زن',
	'grouppage-patroller' => '{{ns:project}}:گشت‌زن',
	'right-patroller' => 'گشت‌زنی صفحه‌ها',
);

/** Finnish (Suomi)
 * @author Cimon Avaro
 * @author Crt
 * @author Nike
 * @author Str4nd
 */
$messages['fi'] = array(
	'patrol' => 'Tarkasta muokkauksia',
	'patrol-desc' => 'Tehostettu liittymä, joka mahdollistaa työtaakan jakamisen.',
	'patrol-endorse' => 'Kannatetaan',
	'patrol-revert' => 'Palauta',
	'patrol-revert-reason' => 'Syy',
	'patrol-skip' => 'Ohita',
	'patrol-reverting' => 'Palautetaan $1',
	'patrol-nonefound' => 'Ei sopivia muokkauksia, joita voitaisiin tarkistaa.',
	'patrol-endorsed-ok' => 'Muokkaus merkittiin tarkastetuksi.',
	'patrol-endorsed-failed' => 'Muokkausta ei voitu merkitä tarkastetuksi.',
	'patrol-reverted-ok' => 'Muokkaus kumottiin.',
	'patrol-reverted-failed' => 'Muokkausta ei voitu palauttaa.',
	'patrol-skipped-ok' => 'Ohitetaan muokkaus.',
	'patrol-reasons' => '* Selvää vandalismia
* Tulokkaan kokeilu
* Katso keskustelusivu',
	'patrol-another' => 'Näytä toinen muokkaus, jos saatavilla.',
	'patrol-stopped' => 'Olet valinnut olla tarkastamatta lisää muokkauksia. $1',
	'patrol-resume' => 'Napsauta jatkaaksesi.',
	'group-patroller' => 'tarkastajat',
	'group-patroller-member' => '{{GENDER:$1|tarkastaja}}',
	'grouppage-patroller' => '{{ns:project}}:Tarkastajat',
	'right-patroller' => 'Tarkastaa sivuja',
);

/** French (Français)
 * @author Grondin
 * @author IAlex
 * @author Urhixidur
 * @author Verdy p
 */
$messages['fr'] = array(
	'patrol' => 'Vérification des modifications',
	'patrol-desc' => 'Interface mettant en valeur le patrouillage collaboratif.',
	'patrol-endorse' => 'Accepter',
	'patrol-revert' => 'Rétablir',
	'patrol-revert-reason' => 'Motif :',
	'patrol-skip' => 'Sauter',
	'patrol-reverting' => 'Annulation : $1',
	'patrol-nonefound' => 'Aucune modification correspondante n’a pu être trouvée pour la vérification.',
	'patrol-endorsed-ok' => 'L’édition a été marquée comme vérifiée.',
	'patrol-endorsed-failed' => 'L’édition n’a pu être vérifiée.',
	'patrol-reverted-ok' => 'L’édition a été annulée.',
	'patrol-reverted-failed' => 'L’édition n’a pu être annulée.',
	'patrol-skipped-ok' => 'Ignorer l’édition.',
	'patrol-reasons' => '* Simple vandalisme
* Test de débutant
* Voir page de discussion',
	'patrol-another' => 'Voir une autre modification, si disponible.',
	'patrol-stopped' => 'Vous avez choisi de ne pas vérifier une autre modification. $1',
	'patrol-resume' => 'Cliquer ici pour reprendre.',
	'group-patroller' => 'Patrouilleurs',
	'group-patroller-member' => 'patrouilleur',
	'grouppage-patroller' => '{{ns:project}}:Patrouilleur',
	'right-patroller' => 'Pages de patrouille',
);

/** Franco-Provençal (Arpetan)
 * @author Cedric31
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'patrol' => 'Contrôlo des changements',
	'patrol-endorse' => 'Accèptar',
	'patrol-revert' => 'Rètablir',
	'patrol-revert-reason' => 'Rêson :',
	'patrol-skip' => 'Passar',
	'patrol-reverting' => 'Anulacion : $1',
	'patrol-endorsed-ok' => 'Lo changement at étâ marcâ coment controlâ.',
	'patrol-endorsed-failed' => 'Lo changement at pas possu étre controlâ.',
	'patrol-reverted-ok' => 'Lo changement at étâ anulâ.',
	'patrol-reverted-failed' => 'Lo changement at pas possu étre anulâ.',
	'patrol-skipped-ok' => 'Ignorar lo changement.',
	'patrol-reasons' => '* Simplo vandalismo
* Èprôva de comencient
* Vêde la pâge de discussion',
	'patrol-another' => 'Fâre vêre un ôtro changement, se disponiblo.',
	'patrol-resume' => 'Clicar ique por reprendre.',
	'group-patroller' => 'Patrolyors',
	'group-patroller-member' => 'patrolyor',
	'grouppage-patroller' => '{{ns:project}}:Patrolyors',
	'right-patroller' => 'Controlar des pâges',
);

/** Northern Frisian (Nordfriisk)
 * @author Murma174
 */
$messages['frr'] = array(
	'group-patroller' => 'Kontrolöören',
	'group-patroller-member' => '{{GENDER:$1|kontrolöör}}',
);

/** Western Frisian (Frysk)
 * @author Snakesteuben
 */
$messages['fy'] = array(
	'patrol-revert' => 'Werom sette',
);

/** Galician (Galego)
 * @author Alma
 * @author Toliño
 * @author Xosé
 */
$messages['gl'] = array(
	'patrol' => 'Patrullar edicións',
	'patrol-desc' => 'Realzado da interface de patrulla coa parte que toca da cantidade de traballo',
	'patrol-endorse' => 'Apoiar',
	'patrol-revert' => 'Reverter',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Saltar',
	'patrol-reverting' => 'Revertendo: $1',
	'patrol-nonefound' => 'Non se atoparon edicións adecuadas para patrullar.',
	'patrol-endorsed-ok' => 'Marcouse esta edición como patrullada.',
	'patrol-endorsed-failed' => 'Non se puido marcar a edición como patrullada.',
	'patrol-reverted-ok' => 'Desfíxose esta edición.',
	'patrol-reverted-failed' => 'Non se puido desfacer esta edición.',
	'patrol-skipped-ok' => 'Ignórase a edición.',
	'patrol-reasons' => '* Simple vandalismo
* Probas de novato
* Ver a páxina de conversa',
	'patrol-another' => 'Mostrar outra edición, se existe.',
	'patrol-stopped' => 'Optou por non patrullar outra edición. $1',
	'patrol-resume' => 'Prema aquí para continuar.',
	'group-patroller' => 'Patrulleiros',
	'group-patroller-member' => '{{GENDER:$1|patrulleiro|patrulleira}}',
	'grouppage-patroller' => '{{ns:project}}:Patrulleiro',
	'right-patroller' => 'Patrullar páxinas',
);

/** Gothic (Gothic)
 * @author Crazymadlover
 * @author Jocke Pirat
 */
$messages['got'] = array(
	'patrol-revert-reason' => '𐍆𐌰𐌹𐍂𐌹𐌽𐌰:',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Crazymadlover
 */
$messages['grc'] = array(
	'patrol-revert-reason' => 'Αἰτία:',
);

/** Swiss German (Alemannisch)
 * @author Als-Chlämens
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'patrol' => 'Bearbeitige as Fäldhieter aaluege',
	'patrol-desc' => 'Erwytereti Schnittstell fir s Kontrolliere vu Bearbeitige',
	'patrol-endorse' => 'Bstätige',
	'patrol-revert' => 'Zruggsetze',
	'patrol-revert-reason' => 'Grund:',
	'patrol-skip' => 'Ibergumpe',
	'patrol-reverting' => 'Zruggsetze: $1',
	'patrol-nonefound' => 'S sin kei Bearbeitige gfunde wore, wu no vum Fäldhieter sotte aagluegt wäre.',
	'patrol-endorsed-ok' => 'Die Bearbeitig isch scho vum Fäldhieter aagluegt.',
	'patrol-endorsed-failed' => 'Die Bearbeitig cha nit as vum Fäldhieter gsäh markiert wäre.',
	'patrol-reverted-ok' => 'D Bearbeitig isch zrugggsetzt wore.',
	'patrol-reverted-failed' => 'D Bearbeitig cha nit zrugggsetzt wäre.',
	'patrol-skipped-ok' => 'Bearbeitig wird ignoriert.',
	'patrol-reasons' => '* Eifache Vandalismus
* Tescht vun eme neje Benutzer
* Lueg Diskussionssyte',
	'patrol-another' => 'Zeig no ne Bearbeitig, wänn s eini het.',
	'patrol-stopped' => 'Du hesch entschide, keini Bearbeitige meh as Fäldhieter aazluege. $1',
	'patrol-resume' => 'Druck doo zum Wytermache.',
	'group-patroller' => 'Fäldhieter',
	'group-patroller-member' => '{{GENDER:$1|Fäldhieter|Fäldhieteri}}',
	'grouppage-patroller' => '{{ns:project}}:Fäldhieter',
	'right-patroller' => 'Syte as Fäldhieter aaluege',
);

/** Gujarati (ગુજરાતી)
 * @author Sushant savla
 */
$messages['gu'] = array(
	'group-patroller' => 'પ્રહરીઓ',
	'group-patroller-member' => '{{GENDER:$1|પ્રહરી}}',
);

/** Manx (Gaelg)
 * @author MacTire02
 */
$messages['gv'] = array(
	'patrol-revert-reason' => 'Fa:',
);

/** Hausa (هَوُسَ) */
$messages['ha'] = array(
	'patrol-revert-reason' => 'Dalili:',
);

/** Hawaiian (Hawai`i)
 * @author Singularity
 */
$messages['haw'] = array(
	'patrol-revert-reason' => 'Kumu:',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author Rotemliss
 * @author YaronSh
 */
$messages['he'] = array(
	'patrol' => 'ניטור עריכות',
	'patrol-desc' => 'ממשק ניטור מורחב עם שיתוף עומס עבודה',
	'patrol-endorse' => 'לאשר',
	'patrol-revert' => 'לשחזר',
	'patrol-revert-reason' => 'סיבה:',
	'patrol-skip' => 'דילוג',
	'patrol-reverting' => 'שחזור: $1',
	'patrol-nonefound' => 'לא נמצאו עריכות שאפשר לנטר.',
	'patrol-endorsed-ok' => 'העריכה סומנה כמנוטרת.',
	'patrol-endorsed-failed' => 'לא ניתן לסמן את העריכה כמנוטרת.',
	'patrol-reverted-ok' => 'העריכה שוחזרה.',
	'patrol-reverted-failed' => 'לא ניתן לשחזר את העריכה.',
	'patrol-skipped-ok' => 'דילוג על עריכה.',
	'patrol-reasons' => '* השחתה פשוטה
* ניסיון של חדשים
* ראו בדף השיחה',
	'patrol-another' => 'הצגת עריכה נוספת, אם ניתן.',
	'patrol-stopped' => 'בחרתם לא לנטר עוד עריכה. $1',
	'patrol-resume' => 'לחצו כאן להמשך.',
	'group-patroller' => 'מנטרים',
	'group-patroller-member' => '{{GENDER:$1|מנטר|מנטרת}}',
	'grouppage-patroller' => '{{ns:project}}:מנטר',
	'right-patroller' => 'לנטר דפים',
);

/** Hindi (हिन्दी)
 * @author Kaustubh
 * @author Mayur
 */
$messages['hi'] = array(
	'patrol' => 'बदलावोंपर नजर रखें',
	'patrol-desc' => 'कार्य का लोड शेअर करने में मदत करने वाला एनहान्सड पेट्रोलिंग इंटरफेस',
	'patrol-endorse' => 'समर्थन दें',
	'patrol-revert' => 'पूर्ववत करें',
	'patrol-revert-reason' => 'कारण:',
	'patrol-skip' => 'नजर‍अंदाज करें',
	'patrol-reverting' => 'पलट रहें हैं: $1',
	'patrol-nonefound' => 'पहरा देने लायक बदलाव मिलें नहीं।',
	'patrol-endorsed-ok' => 'इस बदलाव पर पहरा दे दिया।',
	'patrol-endorsed-failed' => 'इस बदलाव पर पहरा नहीं दे पायें।',
	'patrol-reverted-ok' => 'यह बदलाव पलट दिया।',
	'patrol-reverted-failed' => 'यह बदलाव पलट नहीं पायें।',
	'patrol-skipped-ok' => 'बदलाव को नजर अंदाज कर रहें हैं।',
	'patrol-reasons' => '* सर्वसाधारण उत्पात
* नये सदस्योंके अस्थायी बदलाव
* वार्ता पृष्ठ देखें',
	'patrol-another' => 'अगर उपलब्ध हैं तो अन्य बदलाव दर्शायें।',
	'patrol-stopped' => 'आपने दुसरे बदलावोंपर पहरा रखने से मना किया हैं। $1',
	'patrol-resume' => 'फिरसे शुरू करने के लिये यहां क्लिक करें।',
	'group-patroller' => 'निरीक्षक',
	'group-patroller-member' => 'निरीक्षक',
	'grouppage-patroller' => '{{ns:project}}:निरीक्षक',
	'right-patroller' => 'पृष्ठ निरीक्षण करें',
);

/** Hiligaynon (Ilonggo)
 * @author Jose77
 */
$messages['hil'] = array(
	'patrol-revert-reason' => 'Rason:',
);

/** Croatian (Hrvatski)
 * @author Ex13
 */
$messages['hr'] = array(
	'patrol' => 'Patrolirane promjene',
	'patrol-revert' => 'Ukloni',
	'patrol-revert-reason' => 'Razlog:',
	'patrol-stopped' => 'Vi ste odabrali ne provjeravati drugo uređivanje. $1',
	'patrol-resume' => 'Kliknite ovdje za nastavak.',
	'group-patroller' => 'Patroleri',
	'group-patroller-member' => 'patroler',
	'grouppage-patroller' => '{{ns:project}}:Patroler',
	'right-patroller' => 'Patroliranje stranica',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'patrol' => 'Dohladowanje změnow',
	'patrol-desc' => 'Rozšěrjeny kontrolowy interfejs za dźělenje dźěłowych ćežow',
	'patrol-endorse' => 'Přihłosować',
	'patrol-revert' => 'Cofnyć',
	'patrol-revert-reason' => 'Přičina:',
	'patrol-skip' => 'Přeskočić',
	'patrol-reverting' => 'Cofnjenje: $1',
	'patrol-nonefound' => 'Za dohladowanje njebuchu přihódne změny namakane.',
	'patrol-endorsed-ok' => 'Tuta změna bu jako dohladowana markěrowana.',
	'patrol-endorsed-failed' => 'Tuta změna njemóže so jako dohladowana markěrować.',
	'patrol-reverted-ok' => 'Změna bu cofnjena.',
	'patrol-reverted-failed' => 'Změna njeda so cofnyć',
	'patrol-skipped-ok' => 'Změna so ignoruje.',
	'patrol-reasons' => '* Jednory wandalizm
* Test nowačka
* Hlej diskusijna strona',
	'patrol-another' => 'Dalšu změnu pokazać, jeli k dispoziciji stejaca.',
	'patrol-stopped' => 'Sy rozsudźił, zo njeby žane dalše změny dohladował. $1',
	'patrol-resume' => 'Klikń sem, zo by pokročował.',
	'group-patroller' => 'Dohladowarjo',
	'group-patroller-member' => '{{GENDER:$1|dohladowar|dohladowarka}}',
	'grouppage-patroller' => '{{ns:project}}:Dohladowar',
	'right-patroller' => 'Strony dohladować',
);

/** Hungarian (Magyar)
 * @author Dani
 * @author Dj
 * @author Glanthor Reviol
 */
$messages['hu'] = array(
	'patrol' => 'Szerkesztések járőrözése',
	'patrol-desc' => 'Továbbfejlesztett járőri felület munkamegosztással',
	'patrol-endorse' => 'Jóváhagyás',
	'patrol-revert' => 'Visszaállítás',
	'patrol-revert-reason' => 'Ok:',
	'patrol-skip' => 'Kihagyás',
	'patrol-reverting' => 'Visszaállítás: $1',
	'patrol-nonefound' => 'Nincs járőrzésre váró szerkesztés.',
	'patrol-endorsed-ok' => 'A szerkesztést ellenőrzöttnek jelöltem.',
	'patrol-endorsed-failed' => 'A szerkesztést nem sikerült ellenőrzöttnek jelölnöm.',
	'patrol-reverted-ok' => 'A szerkesztést visszaállítottam.',
	'patrol-reverted-failed' => 'A szerkesztést nem sikerült visszaállítanom.',
	'patrol-skipped-ok' => 'Szerkesztés kihagyása.',
	'patrol-reasons' => '* Egyszerű vandalizmus
* Újonc tesztje
* Lásd a vitalapot',
	'patrol-another' => 'Mutass más szerkesztést, ha lehetséges.',
	'patrol-stopped' => 'Nem jelölted be, hogy szeretnél további szerkesztést járőrözni. $1',
	'patrol-resume' => 'Kattints ide a folytatáshoz.',
	'group-patroller' => 'járőrök',
	'group-patroller-member' => 'járőr',
	'grouppage-patroller' => '{{ns:project}}:FV-járőr',
	'right-patroller' => 'lapok járőrözése',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'patrol' => 'Patruliar modificationes',
	'patrol-desc' => 'Interfacie de patruliage meliorate con division del carga de labor',
	'patrol-endorse' => 'Approbar',
	'patrol-revert' => 'Reverter',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Saltar',
	'patrol-reverting' => 'Reversion de: $1',
	'patrol-nonefound' => 'Nulle modificationes appropriate esseva trovate pro esser patruliate.',
	'patrol-endorsed-ok' => 'Le modification esseva marcate como patruliate.',
	'patrol-endorsed-failed' => 'Le modification non poteva esser marcate como patruliate.',
	'patrol-reverted-ok' => 'Le modification ha essite revertite.',
	'patrol-reverted-failed' => 'Le modification non poteva esser revertite.',
	'patrol-skipped-ok' => 'Le modification es ignorate.',
	'patrol-reasons' => '* Vandalismo simple
* Test de un novicio
* Vide pagina de discussion',
	'patrol-another' => 'Monstrar un altere modification, si possibile.',
	'patrol-stopped' => 'Tu ha optate pro non patruliar on altere modification. $1',
	'patrol-resume' => 'Clicca hic pro reprender.',
	'group-patroller' => 'Patruliatores',
	'group-patroller-member' => '{{GENDER:$1|patruliator|patruliatrice}}',
	'grouppage-patroller' => '{{ns:project}}:Patruliator',
	'right-patroller' => 'Paginas de patrulia',
);

/** Indonesian (Bahasa Indonesia)
 * @author Ivan Lanin
 * @author Kenrick95
 * @author Rex
 */
$messages['id'] = array(
	'patrol' => 'Patroli suntingan',
	'patrol-desc' => 'Antarmuka patroli yang ditingkatkan dengan pembagian beban kerja',
	'patrol-endorse' => 'Setujui',
	'patrol-revert' => 'Kembalikan',
	'patrol-revert-reason' => 'Alasan:',
	'patrol-skip' => 'Lewati',
	'patrol-reverting' => 'Mengembalikan: $1',
	'patrol-nonefound' => 'Tidak ada suntingan yang dapat dipatroli.',
	'patrol-endorsed-ok' => 'Suntingan ini ditandai telah dipatroli.',
	'patrol-endorsed-failed' => 'Suntingan ini tidak dapat ditandai telah dipatroli.',
	'patrol-reverted-ok' => 'Suntingan dikembalikan.',
	'patrol-reverted-failed' => 'Suntingan tidak dapat dikembalikan.',
	'patrol-skipped-ok' => 'Suntingan diabaikan.',
	'patrol-reasons' => '* Vandalisme sederhana
* Pengujian pemula
* Lihat halaman pembicaraan',
	'patrol-another' => 'Tampilkan suntingan lain jika tersedia.',
	'patrol-stopped' => 'Anda telah memilih untuk tidak mengontrol suntingan lain. $1',
	'patrol-resume' => 'Klik di sini untuk melanjutkan.',
	'group-patroller' => 'Pematroli',
	'group-patroller-member' => '{{GENDER:$1|Pematroli}}',
	'grouppage-patroller' => '{{ns:project}}:Pematroli',
	'right-patroller' => 'Melakukan patroli atas halaman-halaman',
);

/** Igbo (Igbo)
 * @author Ukabia
 */
$messages['ig'] = array(
	'patrol-revert-reason' => 'Mgbághapụtà:',
	'patrol-skip' => 'Gáfè',
	'group-patroller-member' => 'onye na léfuru ihe anyà',
);

/** Iloko (Ilokano)
 * @author Lam-ang
 */
$messages['ilo'] = array(
	'group-patroller' => 'Dagiti agpatpattrulia',
	'group-patroller-member' => '{{GENDER:$1|agpatpatrulia}}',
);

/** Icelandic (Íslenska)
 * @author Jóna Þórunn
 */
$messages['is'] = array(
	'patrol-revert' => 'Taka aftur',
	'patrol-revert-reason' => 'Ástæða:',
	'patrol-skip' => 'Sleppa',
	'patrol-reverting' => 'Tek aftur: $1',
	'patrol-skipped-ok' => 'Hunsa breytingu.',
	'patrol-reasons' => '* Smávægilegt skemmdarverk
* Tilraun nýliða
* Sjá spjallsíðu',
	'patrol-resume' => 'Smelltu hér til að halda áfram.',
);

/** Italian (Italiano)
 * @author BrokenArrow
 * @author Nemo bis
 * @author Pietrodn
 */
$messages['it'] = array(
	'patrol' => 'Verifica delle modifiche',
	'patrol-desc' => 'Interfaccia di patrolling migliorata con suddivisione del carico di lavoro',
	'patrol-endorse' => 'Approva',
	'patrol-revert' => 'Ripristina',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Salta',
	'patrol-reverting' => 'Ripristino: $1',
	'patrol-nonefound' => 'Non vi sono modifiche da verificare.',
	'patrol-endorsed-ok' => 'La modifica è stata segnata come verificata.',
	'patrol-endorsed-failed' => 'Impossibile segnare la modifica come verificata.',
	'patrol-reverted-ok' => 'La modifica è stata annullata.',
	'patrol-reverted-failed' => 'Impossibile annullare la modifica.',
	'patrol-skipped-ok' => 'Modifica ignorata.',
	'patrol-reasons' => '* Vandalismo semplice
* Prova di nuovo utente
* Vedi pagina di discussione',
	'patrol-another' => "Mostra un'altra modifica, se disponibile.",
	'patrol-stopped' => 'Si è scelto di non verificare altre modifiche. $1',
	'patrol-resume' => 'Fare clic qui per riprendere.',
	'group-patroller' => 'Patrollatori',
	'group-patroller-member' => '{{GENDER:$1|patrollatore|patrollatrice|patrollatore/trice}}',
	'grouppage-patroller' => '{{ns:project}}:Patrollatore',
	'right-patroller' => 'Verifica le pagine',
);

/** Japanese (日本語)
 * @author Fryed-peach
 * @author Hosiryuhosi
 * @author Whym
 * @author 青子守歌
 */
$messages['ja'] = array(
	'patrol' => '編集をパトロール',
	'patrol-desc' => 'パトロール負荷を共有する機能をもつ、パトロール機能の拡張インタフェース',
	'patrol-endorse' => '承認',
	'patrol-revert' => '差し戻し',
	'patrol-revert-reason' => '理由：',
	'patrol-skip' => 'スキップ',
	'patrol-reverting' => '差し戻し中: $1',
	'patrol-nonefound' => 'パトロールすべき編集は見つかりませんでした。',
	'patrol-endorsed-ok' => '編集をパトロール済みにしました。',
	'patrol-endorsed-failed' => '編集をパトロール済みにできませんでした。',
	'patrol-reverted-ok' => '編集を差し戻しました。',
	'patrol-reverted-failed' => '編集を差し戻せませんでした。',
	'patrol-skipped-ok' => '編集を無視しています。',
	'patrol-reasons' => '* 単なる荒らし
* 新規参加者によるテスト
* ノートを参照',
	'patrol-another' => 'あれば別の編集を表示する。',
	'patrol-stopped' => 'あなたは別の編集をパトロールしないよう設定しています。$1',
	'patrol-resume' => '再開するにはここをクリックしてください。',
	'group-patroller' => '巡回者',
	'group-patroller-member' => '{{GENDER:$1|巡回者}}',
	'grouppage-patroller' => '{{ns:project}}:パトローラー',
	'right-patroller' => 'ページをパトロールする',
);

/** Javanese (Basa Jawa)
 * @author Meursault2004
 */
$messages['jv'] = array(
	'patrol-revert' => 'Balèkna',
	'patrol-revert-reason' => 'Alesan:',
	'patrol-reverting' => 'Mbalèkaké: $1',
	'patrol-reverted-ok' => 'Suntingané dibalèkaké.',
	'patrol-skipped-ok' => 'Nglirwakaké suntingan.',
	'patrol-reasons' => '* Vandalisme biasa
* Tès pamula
* Delengen kaca dhiskusi',
);

/** Georgian (ქართული)
 * @author Alsandro
 * @author BRUTE
 * @author Malafaya
 */
$messages['ka'] = array(
	'patrol-revert-reason' => 'მიზეზი:',
	'group-patroller' => 'პატრულები',
	'group-patroller-member' => 'პატროლიორი',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Chhorran
 * @author Lovekhmer
 * @author Thearith
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'patrol-endorse' => 'យល់ស្រប',
	'patrol-revert' => 'ត្រឡប់',
	'patrol-revert-reason' => 'មូលហេតុ៖',
	'patrol-skip' => 'រំលង',
	'patrol-reverting' => 'កំពុងត្រឡប់: $1',
	'patrol-reverted-ok' => 'កំណែប្រែត្រូវត្រឡប់រួច។',
	'patrol-reverted-failed' => 'កំណែប្រែមិនអាចត្រឡប់បានទេ។',
	'patrol-resume' => 'ចុច​ទីនេះ ដើម្បី​បន្ត​។',
	'group-patroller' => 'អ្នកតាមដាន',
	'group-patroller-member' => '{{GENDER:$1|អ្នកល្បាត}}',
	'grouppage-patroller' => '{{ns:project}}:អ្នកតាមដាន',
	'right-patroller' => 'ទំព័រតាមដាន',
);

/** Kannada (ಕನ್ನಡ)
 * @author Nayvik
 */
$messages['kn'] = array(
	'patrol-revert' => 'ಹಿಂದಿನಂತಾಗಿಸು',
	'patrol-revert-reason' => 'ಕಾರಣ:',
);

/** Korean (한국어)
 * @author Devunt
 * @author Kwj2772
 */
$messages['ko'] = array(
	'group-patroller' => '검토자',
	'group-patroller-member' => '{{GENDER:$1|검토자}}',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'patrol' => 'Patrulleere',
	'patrol-desc' => 'E besser Ingerface för et Änderunge Nohlore (Patrullje) met Verdeile fun dä Ärbeit.',
	'patrol-endorse' => 'Jootheiße',
	'patrol-revert' => 'Zeröck nemme',
	'patrol-revert-reason' => 'Aanlass:',
	'patrol-skip' => 'Övverjonn',
	'patrol-reverting' => 'Zeröck nemme: $1',
	'patrol-nonefound' => 'Kein zopaß Änderunge för et Nohloore jefonge.',
	'patrol-endorsed-ok' => 'Di Änderung eß ald nohjeloohrt jewääse.',
	'patrol-endorsed-failed' => 'Mer kunnte di Änderung nit als „nohjeloort“ makkeere.',
	'patrol-reverted-ok' => 'De Änderung eß jätz widder retuur jemaat.',
	'patrol-reverted-failed' => 'Di Änderung kunnte mer nit retuur nämme.',
	'patrol-skipped-ok' => 'De Änderung weed övverjange.',
	'patrol-reasons' => '* Bloß ene Kappottmaachwöödijje
* Ene Neue moot sesch jet ußprobeere
* Loor noh, op dä Klaafsigg',
	'patrol-another' => 'Zeisch de nääxte Änderung, wann ein do es.',
	'patrol-stopped' => 'Do häß Desch entschiede, dat De jetz kei Änderunge mieh nohloore wells. $1',
	'patrol-resume' => 'Wigger maache!',
	'group-patroller' => 'Patrullje',
	'group-patroller-member' => '{{GENDER:$1|Patrullör|Patrullören|Patrullör|Patrullören|Patrullör}}',
	'grouppage-patroller' => '{{ns:project}}:Patrullje',
	'right-patroller' => 'Sigge Nohloore (Patrullje)',
);

/** Latin (Latina)
 * @author SPQRobin
 */
$messages['la'] = array(
	'patrol-revert-reason' => 'Causa:',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'patrol' => 'Kontroll vun den Ännerungen',
	'patrol-desc' => "Erweiderten Interface fir d'Kontrolléiere vu Säiten an Zesummenaarbecht mat Aneren",
	'patrol-endorse' => 'Bestätegen',
	'patrol-revert' => 'Zrécksetzen',
	'patrol-revert-reason' => 'Grond:',
	'patrol-skip' => 'Iwwersprangen',
	'patrol-reverting' => 'Zrécksetzen: $1',
	'patrol-nonefound' => "Et goufe keng passend Ännerunge fir z'iwerkucke fonnt.",
	'patrol-endorsed-ok' => 'Dës Ännerung gouf als nogekuckt markéiert.',
	'patrol-endorsed-failed' => "D'Ännerung konnt net als kontolléiert markéiert ginn.",
	'patrol-reverted-ok' => "D'Ännerung gouf zréckgesat.",
	'patrol-reverted-failed' => "D'Ännerung konnt net zréck gesat ginn.",
	'patrol-skipped-ok' => 'Ännerung ignoréieren',
	'patrol-reasons' => "* Einfache Vandalismus
* Test vun engem neie Benotzer
* Kuckt d'Diskussiounssäit",
	'patrol-another' => 'Weis eng aner Ännerung, wann se disponibel ass',
	'patrol-stopped' => 'Dir hutt decidéiert keng weider Ännerung ze kontrolléieren. $1',
	'patrol-resume' => 'Hei clicke fir weiderzefueren.',
	'group-patroller' => 'Kontrolleren',
	'group-patroller-member' => '{{GENDER:$1|Kontroller|Kontrollesch}}',
	'grouppage-patroller' => '{{ns:project}}:Kontroller',
	'right-patroller' => 'Sàiten iwwerkucken',
);

/** Limburgish (Limburgs)
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
	'group-patroller' => 'Controleurs',
	'group-patroller-member' => 'Controleur',
);

/** Lumbaart (Lumbaart)
 * @author Dakrismeno
 */
$messages['lmo'] = array(
	'patrol-resume' => 'Fà click chì-inscì-chì per tacà ancamò.',
	'group-patroller' => 'Gent de rónda',
);

/** Lithuanian (Lietuvių)
 * @author Hugo.arg
 */
$messages['lt'] = array(
	'group-patroller' => 'Patruliai',
);

/** Malagasy (Malagasy)
 * @author Jagwar
 */
$messages['mg'] = array(
	'group-patroller' => 'Mpitsidika',
	'group-patroller-member' => 'Mpitsidika',
);

/** Eastern Mari (Олык Марий)
 * @author Сай
 */
$messages['mhr'] = array(
	'patrol-revert-reason' => 'Амал:',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 * @author Brest
 */
$messages['mk'] = array(
	'patrol' => 'Патролирај уредувања',
	'patrol-desc' => 'Подобрен интеррфејс за патролирање со распределба на работниот обем',
	'patrol-endorse' => 'Потврди',
	'patrol-revert' => 'Врати',
	'patrol-revert-reason' => 'Причина:',
	'patrol-skip' => 'Прескокни',
	'patrol-reverting' => 'Враќање: $1',
	'patrol-nonefound' => 'Нема пронајдено соодветни уредувања за патролирање.',
	'patrol-endorsed-ok' => 'Уредувањето е обележано како проверено.',
	'patrol-endorsed-failed' => 'Уредувањето не можеше да се одбележи како проверено.',
	'patrol-reverted-ok' => 'Уредувањето е вратено.',
	'patrol-reverted-failed' => 'Уредувањето не можеше да се врати.',
	'patrol-skipped-ok' => 'Игнорирање на уредувањето.',
	'patrol-reasons' => '* Прост вандализам
* Тест на почетник
* Види страница за разговор',
	'patrol-another' => 'Прикажи друго уредување, ако го има.',
	'patrol-stopped' => 'Одлучивте да не патролирате друго уредување. $1',
	'patrol-resume' => 'Кликнете тука за да продолжите',
	'group-patroller' => 'Патролери',
	'group-patroller-member' => 'патролер',
	'grouppage-patroller' => '{{ns:project}}:Патролер',
	'right-patroller' => 'Патролирани страници',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Shijualex
 */
$messages['ml'] = array(
	'patrol' => 'തിരുത്തലുകളിൽ റോന്തു ചുറ്റുക',
	'patrol-desc' => 'ജോലിഭാര പങ്ക് വെയ്ക്കൽ ഉള്ള മെച്ചപ്പെട്ട റോന്ത് ചുറ്റൽ സമ്പർക്കമുഖം',
	'patrol-endorse' => 'അംഗീകാരം നൽകുക',
	'patrol-revert' => 'പൂർവ്വസ്ഥിതിയിലാക്കുക',
	'patrol-revert-reason' => 'കാരണം:',
	'patrol-skip' => 'മറികടക്കുക',
	'patrol-reverting' => 'പൂർവ്വസ്ഥിതിയിലാക്കുന്നു: $1',
	'patrol-nonefound' => 'റോന്തു ചുറ്റുന്നതിനു അനുയോജ്യമായ തിരുത്തലുകൾ കണ്ടില്ല.',
	'patrol-endorsed-ok' => 'ഈ തിരുത്തൽ റോന്തു ചുറ്റപ്പെട്ടതായി രേഖപ്പെടുത്തി.',
	'patrol-endorsed-failed' => 'ഈ തിരുത്തൽ റോന്തു ചുറ്റപ്പെട്ടതായി രേഖപ്പെടുത്തുന്നതിനു സാദ്ധ്യമല്ല.',
	'patrol-reverted-ok' => 'ഈ തിരുത്തൽ തിരസ്ക്കരിച്ചിരിക്കുന്നു.',
	'patrol-reverted-failed' => 'ഈ തിരുത്തൽ തിരസ്ക്കരിക്കുവാൻ സാദ്ധ്യമല്ല.',
	'patrol-skipped-ok' => 'തിരുത്തൽ അവഗണിക്കുന്നു.',
	'patrol-reasons' => '* ലഘുവായ നശീകരണപ്രവർത്തനം
* പുതുമുഖത്തിന്റെ പരീക്ഷണം
* സം‌വാദം താൾ കാണുക',
	'patrol-another' => '* ലഭ്യമാണെങ്കിൽ മറ്റൊരു തിരുത്തൽ കാണിക്കുക.',
	'patrol-stopped' => 'മറ്റൊരു തിരുത്തലിൽ റോന്തു ചുറ്റേണ്ടതില്ല എന്നു താങ്കൾ തിരഞ്ഞെടുത്തു.   $1',
	'patrol-resume' => 'തുടരുന്നതിനായി ഇവിടെ ഞെക്കുക.',
	'group-patroller' => 'റോന്തു ചുറ്റുന്നവർ',
	'group-patroller-member' => '{{GENDER:$1|റോന്തു ചുറ്റുന്നവർ}}',
	'grouppage-patroller' => '{{ns:project}}:റോന്തു ചുറ്റുന്നവർ',
	'right-patroller' => 'താളുകളിൽ റോന്തുചുറ്റുക',
);

/** Mongolian (Монгол)
 * @author Chinneeb
 */
$messages['mn'] = array(
	'patrol-revert' => 'Хуучин төлөвт шилжүүлэх',
	'patrol-revert-reason' => 'Шалтгаан:',
	'patrol-reverting' => 'Хуучин төлөвт шилжүүлж байна: $1',
	'patrol-reverted-ok' => 'Засварыг хуучин төлөвт шилжүүллээ.',
	'patrol-reverted-failed' => 'Засварыг хуучин төлөвт шилжүүлэх боломжгүй байв',
);

/** Marathi (मराठी)
 * @author Htt
 * @author Kaustubh
 * @author Mahitgar
 * @author Rahuldeshmukh101
 */
$messages['mr'] = array(
	'patrol' => 'संपादनांवर पहारा ठेवा',
	'patrol-desc' => 'कामाचा भार शेअर करून करता येण्याजोगा वाढीव संपादनांवरील पहारा',
	'patrol-endorse' => 'समर्थन द्या',
	'patrol-revert' => 'पूर्वपदास न्या',
	'patrol-revert-reason' => 'कारण:',
	'patrol-skip' => 'दुर्लक्ष करा',
	'patrol-reverting' => 'पूर्वपदास नेत आहे: $1',
	'patrol-nonefound' => 'पहारा देण्यास योग्य अशी संपादने सापडली नाहीत.',
	'patrol-endorsed-ok' => 'हे संपादन तपासले म्हणून खूण केली.',
	'patrol-endorsed-failed' => 'ह्या संपादनावर तपासले अशी खूण करता आली नाही.',
	'patrol-reverted-ok' => 'हे संपादन उलटविले.',
	'patrol-reverted-failed' => 'संपादन उलटविता आले नाही.',
	'patrol-skipped-ok' => 'संपादनाकडे दुर्लक्ष करीत आहे.',
	'patrol-reasons' => '* सर्वसाधारण उत्पात
* नवीन सदस्याचे तात्पुरते संपादन
* चर्चा पान पहा',
	'patrol-another' => 'जर उपलब्ध असतील तर इतर संपादने दाखवा.',
	'patrol-stopped' => 'तुम्ही इतर संपादनांवर पहारा देण्याचे नाकारले आहे. $1',
	'patrol-resume' => 'पुन्हा चालू करण्यासाठी इथे टिचकी द्या.',
	'group-patroller' => 'निरीक्षक',
	'group-patroller-member' => '{{GENDER:$1|निरीक्षक}}',
	'grouppage-patroller' => '{{ns:project}}:निरीक्षक',
	'right-patroller' => 'पानांवर पहारा करा',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 * @author Aviator
 * @author Kurniasan
 */
$messages['ms'] = array(
	'patrol' => 'Ronda suntingan',
	'patrol-desc' => 'Meningkatkan antara muka rondaan dengan perkongsian beban kerja',
	'patrol-endorse' => 'Endors',
	'patrol-revert' => 'Balik',
	'patrol-revert-reason' => 'Sebab:',
	'patrol-skip' => 'Langkau',
	'patrol-reverting' => 'Membalikkan: $1',
	'patrol-nonefound' => 'Tiada suntingan yang bersesuaian untuk dirondai.',
	'patrol-endorsed-ok' => 'Suntingan ini telah ditanda ronda.',
	'patrol-endorsed-failed' => 'Suntingan tersebut tidak dapat ditandakan telah dironda.',
	'patrol-reverted-ok' => 'Suntingan telah dikembalikan.',
	'patrol-reverted-failed' => 'Suntingan ini tidak dapat dikembalikan.',
	'patrol-skipped-ok' => 'Mengabaikan suntingan.',
	'patrol-reasons' => '* Vandalisme ringan
* Ujian orang baru
* Lihat laman perbualan',
	'patrol-another' => 'Tunjukkan suntingan seterusnya, jika ada.',
	'patrol-stopped' => 'Anda telah memilih untuk tidak meronda suntingan lain. $1',
	'patrol-resume' => 'Klik di sini untuk sambung semula.',
	'group-patroller' => 'Peronda',
	'group-patroller-member' => 'peronda',
	'grouppage-patroller' => '{{ns:project}}:Peronda',
	'right-patroller' => 'Meronda laman',
);

/** Erzya (Эрзянь)
 * @author Botuzhaleny-sodamo
 */
$messages['myv'] = array(
	'patrol-revert-reason' => 'Тувталось:',
	'patrol-skip' => 'Ютык вакска',
);

/** Nahuatl (Nāhuatl)
 * @author Fluence
 * @author Teòtlalili
 */
$messages['nah'] = array(
	'patrol-revert' => 'Tlacuepāz',
	'patrol-revert-reason' => 'Īxtlamatiliztli:',
	'group-patroller' => 'Tlapîpiànimë',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['nb'] = array(
	'patrol' => 'Patruljer redigeringer',
	'patrol-desc' => 'Forbedret patruljeringsgrensesnitt med arbeidsfordeling',
	'patrol-endorse' => 'Godkjenn',
	'patrol-revert' => 'Tilbakestill',
	'patrol-revert-reason' => 'Årsak:',
	'patrol-skip' => 'Hopp over',
	'patrol-reverting' => 'Tilbakestiller: $1',
	'patrol-nonefound' => 'Ingen redigeringer passende for patruljering kunne finnes.',
	'patrol-endorsed-ok' => 'Redigeringen ble merket som patruljert.',
	'patrol-endorsed-failed' => 'Redigeringen kunne ikke merkes som patruljert.',
	'patrol-reverted-ok' => 'Redigeringen ble tilbakestilt.',
	'patrol-reverted-failed' => 'Redigeringen kunne ikke tilbakestilles.',
	'patrol-skipped-ok' => 'Ignorerer redigering.',
	'patrol-reasons' => '* Enkel vandalisme
* Nybegynnertest
* Se diskusjonsside',
	'patrol-another' => 'Vis en annen redigering, om tilgjenglig.',
	'patrol-stopped' => 'Du har valgt å ikke patruljere en annen redigering. $1',
	'patrol-resume' => 'Klikk her for å fortsette.',
	'group-patroller' => 'Patruljører',
	'group-patroller-member' => 'patruljør',
	'grouppage-patroller' => '{{ns:project}}:Patruljør',
	'right-patroller' => 'Patruljere sider',
);

/** Nedersaksisch (Nedersaksisch)
 * @author Servien
 */
$messages['nds-nl'] = array(
	'group-patroller' => 'kontroleurs',
	'group-patroller-member' => 'kontroleur',
);

/** Nepali (नेपाली)
 * @author सरोज कुमार ढकाल
 */
$messages['ne'] = array(
	'group-patroller' => 'गस्तीकर्ताहरु',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'patrol' => 'Wijzigingen controleren',
	'patrol-desc' => 'Verbeterde interface voor controle van wijzigingen met mogelijkheden om werklast te delen',
	'patrol-endorse' => 'Goedkeuren',
	'patrol-revert' => 'Terugdraaien',
	'patrol-revert-reason' => 'Reden:',
	'patrol-skip' => 'Overslaan',
	'patrol-reverting' => '$1 aan het terugdraaien',
	'patrol-nonefound' => 'Er zijn geen te controleren wijzigingen gevonden.',
	'patrol-endorsed-ok' => 'De bewerking is gemarkteerd als gecontroleerd.',
	'patrol-endorsed-failed' => 'De bewerking kon niet gemarkeerd worden als gecontroleerd.',
	'patrol-reverted-ok' => 'De bewerking is teruggedraaid.',
	'patrol-reverted-failed' => 'De bewerking kon niet teruggedraaid worden.',
	'patrol-skipped-ok' => 'Bewerking wordt genegeerd.',
	'patrol-reasons' => '* Eenvoudig vandalisme
* Zandbakken
* Zie overlegpagina',
	'patrol-another' => 'Volgende bewerking weergeven als beschikbaar.',
	'patrol-stopped' => 'U hebt gekozen geen andere bewerking te controleren. $1',
	'patrol-resume' => 'Klik hier om verder te gaan.',
	'group-patroller' => 'controleurs',
	'group-patroller-member' => '{{GENDER:$1|controleur}}',
	'grouppage-patroller' => '{{ns:project}}:Controleur',
	'right-patroller' => "Pagina's markeren",
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Harald Khan
 * @author Jon Harald Søby
 */
$messages['nn'] = array(
	'patrol' => 'Patruljer endringar',
	'patrol-desc' => 'Betra patruljeringsgrensesnitt med arbeidsfordeling.',
	'patrol-endorse' => 'Godkjenn',
	'patrol-revert' => 'Gjer om',
	'patrol-revert-reason' => 'Årsak:',
	'patrol-skip' => 'Hopp over',
	'patrol-reverting' => 'Gjer om: $1',
	'patrol-nonefound' => 'Fann ingen endringar som var passande for patruljering.',
	'patrol-endorsed-ok' => 'Endringa vart merkt som patruljert.',
	'patrol-endorsed-failed' => 'Endringa kunne ikkje verta merkt som patruljert.',
	'patrol-reverted-ok' => 'Endringa vart gjort om.',
	'patrol-reverted-failed' => 'Endringa kunne ikkje verta gjort om.',
	'patrol-skipped-ok' => 'Ignorerer endring.',
	'patrol-reasons' => '* Enkel vandalisme
* Nybyrjartest
* Sjå diskusjonssida',
	'patrol-another' => 'Sjå ei anna endring, om tilgjengeleg.',
	'patrol-stopped' => 'Du har valt å ikkje patruljera ei anna endring. $1',
	'patrol-resume' => 'Trykk her for å halda fram.',
	'group-patroller' => 'Endringsvaktarar',
	'group-patroller-member' => 'endringsvaktar',
	'grouppage-patroller' => '{{ns:project}}:Endringsvaktar',
	'right-patroller' => 'Patruljera sider',
);

/** Novial (Novial)
 * @author Malafaya
 */
$messages['nov'] = array(
	'patrol-revert-reason' => 'Resone:',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'patrol' => 'Verificacion de las modificacions',
	'patrol-desc' => 'Interfàcia metent en valor lo patrolhatge collaboratiu.',
	'patrol-endorse' => 'Acceptar',
	'patrol-revert' => 'Anullar',
	'patrol-revert-reason' => 'Motiu :',
	'patrol-skip' => 'Sautar',
	'patrol-reverting' => 'Anullacion: $1',
	'patrol-nonefound' => "Cap d'edicion seguida a pas pugut èsser trobada per la verificacion.",
	'patrol-endorsed-ok' => 'L’edicion es estada marcada coma verificada.',
	'patrol-endorsed-failed' => 'L’edicion a pas pogut èsser verificada.',
	'patrol-reverted-ok' => 'L’edicion es estada anullada.',
	'patrol-reverted-failed' => 'L’edicion a pas pogut èsser anullada.',
	'patrol-skipped-ok' => 'Ignorar l’edicion.',
	'patrol-reasons' => '* Simple vandalisme
* Tèst de debutant
* Vejatz pagina de discussion',
	'patrol-another' => 'Veire una edicion novèla, se es disponibla.',
	'patrol-stopped' => 'Avètz causit per verificar pas mai una autra edicion. $1',
	'patrol-resume' => 'Clicar aicí per reprener.',
	'group-patroller' => 'Patrolhaires',
	'group-patroller-member' => 'Patrolhaire',
	'grouppage-patroller' => '{{ns:project}}:Patrolhaire',
	'right-patroller' => 'Paginas de patrolha',
);

/** Oriya (ଓଡ଼ିଆ)
 * @author Psubhashish
 */
$messages['or'] = array(
	'group-patroller' => 'ପହରାଦାର',
	'group-patroller-member' => '{{GENDER:$1|ପହରାଦାର}}',
);

/** Ossetic (Ирон)
 * @author Amikeco
 */
$messages['os'] = array(
	'patrol-revert-reason' => 'Аххос:',
);

/** Deitsch (Deitsch)
 * @author Xqt
 */
$messages['pdc'] = array(
	'patrol-revert-reason' => 'Grund:',
);

/** Polish (Polski)
 * @author Derbeth
 * @author Leinad
 * @author Sp5uhe
 */
$messages['pl'] = array(
	'patrol' => 'Patrolowanie edycji',
	'patrol-desc' => 'Rozszerzony interfejs patrolowania ostatnich zmian',
	'patrol-endorse' => 'Zatwierdź',
	'patrol-revert' => 'Cofnij',
	'patrol-revert-reason' => 'Powód',
	'patrol-skip' => 'Pomiń',
	'patrol-reverting' => 'Wycofanie: $1',
	'patrol-nonefound' => 'Nie znaleziono edycji do patrolowania.',
	'patrol-endorsed-ok' => 'Edycja została oznaczona jako sprawdzona.',
	'patrol-endorsed-failed' => 'Nie udało się oznaczyć edycji jako sprawdzonej.',
	'patrol-reverted-ok' => 'Edycja została cofnięta.',
	'patrol-reverted-failed' => 'Nie udało się cofnąć edycji.',
	'patrol-skipped-ok' => 'Ignorowanie edycji.',
	'patrol-reasons' => '* Prosty wandalizm * Test * Zobacz stronę dyskusji',
	'patrol-another' => 'Pokaż inną edycję, jeśli istnieje.',
	'patrol-stopped' => 'Wybrano, by nie patrolować więcej edycji. $1',
	'patrol-resume' => 'Kliknij tu by kontynuować',
	'group-patroller' => 'Patrolujący',
	'group-patroller-member' => '{{GENDER:$1|patrolujący|patrolująca}}',
	'grouppage-patroller' => '{{ns:project}}:Patrolujący',
	'right-patroller' => 'Patrolowanie stron',
);

/** Piedmontese (Piemontèis)
 * @author Bèrto 'd Sèra
 * @author Dragonòt
 */
$messages['pms'] = array(
	'patrol' => 'Patojé le modìfiche',
	'patrol-desc' => 'Antërfacia ëd verìfica mijorà con condivision dël càrich ëd travaj',
	'patrol-endorse' => 'Aprové',
	'patrol-revert' => "Buté com a l'era",
	'patrol-revert-reason' => 'Rason:',
	'patrol-skip' => 'Sàota',
	'patrol-reverting' => 'Ën tirand andré: $1',
	'patrol-nonefound' => 'Gnun-a modìfica da patojé.',
	'patrol-endorsed-ok' => 'La modìfica a l\'é staita marcà "patojà".',
	'patrol-endorsed-failed' => 'La modìfica a l\'é nen podusse marché coma "patojà"',
	'patrol-reverted-ok' => "La modìfica a l'é staita tirà andré com a l'era.",
	'patrol-reverted-failed' => "Sta modìfica-sì as peul pa buté andré com a l'era.",
	'patrol-skipped-ok' => 'Lassa beuje la modìfica.',
	'patrol-reasons' => "* vandalism sempi
* preuva ëd n'utent neuv
* ch'a varda la pàgina ëd discussion",
	'patrol-another' => "Smon n'àotra modìfica, s'a-i n'a-i é un-a da smon-e.",
	'patrol-stopped' => "A l'ha sërnù dë nen patojé n'àotra modìfica. $1",
	'patrol-resume' => "Ch'a bata ambelessì për andé anans da andova a l'era.",
	'group-patroller' => 'Gent ëd patoja',
	'group-patroller-member' => '{{GENDER:$1|ëd patoja}}',
	'grouppage-patroller' => '{{ns:project}}:patoja',
	'right-patroller' => 'Verìrica le pàgine',
);

/** Western Punjabi (پنجابی)
 * @author Khalid Mahmood
 */
$messages['pnb'] = array(
	'group-patroller' => 'گشت کرن والے',
	'group-patroller-member' => 'گشت کرن والے',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'patrol-revert' => 'په څټ ګرځول',
	'patrol-revert-reason' => 'سبب:',
	'patrol-skip' => 'تېرېدل',
);

/** Portuguese (Português)
 * @author Giro720
 * @author Hamilton Abreu
 * @author Lijealso
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'patrol' => 'Edições de patrulha',
	'patrol-desc' => 'Interface de patrulhagem melhorada com partilha de carga de trabalho',
	'patrol-endorse' => 'Aprovar',
	'patrol-revert' => 'Reverter',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Saltar',
	'patrol-reverting' => 'Revertendo: $1',
	'patrol-nonefound' => 'Nenhuma edição apropriada foi encontrada para ser patrulhada.',
	'patrol-endorsed-ok' => 'A edição foi marcada como patrulhada.',
	'patrol-endorsed-failed' => 'Não foi possível marcar a edição como patrulhada.',
	'patrol-reverted-ok' => 'A edição foi revertida.',
	'patrol-reverted-failed' => 'Não foi possível reverter a edição.',
	'patrol-skipped-ok' => 'Ignorando edição.',
	'patrol-reasons' => '* Simples vandalismo
* Teste de principiante
* Ver página de discussão',
	'patrol-another' => 'Mostrar outra edição, se disponível.',
	'patrol-stopped' => 'Optou por não patrulhar uma outra edição. $1',
	'patrol-resume' => 'Carregue aqui para continuar.',
	'group-patroller' => 'Patrulhadores',
	'group-patroller-member' => '{{GENDER:$1|patrulhador|patrulhadora}}',
	'grouppage-patroller' => '{{ns:project}}:Patrulhador',
	'right-patroller' => 'Patrulhar páginas',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 * @author Rafael Vargas
 */
$messages['pt-br'] = array(
	'patrol' => 'Patrulha de edições',
	'patrol-desc' => 'Interface de patrulhamento melhorada com compartilhamento de carga de trabalho',
	'patrol-endorse' => 'Aprovar',
	'patrol-revert' => 'Reverter',
	'patrol-revert-reason' => 'Motivo:',
	'patrol-skip' => 'Saltar',
	'patrol-reverting' => 'Revertendo: $1',
	'patrol-nonefound' => 'Nenhuma edição apropriada foi encontrada para ser patrulhada.',
	'patrol-endorsed-ok' => 'A edição foi marcada como patrulhada.',
	'patrol-endorsed-failed' => 'A edição não pôde ser marcada como patrulhada.',
	'patrol-reverted-ok' => 'A edição foi revertida.',
	'patrol-reverted-failed' => 'A edição não pôde ser revertida.',
	'patrol-skipped-ok' => 'Ignorando edição.',
	'patrol-reasons' => '* Simples vandalismo
* Teste de principiante
* Ver página de discussão',
	'patrol-another' => 'Mostrar outra edição, se disponível.',
	'patrol-stopped' => 'Você optou por não patrulhar uma outra edição. $1',
	'patrol-resume' => 'Clique aqui para continuar.',
	'group-patroller' => 'Patrulhadores',
	'group-patroller-member' => '{{GENDER:$1|Patrulhador|Patrulhadora}}',
	'grouppage-patroller' => '{{ns:project}}:Patrulhador',
	'right-patroller' => 'Patrulhar páginas',
);

/** Quechua (Runa Simi)
 * @author AlimanRuna
 */
$messages['qu'] = array(
	'group-patroller' => 'Patrullaqkuna',
	'group-patroller-member' => '{{GENDER:$1|}}patrullaq',
);

/** Romansh (Rumantsch) */
$messages['rm'] = array(
	'patrol-revert-reason' => 'Motiv:',
);

/** Romanian (Română)
 * @author Firilacroco
 * @author KlaudiuMihaila
 * @author Minisarm
 */
$messages['ro'] = array(
	'patrol' => 'Patrulează modificări',
	'patrol-revert-reason' => 'Motiv:',
	'patrol-skip' => 'Sari peste',
	'patrol-another' => 'Arată altă modificare, dacă e posibil.',
	'patrol-resume' => 'Apăsați aici pentru a reveni.',
	'group-patroller' => 'Patrule',
	'group-patroller-member' => 'patrulă',
	'grouppage-patroller' => '{{ns:project}}:Patrule',
	'right-patroller' => 'Pagini de patrulat',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'group-patroller' => 'Condrollore',
	'group-patroller-member' => '{{GENDER:$1|condrollore}}',
);

/** Russian (Русский)
 * @author Ferrer
 * @author Illusion
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'patrol' => 'Патрулирование',
	'patrol-desc' => 'Усовершенствованный интерфейс патрулирования с распределением рабочей нагрузки',
	'patrol-endorse' => 'Подтвердить',
	'patrol-revert' => 'Откатить',
	'patrol-revert-reason' => 'Причина:',
	'patrol-skip' => 'Отложить',
	'patrol-reverting' => 'Откат: $1',
	'patrol-nonefound' => 'Невозможно найти соответствующих правок для патрулирования.',
	'patrol-endorsed-ok' => 'Правка подтверждена.',
	'patrol-endorsed-failed' => 'Не удалось подтвердить правку.',
	'patrol-reverted-ok' => 'Правка откачена.',
	'patrol-reverted-failed' => 'Не удалось откатить правку.',
	'patrol-skipped-ok' => 'Правка отложена.',
	'patrol-reasons' => '* Просто вандализм
* Тест новичка
* См. страницу обсуждения',
	'patrol-another' => 'Вывести ещё одну правку, если имеется.',
	'patrol-stopped' => 'Вы решили не патрулировать другую правку. $1',
	'patrol-resume' => 'Нажмите здесь для продолжения.',
	'group-patroller' => 'Патрулирующие',
	'group-patroller-member' => '{{GENDER:$1|патрулирующий|патрулирующая}}',
	'grouppage-patroller' => '{{ns:project}}:Патрулирование',
	'right-patroller' => 'Патрулирование страниц',
);

/** Rusyn (Русиньскый)
 * @author Gazeb
 */
$messages['rue'] = array(
	'patrol-skip' => 'Перескочіти',
	'group-patroller' => 'Патролы',
	'group-patroller-member' => 'патрола',
);

/** Sakha (Саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'group-patroller' => 'Ботурууллар',
	'group-patroller-member' => '{{GENDER:$1|ботуруул}}',
);

/** Serbo-Croatian (Srpskohrvatski)
 * @author OC Ripper
 */
$messages['sh'] = array(
	'patrol-revert' => 'Vrati',
);

/** Sinhala (සිංහල)
 * @author Calcey
 * @author Singhalawap
 * @author බිඟුවා
 */
$messages['si'] = array(
	'group-patroller' => 'මුර සංචාරකයන්',
	'group-patroller-member' => '{{GENDER:$1|මුර-සංචාරක}}',
);

/** Slovak (Slovenčina)
 * @author Helix84
 * @author Teslaton
 */
$messages['sk'] = array(
	'patrol' => 'Strážiť úpravy',
	'patrol-desc' => 'Rozšírené rozhranie na stráženie s rozkladaním pracovnej záťaže',
	'patrol-endorse' => 'Schváliť',
	'patrol-revert' => 'Vrátiť',
	'patrol-revert-reason' => 'Dôvod:',
	'patrol-skip' => 'Preskočiť',
	'patrol-reverting' => 'Vraciam: $1',
	'patrol-nonefound' => 'Neboli nájdené vhodné úpravy na stráženie.',
	'patrol-endorsed-ok' => 'Úprava bola označená ako strážená.',
	'patrol-endorsed-failed' => 'Úpravu nebolo možné označiť ako stráženú',
	'patrol-reverted-ok' => 'Úprava bola vrátená.',
	'patrol-reverted-failed' => 'Úpravu nebolo možné vrátiť.',
	'patrol-skipped-ok' => 'Ignorujem úpravu.',
	'patrol-reasons' => '* Jednoduchý vandalizmus
* Testy nováčika
* Pozri diskusiu',
	'patrol-another' => 'Zobraz ďalšiu úpravu, ak existuje.',
	'patrol-stopped' => 'Zvolili ste nestrážiť ďalšiu úpravu. $1',
	'patrol-resume' => 'Kliknutím sem pokračujte.',
	'group-patroller' => 'Strážcovia',
	'group-patroller-member' => '{{GENDER:$1|Strážca|Strážkyňa}}',
	'grouppage-patroller' => '{{ns:project}}:Strážca',
	'right-patroller' => 'Sledovať stránky',
);

/** Slovenian (Slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'patrol' => 'Nadziranje urejanj',
	'patrol-desc' => 'Izboljšan vmesnik za nadziranje z delitvijo delovne obremenitve',
	'patrol-endorse' => 'Vključi se',
	'patrol-revert' => 'Vrni',
	'patrol-revert-reason' => 'Razlog:',
	'patrol-skip' => 'Preskoči',
	'patrol-reverting' => 'Vračanje: $1',
	'patrol-nonefound' => 'Za nadziranje ni bilo najdeno nobeno primerno urejanje.',
	'patrol-endorsed-ok' => 'Urejanje je bilo označeno kot nadzorovano.',
	'patrol-endorsed-failed' => 'Urejanja ni bilo mogoče označiti kot nadzorovano.',
	'patrol-reverted-ok' => 'Urejanje je bilo vrnjeno.',
	'patrol-reverted-failed' => 'Urejanja ni bilo mogoče vrniti.',
	'patrol-skipped-ok' => 'Preziranje urejanja.',
	'patrol-reasons' => '* Preprost vandalizem
* Preizkus novinca
* Glej pogovorno stran',
	'patrol-another' => 'Prikaži drugo urejanje, če je na voljo.',
	'patrol-stopped' => 'Odločili ste se za prenehanje nadziranja urejanj. $1',
	'patrol-resume' => 'Kliknite tukaj za nadaljevanje.',
	'group-patroller' => 'Nadzorniki',
	'group-patroller-member' => 'nadzornik',
	'grouppage-patroller' => '{{ns:project}}:Nadzornik',
	'right-patroller' => 'Nadziranje strani',
);

/** Albanian (Shqip)
 * @author Mikullovci11
 * @author Olsi
 */
$messages['sq'] = array(
	'group-patroller' => 'Patrolluesit',
	'group-patroller-member' => 'inspektorues',
);

/** Serbian (Cyrillic script) (‪Српски (ћирилица)‬)
 * @author Rancher
 * @author Sasa Stefanovic
 * @author Михајло Анђелковић
 */
$messages['sr-ec'] = array(
	'patrol' => 'Патролирај измене',
	'patrol-revert' => 'Врати',
	'patrol-revert-reason' => 'Разлог:',
	'patrol-skip' => 'Прескочи',
	'patrol-reverting' => 'Враћање: $1',
	'patrol-nonefound' => 'Нема одговарајућих измена за патролирање.',
	'patrol-endorsed-ok' => 'Измена је означена као прегледана.',
	'patrol-endorsed-failed' => 'Не могу да означим измену као прегледану.',
	'patrol-reverted-ok' => 'Измена је враћена.',
	'patrol-reverted-failed' => 'Не могу да вратим измену.',
	'patrol-skipped-ok' => 'Занемарујем измену.',
	'patrol-reasons' => '* Прост вандализам
* Тест почетника
* Види страницу за разговор',
	'patrol-another' => 'Прикажи другу измену, ако постоји.',
	'patrol-resume' => 'Кликните овде за наставак.',
	'group-patroller' => 'Патролери',
	'group-patroller-member' => 'патролер',
	'grouppage-patroller' => '{{ns:project}}:Патролер',
	'right-patroller' => 'Патролирање страна',
);

/** Serbian (Latin script) (‪Srpski (latinica)‬)
 * @author Michaello
 */
$messages['sr-el'] = array(
	'patrol' => 'Potroliranje izmena',
	'patrol-revert' => 'Vrati',
	'patrol-revert-reason' => 'Razlog:',
	'patrol-skip' => 'Preskoči',
	'patrol-reverting' => 'Vraćanje: $1',
	'patrol-nonefound' => 'Nema izmena koje bi se mogle patrolirati',
	'patrol-endorsed-ok' => 'Izmena je obeležena kao patrolisana.',
	'patrol-endorsed-failed' => 'Izmena nije mogla biti obeležena kao patrolirana.',
	'patrol-reverted-ok' => 'Izmena je vraćena.',
	'patrol-reverted-failed' => 'Izmena nije mogla biti vraćena.',
	'patrol-skipped-ok' => 'Ignorisanje izmene.',
	'patrol-reasons' => '* Običan vandalizam
* Test početnika
* Vidi stranu za razgovor',
	'patrol-another' => 'Pokaži neku drugu izmenu, ako je dostupna.',
	'patrol-resume' => 'Kliknite ovde da se vratite.',
	'group-patroller' => 'Patroleri',
	'group-patroller-member' => 'patroler',
	'grouppage-patroller' => '{{ns:project}}:Patroler',
	'right-patroller' => 'Patroliranje strana',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'patrol' => 'Kontrolle fon do Beoarbaidengen',
	'patrol-desc' => 'Ärwiederde Snitsteede foar ju Kontrolle fon Beoarbaidengen',
	'patrol-endorse' => 'Bestäätigje',
	'patrol-revert' => 'Touräächsätte',
	'patrol-revert-reason' => 'Gruund:',
	'patrol-skip' => 'Uurspringe',
	'patrol-reverting' => 'Touräächsätte: $1',
	'patrol-nonefound' => 'Der wuuden neen paasjende Beoarbaidengen toun Kontrollierjen fuunen.',
	'patrol-endorsed-ok' => 'Disse Beoarbaidenge wuude al as kontrollierd markierd.',
	'patrol-endorsed-failed' => 'Disse Beoarbaidenge kon nit as kontrollierd markierd wäide.',
	'patrol-reverted-ok' => 'Ju Beoarbaidenge wuude touräächsät.',
	'patrol-reverted-failed' => 'Ju Beoarbaidenge kon nit touräächsät wäide.',
	'patrol-skipped-ok' => 'Beoarbaidenge wäd ignorierd.',
	'patrol-reasons' => '* Eenfacher Vandalismus
* Test fon n Näiling
* Sjuch Diskussionssiede',
	'patrol-another' => 'Wies ne wiedere Beoarbaidenge, sofier ferföichboar.',
	'patrol-stopped' => 'Jie hääbe äntskeeden, neen wiedere Beoarbaidenge tou kontrollierjen. $1',
	'patrol-resume' => 'Klik hier toun Foutsätten.',
	'group-patroller' => 'Kontrolleure',
	'group-patroller-member' => 'Kontrolleur',
	'grouppage-patroller' => '{{ns:project}}:Kontrolleur',
	'right-patroller' => 'Sieden kontrollierje',
);

/** Sundanese (Basa Sunda)
 * @author Kandar
 */
$messages['su'] = array(
	'patrol' => 'Ronda éditan',
	'patrol-endorse' => 'Widian',
	'patrol-revert' => 'Balikkeun',
	'patrol-revert-reason' => 'Alesan:',
	'patrol-skip' => 'Liwat',
	'patrol-reverting' => 'Malikkeun: $1',
	'patrol-nonefound' => 'Euweuh éditan nu perlu dironda.',
	'patrol-endorsed-ok' => 'Éditan geus ditandaan geus dironda.',
	'patrol-endorsed-failed' => 'Éditan teu bisa dicirian geus dironda.',
	'patrol-reverted-ok' => 'Éditan geus dibalikkeun.',
	'patrol-reverted-failed' => 'Éditanana teu bisa dibalikkeun.',
	'patrol-skipped-ok' => 'Ngantepkeun éditan.',
	'patrol-reasons' => '* Vandalismeu basajan
* Uji coba nu anyar datang
* Tempo kaca obrolan',
	'patrol-another' => 'Mun bisa, témbongkeun éditan lianna.',
	'patrol-stopped' => 'Anjeun geus milih teu ngaronda éditan séjén. $1',
	'patrol-resume' => 'Klik di dieu pikeun neruskeun.',
	'group-patroller' => 'Nu ngaronda',
	'group-patroller-member' => 'Nu ngaronda',
	'grouppage-patroller' => '{{ns:project}}:Nu ngaronda',
);

/** Swedish (Svenska)
 * @author Diupwijk
 * @author Lejonel
 * @author Lokal Profil
 * @author M.M.S.
 */
$messages['sv'] = array(
	'patrol' => 'Patrullera redigeringar',
	'patrol-desc' => 'Förbereder patrulleringsgränssnitt med arbetsfördelning',
	'patrol-endorse' => 'Godkänn',
	'patrol-revert' => 'Återställ',
	'patrol-revert-reason' => 'Anledning:',
	'patrol-skip' => 'Hoppa över',
	'patrol-reverting' => 'Återställer: $1',
	'patrol-nonefound' => 'Inga redigeringar som passar för patrullering hittades.',
	'patrol-endorsed-ok' => 'Redigeringen blev markerad som patrullerad.',
	'patrol-endorsed-failed' => 'Redigeringen kunde inte markeras som patrullerad.',
	'patrol-reverted-ok' => 'Redigeringen återställdes.',
	'patrol-reverted-failed' => 'Redigeringen kunde inte återställas.',
	'patrol-skipped-ok' => 'Ignorera redigering.',
	'patrol-reasons' => '* Enkel vandalism
* Nybörjartest
* Se diskussionssida',
	'patrol-another' => 'Visa andra redigeringar, om det är tillgängligt.',
	'patrol-stopped' => 'Du har valt och inte patrullera en annan redigering. $1',
	'patrol-resume' => 'Klicka här för att fortsätta.',
	'group-patroller' => 'Patrullerare',
	'group-patroller-member' => '{{GENDER:$1|patrullerare}}',
	'grouppage-patroller' => '{{ns:project}}:Patrullerare',
	'right-patroller' => 'Patrullera sidor',
);

/** Silesian (Ślůnski)
 * @author Herr Kriss
 */
$messages['szl'] = array(
	'patrol-revert-reason' => 'Čymu:',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'patrol-revert-reason' => 'కారణం:',
	'patrol-skip' => 'వదిలివేయి',
	'patrol-resume' => 'కొనసాగించడానికి ఇక్కడ నొక్కండి.',
	'group-patroller' => 'పర్యవేక్షకులు',
	'group-patroller-member' => 'పర్యవేక్షకుడు',
	'grouppage-patroller' => '{{ns:project}}:పర్యవేక్షకుడు',
	'right-patroller' => 'పేజీలను పర్యవేక్షించగలగడం',
);

/** Tetum (Tetun)
 * @author MF-Warburg
 */
$messages['tet'] = array(
	'patrol-revert-reason' => 'Motivu:',
);

/** Tajik (Cyrillic script) (Тоҷикӣ)
 * @author Ibrahim
 */
$messages['tg-cyrl'] = array(
	'patrol-revert-reason' => 'Сабаб:',
	'patrol-skip' => 'Ҷаҳиш',
);

/** Tajik (Latin script) (tojikī)
 * @author Liangent
 */
$messages['tg-latn'] = array(
	'patrol-revert-reason' => 'Sabab:',
	'patrol-skip' => 'Çahiş',
);

/** Thai (ไทย)
 * @author Mopza
 * @author Octahedron80
 * @author Woraponboonkerd
 */
$messages['th'] = array(
	'patrol-revert-reason' => 'เหตุผล:',
	'patrol-skip' => 'ข้าม',
	'group-patroller' => 'ผู้ตรวจการ',
	'group-patroller-member' => 'ผู้ตรวจการ',
);

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'group-patroller' => 'Patrullar',
	'group-patroller-member' => 'patrul',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'patrol' => 'Patrulyahan ang mga pagbabago',
	'patrol-desc' => 'Pinainam na ugnayang-hangganan na pangpatrolya na may pakikisalo sa dami ng bilang ng gawain',
	'patrol-endorse' => 'Itaguyod',
	'patrol-revert' => 'Ibalik sa dati',
	'patrol-revert-reason' => 'Dahilan:',
	'patrol-skip' => 'Laktawan',
	'patrol-reverting' => 'Ibinabalik sa dati: $1',
	'patrol-nonefound' => 'Walang natatagpuang naaangkop na mga pagbabago para sa pagpapatrolya.',
	'patrol-endorsed-ok' => 'Minarkahan bilang napatrolya na ang pagbabago.',
	'patrol-endorsed-failed' => 'Hindi maaaring markahan bilang napatrolya na ang pagbabagong ito.',
	'patrol-reverted-ok' => 'Ibinalik sa dati ang pagbabago.',
	'patrol-reverted-failed' => 'Hindi maibalik sa dati ang pagbabago.',
	'patrol-skipped-ok' => 'Hindi pinapansin ang pagbabago.',
	'patrol-reasons' => '* Payak na pambababoy/bandalismo
* Pagsubok ng baguhan
* Tingnan ang pahina ng usapan',
	'patrol-another' => 'Magpakita ng iba pang pagbabago, kung may makukuha.',
	'patrol-stopped' => 'Pinili mong huwag magpatrolya ng iba pang pagbabago. $1',
	'patrol-resume' => 'Pindutin dito upang magpatuloy.',
	'group-patroller' => 'Mga tagapatrolya',
	'group-patroller-member' => 'tagapatrolya',
	'grouppage-patroller' => '{{ns:project}}:Tagapatrolya',
	'right-patroller' => 'Patrulyahan ang mga pahina',
);

/** Turkish (Türkçe)
 * @author Emperyan
 * @author Joseph
 * @author Karduelis
 * @author Suelnur
 */
$messages['tr'] = array(
	'patrol' => 'Değişiklikleri gözle',
	'patrol-desc' => 'Geliştirilmiş devriye arayüzü, işyükü paylaşımı ile',
	'patrol-endorse' => 'Teyit et',
	'patrol-revert' => 'Geri al',
	'patrol-revert-reason' => 'Neden:',
	'patrol-skip' => 'Atla',
	'patrol-reverting' => 'Geri alınıyor: $1',
	'patrol-nonefound' => 'Gözlenebilecek uygun değişiklik bulunamadı.',
	'patrol-endorsed-ok' => 'Değişiklik gözlenmiş olarak işaretlendi.',
	'patrol-endorsed-failed' => 'Değişiklik gözlenmiş olarak işaretlenemedi.',
	'patrol-reverted-ok' => 'Değişiklik geri alındı.',
	'patrol-reverted-failed' => 'Değişiklik geri alınamadı.',
	'patrol-skipped-ok' => 'Değişiklik görmezden geliniyor.',
	'patrol-reasons' => '* Basit vandallık
* Acemi testi
* Tartışma sayfasına bakın',
	'patrol-another' => 'Varsa, başka bir değişikliği göster.',
	'patrol-stopped' => 'Başka bir değişikliği gözlememeyi seçtiniz. $1',
	'patrol-resume' => 'Devam etmek için buraya tıklayın.',
	'group-patroller' => 'Devriyeler',
	'group-patroller-member' => '{{GENDER:$1|devriye}}',
	'grouppage-patroller' => '{{ns:project}}:Devriye',
	'right-patroller' => 'Sayfaları gözle',
);

/** Tatar (Cyrillic script) (Татарча)
 * @author Ильнар
 * @author Рашат Якупов
 */
$messages['tt-cyrl'] = array(
	'patrol' => 'Тикшерү',
	'patrol-resume' => 'Дәвам итү өчен монда басыгыз',
	'group-patroller' => 'Тикшерүче',
	'group-patroller-member' => '{{GENDER:$1|тикшерүче}}',
	'grouppage-patroller' => '{{ns:project}}:Тикшерү',
	'right-patroller' => 'Битләрне тикшерү',
);

/** Uyghur (Arabic script) (ئۇيغۇرچە)
 * @author Sahran
 */
$messages['ug-arab'] = array(
	'group-patroller' => 'چارلىغۇچىلار',
	'group-patroller-member' => 'چارلىغۇچى',
);

/** Ukrainian (Українська)
 * @author AS
 * @author Ahonc
 * @author Vox
 * @author Тест
 */
$messages['uk'] = array(
	'patrol' => 'Патрулювання',
	'patrol-desc' => 'Вдосконалений інтерфейс патрулювання з розподіленням робочого навантаження',
	'patrol-endorse' => 'Підтвердити',
	'patrol-revert' => 'Відкотити',
	'patrol-revert-reason' => 'Причина:',
	'patrol-skip' => 'Відкласти',
	'patrol-reverting' => 'Відкіт: $1',
	'patrol-nonefound' => 'Не вдалося знати відповідні редагування для патрулювання.',
	'patrol-endorsed-ok' => 'Редагування було позначено патрульованим.',
	'patrol-endorsed-failed' => 'Не вдалося затвердити редагування.',
	'patrol-reverted-ok' => 'Редагування було відкинуто.',
	'patrol-reverted-failed' => 'Не вдалося відкинути редагування.',
	'patrol-skipped-ok' => 'Ігнорування редагування.',
	'patrol-reasons' => '* Просто вандалізм
* Тест новачка
* Див. сторінку обговорення',
	'patrol-another' => 'Показати інше редагування, якщо доступно.',
	'patrol-stopped' => 'Ви вирішили не патрулювати інше редагування. $1',
	'patrol-resume' => 'Натисніть тут, щоб продовжити.',
	'group-patroller' => 'Патрульні',
	'group-patroller-member' => '{{GENDER:$1|патрульний|патрульна}}',
	'grouppage-patroller' => '{{ns:project}}:Патрульні',
	'right-patroller' => 'Патрулювання сторінок',
);

/** Urdu (اردو) */
$messages['ur'] = array(
	'patrol-revert-reason' => 'وجہ:',
);

/** Vèneto (Vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'group-patroller' => 'Patujadori',
	'group-patroller-member' => 'patujador',
);

/** Veps (Vepsän kel')
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'patrol-revert-reason' => 'Sü:',
	'group-patroller' => 'Patrulirujad',
	'group-patroller-member' => '{{GENDER:$1|patrulirui}}',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'patrol' => 'Tuần tra sửa đổi',
	'patrol-desc' => 'Giao diện tuần tra cải tiến với chia sẻ khối lượng công việc',
	'patrol-endorse' => 'Chứng nhận',
	'patrol-revert' => 'Lùi sửa',
	'patrol-revert-reason' => 'Lý do:',
	'patrol-skip' => 'Bỏ qua',
	'patrol-reverting' => 'Đang lùi: $1',
	'patrol-nonefound' => 'Không có sửa đổi thích hợp nào tìm thấy để tuần tra.',
	'patrol-endorsed-ok' => 'Sửa đổi đã được đánh dấu đã tuần tra.',
	'patrol-endorsed-failed' => 'Sửa đổi không thể đánh dấu đã tuần tra.',
	'patrol-reverted-ok' => 'Sửa đổi đã được lùi lại.',
	'patrol-reverted-failed' => 'Không thể lùi sửa đổi.',
	'patrol-skipped-ok' => 'Bỏ qua sửa đổi.',
	'patrol-reasons' => '* Phá hoại đơn giản
* Thử nghiệm của người mới
* Xem trang thảo luận',
	'patrol-another' => 'Hiển thị sửa đổi khác, nếu có.',
	'patrol-stopped' => 'Bạn đã chọn không tuần tra sửa đổi khác. $1',
	'patrol-resume' => 'Nhấn vào đây để tiếp tục.',
	'group-patroller' => 'Tuần tra viên',
	'group-patroller-member' => '{{GENDER:$1}}tuần tra viên',
	'grouppage-patroller' => '{{ns:project}}:Tuần tra viên',
	'right-patroller' => 'Tuần tra trang',
);

/** Volapük (Volapük)
 * @author Malafaya
 * @author Smeira
 */
$messages['vo'] = array(
	'patrol-revert' => 'Sädunön',
	'patrol-revert-reason' => 'Kod:',
);

/** Wu (吴语) */
$messages['wuu'] = array(
	'patrol-revert-reason' => '理由：',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'patrol' => 'פאטראלירן רעדאקטירונגען',
	'patrol-revert' => 'צוריקשטעלן',
	'patrol-revert-reason' => 'אורזאַך:',
	'patrol-skip' => 'איבערהיפן',
	'patrol-reverting' => 'צוריקשטעלן: $1',
	'patrol-reverted-ok' => 'די באַאַרבעטעונג איז געווארן צוריקגעשטעלט',
	'patrol-reverted-failed' => 'די באַאַרבעטונג האט נישט געקענט ווערן צוריקגעשטעלט.',
	'group-patroller' => 'פאטראלירער',
	'group-patroller-member' => '{{GENDER:$1|פאטראלירער|פאטראלירערין}}',
	'grouppage-patroller' => '{{ns:project}}:פאטראלירער',
	'right-patroller' => 'פאטראלירן בלעטער',
);

/** Cantonese (粵語)
 * @author Hillgentleman
 * @author Shinjiman
 */
$messages['yue'] = array(
	'patrol' => '巡啲編輯',
	'patrol-endorse' => '簽署支持',
	'patrol-revert' => '打回頭',
	'patrol-revert-reason' => '原因:',
	'patrol-skip' => '跳',
	'patrol-reverting' => '打回頭：$1',
	'patrol-nonefound' => '揾唔到好嘅版本來簽。',
	'patrol-endorsed-ok' => '嘜咗哩版做「巡過」',
	'patrol-endorsed-failed' => '哩版唔嘜得做「巡過」。',
	'patrol-reverted-ok' => '打咗版嘢回頭。',
	'patrol-reverted-failed' => '打唔到哩版嘢回頭。',
	'patrol-skipped-ok' => '唔理哩次編輯。',
	'patrol-reasons' => '* 塗鴉
* 生手試玩
* 見討論頁',
	'patrol-another' => '試揾另一版睇吓。',
	'patrol-stopped' => '你决定唔再巡。 $1',
	'patrol-resume' => '撳呢度繼續。',
	'group-patroller' => '巡邏員',
	'group-patroller-member' => '巡邏員',
	'grouppage-patroller' => '{{ns:project}}:巡邏員',
);

/** Simplified Chinese (‪中文(简体)‬)
 * @author Gzdavidwong
 * @author Liangent
 * @author Shinjiman
 * @author Xiaomingyan
 */
$messages['zh-hans'] = array(
	'patrol' => '编辑巡逻',
	'patrol-desc' => '分担工作量以增强巡逻界面',
	'patrol-endorse' => '批注',
	'patrol-revert' => '回退',
	'patrol-revert-reason' => '原因：',
	'patrol-skip' => '跳过',
	'patrol-reverting' => '正在回退：$1',
	'patrol-nonefound' => '没有找到可巡逻编辑。',
	'patrol-endorsed-ok' => '此修改已标记巡逻记录。',
	'patrol-endorsed-failed' => '此修改无法标记巡逻记录。',
	'patrol-reverted-ok' => '编辑被回退。',
	'patrol-reverted-failed' => '编辑不能回退。',
	'patrol-skipped-ok' => '忽略编辑。',
	'patrol-reasons' => '* 纯粹破坏
* 新手测试
* 参见讨论页',
	'patrol-another' => '显示其他存在的编辑。',
	'patrol-stopped' => '您选择不巡逻其他编辑。$1',
	'patrol-resume' => '点击这里继续。',
	'group-patroller' => '巡查员',
	'group-patroller-member' => '{{GENDER:$1|巡查员}}',
	'grouppage-patroller' => '{{ns:project}}:巡逻员',
	'right-patroller' => '巡查页面',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Frankou
 * @author KilluaZaoldyeck
 * @author Mark85296341
 * @author Shinjiman
 * @author Wrightbus
 */
$messages['zh-hant'] = array(
	'patrol' => '複查編輯',
	'patrol-desc' => '分擔工作量以增強巡查介面',
	'patrol-endorse' => '批註',
	'patrol-revert' => '回退',
	'patrol-revert-reason' => '原因：',
	'patrol-skip' => '略過',
	'patrol-reverting' => '正在回退：$1',
	'patrol-nonefound' => '沒有複查修改的記錄。',
	'patrol-endorsed-ok' => '此編輯已標記複查記錄。',
	'patrol-endorsed-failed' => '此編輯無法標記複查記錄。',
	'patrol-reverted-ok' => '編輯被回退。',
	'patrol-reverted-failed' => '編輯無法回退。',
	'patrol-skipped-ok' => '忽略編輯。',
	'patrol-reasons' => '* 純粹破壞
* 新人測試
* 參見討論頁',
	'patrol-another' => '列出其他存在的編輯。',
	'patrol-stopped' => '你選擇不複查其他編輯。$1',
	'patrol-resume' => '點擊這裡繼續。',
	'group-patroller' => '巡查員',
	'group-patroller-member' => '{{GENDER:$1|巡查員}}',
	'grouppage-patroller' => '{{ns:project}}:巡邏員',
	'right-patroller' => '巡查頁面',
);

