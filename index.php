<?php
	include_once('tbs_class.php');
	session_start();
	$TBS = new clsTinyButStrong;
	
	if(isset($_GET["start"]))
	{
		unset($_SESSION["show_result"]);
		header("Location: index.php");
	}
	/* Näyttää mikä olisi soveltuva ala */
	if(isset($_SESSION["show_result"]))
	{
		/* Hakee parhaiden soveltuvan alan array-muodossa */
		$match_array = find_top_matches($_SESSION["score"]);
		$TBS->LoadTemplate('templates/result.html');
		$TBS->MergeBlock('sivu',$match_array);
		$TBS->Show();
		return;
	}
	if(!isset($_POST["start"]) && !isset($_POST["yes_button"]) && !isset($_POST["no_button"]))
	{
		/* Aloitussivu */
		$TBS->LoadTemplate('templates/index.html');
	}
	else
	{
		/* Sovelluksen käyttämät kysymykset. 
		
			Kysymyksiin määritellään erikseen assosiatiivinen array, johon voi määritellä kuinka kysymykset vaikuttavat
			tulokseen. Käyttäjän vastatessa Kyllä, positiiviset tulokset lisätään alan lopputulokseen. Ei-vastaukset
			ei vaikuta mitenkään.
			
			Huom: arrayssa avain (=soveltuvuusala) ei voi esiintyä kahdesti.
		
		*/
		$questions = array(
					"Haluaisitko suunnitella itsellesi talon?" => array("arkkitehtuuri" => 5, "kova" => 0.5), 
					"Haluatko tietää, miten terveellinen ravinto tai lääkkeet vaikuttavat?" => array("kova" => 0.5, "psykologia" => 2, "biologia" => 3, "soteli" => 3, "laaketiede" => 3),
					"Oletko aina ajatellut, että osaisit opettaa asiat paremmin kuin omat opettajasi?" => array("kova" => 0.5, "opetusala" => 5),
					"Tykkäätkö viestiä englannin kielellä?" => array("kova" => 0.5, "englanti" => 5, "hiphop" => 1, "kauppatiede" => 1),
					//"Haluatko työllistyä hip hop -kulttuurin parissa?" => array("hip hop" => 5),
					"Tykkäätkö ilmaista itseäsi musiikin, tanssin tai kuva-/sanataiteen kautta?" => array("arkkitehtuuri" => 2, "opetusala" => 1, "soteli" => 1, "hip hop" => 3, "luova_ilmaisu" => 5, "luova_kirjoitus" => 2),
					"Haluaisitko olla Indiana Jones?" => array("kova" => 0.5, "historia" => 2),
					"Haluatko miljonääriksi?" => array("kauppatiede" => 1),
					"Kuunteletko mielelläsi ihmisten ongelmia?" => array("kova" => 0.5, "soteli" => 2, "laaketiede" => 2, "oikeustiede" => 2, "poliisi" => 2, "psykologia" => 5, "opetusala" => 1),
					"Oletko kiinnostunut menneistä ajoista?" => array("kova" => 0.5, "historia" => 5, "arkkitehtuuri" => 1),
					"Tiedätkö, miten yhteiskunnan ongelmat pitäisi ratkaista?" => array("kova" => 0.5, "yhteiskunta" => 3, "opetusala" => 1),
					//"Haluatko työskennellä ihmisten parissa?" => array("opetusala" => 3, "psykologia" => 3, "hip hop" => 2),
					//"Haluatko kehittää ilmaisutaitoasi?" => array("luova_ilmaisu" => 5, "hip hop" => 3),
					"Voisitko kuvitella olevasi luokanopettaja?" => array("kova" => 0.5, "opetusala" => 5),
					//"Kiinnostaako sinua arskkitehtuuri?" => array("arkkitehtuuri" => 5),
					//"Kiehtovatko sinua kasvit tai eläimet?" => array("biologia" => 5),
					"Olen epävarma koulutusalasta, mutta tiedän haluavani yliopistoon tai ammattikorkeakouluun." => array("kova" => 5),
					//"Oletko kiinnostunut ilmaisutaidosta?" => array("luova_ilmaisu" => 5),
					"Haluaisitko olla kirjailija tai kehittyä kirjoittajana?" => array("luova_ilmaisu" => 3, "luova_kirjoitus" => 6),
					"Haluatko ymmärtää paremmin taloutta?" => array("kova" => 0.5, "kauppatiede" => 3),
					"Inspiroiko sinua hip hop -kulttuuri?" => array("hip hop" => 5),
					//"Haluaisitko kirjoittaa jotain yhtä hienoa kuin Taru sormusten herrasta?" => array("luova_kirjoitus" => 5),
					//"Haluatko tietää miten solu toimii?" => array("biologia" => 5),
					//"Oletko kaveripiirisi moraalinen kompassi?" => array("oikeustiede" => 5),
					"Oletko himoliikkuja tai pidätkö terveyttä tärkeänä?" => array("kova" => 0.5, "poliisi" => 2, "laaketiede" => 3, "psykologia" => 2, "opetusala" => 1, "soteli" => 3), 
					"Haluatko pääministeriksi?" => array("kova" => 0.5, "yhteiskunta" => 1),
					"Tykkäisitkö leikkiä poliisia ja rosvoa aikuisena?" => array("kova" => 0.5, "poliisi" => 3),
					"Oletko kiinnostunut ihmisen anatomiasta?" => array("kova" => 0.5, "biologia" => 3, "laaketiede" => 3, "soteli" => 2),
					"Kiinnostaako sinua sijoittaminen?" => array("kova" => 0.5, "kauppatiede" => 3),
					//"Kiinnostaa sinua työ ihmisten parissa?" => array("yhteiskunta" => 2, "opetus" => 2, "englanti" => 2, "psykologia" => 2),
					"Kiinnostaako sinua mainonta ja markkinointi?" => array("kova" => 0.5, "kauppatiede" => 3, "luova_kirjoitus" => 1),
				    "Pidätkö historiasta, yhteiskuntakysymyksistä tai politiikasta?" => array("kova" => 0.5, "historia" => 5, "yhteiskunta" => 5, "opetus" => 1),
					"Oletko valmis matkustamaan tai haluatko tehdä töitä kansainvälisissä tehtävissä?" => array("kova" => 0.5, "englanti" => 5, "kauppatiede" => 2, "yhteiskunta" => 1),
					"Onko ympäristönsuojelu sinulle tärkeää?" => array("kova" => 0.5, "biologia" => 5), 
					"Oletko kiinnostunut eri aikakausien rakennustyyleistä?" => array("kova" => 0.5, "arkkitehtuuri" => 3, "historia" => 2),
					"Oletko kiinnostunut tekniikan kehityksestä?" => array("kova" => 0.5, "arkkitehtuuri" => 4),
					"Pidätkö piirtämisestä tai maalaamisesta?" => array("arkkitehtuuri" => 2, "luova_ilmaisu" => 5, "hip hop" => 2),
					"Kiinnostaako sinua huonon käytöksen tai rikollisen toiminnan ehkäiseminen?" => array("kova" => 0.5, "poliisi" => 6, "oikeustiede" => 4, "soteli" => 2, "opetusala" => 1, "psykologia" => 3),
					"Oletko valmis lukemaan tieteellisiä artikkeleita ja kirjoja?" => array("arkkitehtuuri" => -5, "biologia" => -5, "englanti" => -5, "historia" => -5, "kauppatiede" => -2, "kova" => -10, "laaketiede" => -8, "oikeustiede" => -8, "opetusala" => -5, "soteli" => -2, "poliisi" => -1, "psykologia" => -5, "yhteiskunta" => -5),
					"Haluatko työn olevan fyysistä?" => array("soteli" => -3, "poliisi" => -5, "hip hop" => -5),
					//"Haluaisitko olla puutarhuri?" => array("biologia" => 5),
					"Haluatko ymmärtää luonnontieteellisiä ilmiöitä?" => array("kova" => 0.5, "arkkitehtuuri" => 1, "biologia" => 4, "laaketiede" => 3), 
					"Kiinnostaisiko sinua tehdä analyysejä tai laboratoriokokeita?" => array("kova" => 0.5, "biologia" => 3, "soteli" => 5, "laaketiede" => 3),
					"Kiinnostaisiko sinua tarjota terveyteen ja hyvinvointiin liittyvää neuvontaa yksilöille tai ryhmille? "=> array("kova" => 0.5, "soteli" => 3, "psykologia" => 2),
					"Oletko kiinnostunut vieraista kulttuureista?" => array("kova" => 0.5, "englanti" => 6, "historia" => 3, "yhteiskunta" => 2, "hip hop" => 1, "opetusala" => 1), 
					"Onko sinulla tarve luoda jotain taiteen keinoin?" => array("luova_ilmaisu" => 5, "luova_kirjoitus" => 5, "hip hop" => 3),
					//"Kiinnostaisiko sinua ilmaista itseäsi taiteellisesti?" => array("luova_ilmaisu" => 5, "luova_kirjoitus" => 5, "hip hop" => 3),
					"Haluatko ottaa kantaa yhteiskunnallisiin asioihin?" => array("kova" => 0.5, "yhteiskunta" => 3, "hiphop" => 2, "luova_kirjoitus" => 2),
					"Haluatko auttaa vaikeuksiin joutuneita ihmisiä?" => array("kova" => 0.5, "oikeustiede" => 5, "psykologia" => 3, "poliisi" => 3, "soteli" => 2),
					"Oletko kiinnostunut kansantalouden kehittämisestä?" => array("kova" => 0.5, "kauppatiede" => 2, "yhteiskunta" => 3),
					//"Ovatko säännöt, lait tai käytöstavat mielestäsi tärkeitä?" => array("opetusala" => 3, "oikeustieteet" => 5, "poliisi" => 5),
					//"Oletko kiinnostunut asiakirjojen laatimisesta?" => array("arkkitehtuuri" => 3, "oikeustiede" => 5, "kauppatiede" => 3),
					"Haluatko neuvotella kaupasta tai sopimuksesta?" => array("kova" => 0.5, "kauppatiede" => 5, "oikeustiede" => 5),
					"Onko esiintyminen sinulle luontaista?" => array("kova" => 0.5, "luova_ilmaisu" => -6, "hip hop" => -5, "yhteiskunta" => -1, "oikeustiede" => -1, "opetus" => -3),
					);
					
		$max_questions = sizeof($questions);
		
		/* Resetoidaan arvot, kun kysely aloitetaan alusta */
		if(!isset($_SESSION["cur_question"]) || isset($_POST["start"]))
		{
			$_SESSION["cur_question"] = 0;
			$_SESSION["score"] = array(
									"arkkitehtuuri" => 0,
									"biologia" => 0, 
									"englanti" => 0,
									"hip hop" => 0,
									"historia" => 0,
									"kauppatiede" => 0,
									"kova" => 0,
									"luova_ilmaisu" => 0,
									"luova_kirjoitus" => 0,
									"laaketiede" => 0,
									"oikeustiede" => 0,
									"opetusala" => 0,
									"psykologia" => 0,
									"soteli" => 0,
									"yhteiskunta" => 0,
									"poliisi" => 0,
									);
			
		}
		/* Kun vastataan kyllä/ei, arvioidaan vastaus */
		if(isset($_POST["yes_button"]) || isset($_POST["no_button"]))
		{
			rate_answer($questions, $_SESSION["cur_question"], isset($_POST["yes_button"]));
			$_SESSION["cur_question"]++;

		}
		$cur_question = $_SESSION["cur_question"];
		if($cur_question >= $max_questions)
		{
			$_SESSION["show_result"] = 1;
			header("Location: index.php");
			return;
		}
		$show_question_number = $cur_question + 1;
		$question_text = array_keys($questions)[$cur_question];

		$TBS->LoadTemplate('templates/query.html');
	}
	$TBS->Show();
	
	/* Merkitsee sessioon ylös, mitä kyselyyn vastaaja vastasi ja arvotetaan jo tässä vaiheessa vastauksia */
	
	function rate_answer($q_array, $q_number, $answer)
	{
		$q_text = array_keys($q_array)[$q_number];
		$score_array = $q_array[$q_text];
		foreach($score_array as $key => $value)
        {
			/* Kyllä-vastaukset - jos kysymykseen vastataan kyllä, annetaan pistemäärä mikä on positiivinen */
			if($answer && $value > 0 && array_key_exists($key, $_SESSION["score"]))
			{
				$_SESSION["score"][$key] += $value;
			}
			/* Ei - jos kysymykseen vastataan ei, annetaan pistemäärä joka on negatiivinen */
			if(!$answer && $value < 0 && array_key_exists($key, $_SESSION["score"]))
			{
				$_SESSION["score"][$key] += $value;
			}
        }
	}
	/* Etsii soveltuvimman alan ja palauttaa sen kuvaukset array-muodossa. */
	
	function find_top_matches($score_array)
	{
		$results = array();
		$highest = -10000000;
		/* Getting highest score */
		
		foreach($score_array as $key => $points)
		{
			if($points > $highest)
			{
				$highest = $points;
			}
		}
		foreach($score_array as $key => $points)
		{
			if($points == $highest)
			{
				array_push($results, get_description($key));
			}
		}
		return $results;
	}
	
	/* Opetuslinjojen kuvaukset ja linkit */
	
	function get_description($name)
	{
		$list = array(		
						"arkkitehtuuri" => array("name" => "Arkkitehtuurin ja tekniikan aloille suuntaavat opinnot",
											"link" => "https://www.alkio.fi/opintolinjat/arkkitehtuurin-ja-tekniikan-aloille-suuntaavat-opinnot", 
											"desc" => "Linjalla opiskelet tehokkaasti kohti diplomi-insinööri- ja arkkitehtikoulutuksen yhteisvalintaa (DIA). Linjalla kerrataan ".
													  "tärkeimmät lukion oppimäärät pitkästä matematiikasta ja harjoitellaan ongelmanratkaisutaitoja."),
													  
						"biologia" => array("name" => "Biologia ja luonnontieteet",
											"link" => "https://www.alkio.fi/opintolinjat/biologia-ja-luonnontieteet", 
											"desc" => "Biologian ja luonnontieteiden linjalla voit opiskella yliopistotutkinnon osia, joista bio- ja ".
													  "ympäristötieteiden, farmasian ja kemian perusopinnot ovat olleet suosituimmat. Linjalla voit ".
													  "suorittaa ohjatusti myös ravitsemustieteen perusopintoja, viestintä- ja kieliopintoja sekä luonnonvarat ".
													  "ja ympäristö -opintoja. Voit myös valmistautua muiden alojen valintakokeisiin, joissa tarvitaan ".
													  "luonnontieteiden osaamista kuten metsätaloustiede tai biolääketiede."),
													  
						"englanti" => array("name" => "Englannin kieli ja kansainvälisyys",
											"link" => "https://www.alkio.fi/opintolinjat/englannin-kieli-ja-kansainvalisyys", 
											"desc" => "Englannin kielen ja kansainvälisyyden linjalla voit opiskella yliopistotutkinnon osia, kuten englannin ".
													  "kielen perusopinnot, monikulttuurisuus-opinnot tai viestinnän ja journalistiikan opinnot. Lisäksi ".
													  "opiskellaan viestintä- ja kieliopintoja sekä muita kursseja opiston tarjonnasta."),
													  
											
						"hip hop" =>  array("name" => "Hip hop -elementit",
											"link" => "https://www.alkio.fi/opintolinjat/hiphop-elementit", 
   									 	    "desc" => "Me tarjoamme Suomessa ainutlaatuisen lukuvuoden mittaisen hip hop -elementteihin ja kulttuuriin ".
												      "syventyvän linjan. Vuoden aikana perehdyt hip hopin eri elementteihin ja saat työkaluja ".
													  "kulttuurialalla työskentelyyn. Ohjaajina toimivat oman alansa kokeneet ammattilaiset, jotka jakavat ".
													  "tietoja, taitoja ja kokemuksia vuosien varrelta. Alkio-opistolta saat tarvittavat puitteet eri ".
													  "elementtien harjoittelemista varten. Opiskeluun on mahdollista saada toisen asteen opintotukea ja ".
													  "opistolla on mahdollisuus asumiseen opiston asuntolassa."),

						"historia" => array("name" => "Historia ja kulttuuritieteet",
											"link" => "https://www.alkio.fi/opintolinjat/historia-ja-kulttuuritieteet", 
											"desc" => "Valitset aineyhdistelmät omien päämääriesi ja kiinnostuksesi mukaan. ".
													  "Vuoden aikana käsitellään lisäksi erityisteemoja menneisyyttä ja nykymaailman ymmärtämistä ".
													  "silmällä pitäen. Mukana ovat valintojesi mukaan esimerkiksi Esihistoria ja vanhat kulttuurit, Antiikin ".
													  "historia, Kulttuurin aikakaudet tai Nykyhistoria. Tavoitteena on itsemme ja  muuttuvan maailman ".
													  "ymmärtäminen. Linjan aineiden syventäminen tukee myös yo-kokeitaan korottavia (mm. historia, äidinkieli ".
													  "ja uskonto). Kirjoittamisen tukemiseen kiinnitetään erityishuomiota."),

						"kauppatiede" => array("name" => "Kauppatieteet",
											"link" => "https://www.alkio.fi/opintolinjat/kauppatieteet",
											"desc" => "Kauppatieteiden linjalla opiskeltavat avoimen yliopiston opinnot johdattavat kauppatieteiden ".
													  "maailmaan ja tekevät tutuksi myös valintakokeissa esiin nousevia teemoja."),

						"kova" => 	array("name" => "Korkeakouluun valmentavat opinnot",
											"link" => "https://www.alkio.fi/opintolinjat/korkeakouluun-valmentavat-opinnot", 
											"desc" => "Korkeakouluun valmentavalla linjalla voit etsiä omaa alaa eri yliopisto- ja ammattikorkeakouluopinnoista. ".
													  "Saat ohjausta ja tukea valintojen tekemiseen. Linjalla opiskelet akateemisia opiskelutaitoja, vahvistat ".
													  "osaamistasi kirjoittamisessa, tenttivastaamisessa, kielissä, teet valitsemiasi korkeakouluopintoja ja ".
													  "valmistaudut valitsemasi alan valintakokeisiin. Linjan opetusohjelma toteutetaan kaksi kertaa vuodessa. ".
													  "Voit siis aloittaa opinnot elokuussa tai tammikuussa. Jos aloitat syksyllä, niin voit jatkaa opintoja ".
													  "korkeakouluun valmentavalla linjalla koko lukuvuoden tehden linjan opintojen lisäksi valitsemiasi opintoja muilta linjoilta."),

						"luova_ilmaisu" => array("name" => "Luova ilmaisu",
											"link" => "https://www.alkio.fi/opintolinjat/luova-ilmaisu", 
											"desc" => "Luovan ilmaisun linjalla voit opiskella sanataidetta, graafista suunnittelua ja muita itseäsi ".
													  "kiinnostavia opintoja. Opinnot ovat avoimia kaikille ilmaisutaidosta kiinnostuneille ja elämässään uutta ".
													  "suuntaa hakeville. Ilmaisulinjalla opiskelu ei edellytä aiempia opintoja. Linjalla tehdään laaja-alaisesti ".
													  "audiovisuaalisia ja kehollis-tilallisia ilmaisu- ja viestintäkokeiluja sekä kehitetään omaa oppimisen ja ".
													  "elämisen taitoa. Opintoja on tarjolla mm. improvisaatiosta ja sanataiteesta graafiseen suunnitteluun ja videokuvaukseen."),
						
						"luova_kirjoitus" => array("name" => "Luova kirjoittaminen",
											  "link" => "https://www.alkio.fi/opintolinjat/luova-kirjoittaminen", 
											  "desc" => "Opintojen runko muodostuu luovasta kirjoittamisesta ja kirjallisuuteen tutustumisesta sisältäen monipuolisia ".
														"kirjoitusharjoitteita ja keskusteluja tekstien äärellä. Voit edistää omaa mahdollista kirjoitusprojektiasi. ".
														"Lisäksi voit opiskella yliopistotutkinnon osia kirjallisuudesta sekä kehittää puheviestintä-, kieli- ja ".
														"tiedonhankintataitojasi."),
													  
						"laaketiede" => array("name" => "Lääketieteeseen suuntaavat opinnot",
											  "link" => "https://www.alkio.fi/opintolinjat/laaketieteeseen-suuntaavat-opinnot", 
											  "desc" => "Opinnoissa käydään tarkalla kammalla ja runsaalla rautalangalla läpi lukion biologia, kemia ja fysiikka. ".
														"Lukuvuoden aikana opiskelija oppii kemiassa ja fysiikassa tarvittavan luonnontieteellisen ajattelun ja saavuttaa ".
														"hyvän fysiologian, anatomian ja solubiologian tuntemuksen. Valintakokeessa tärkein onnistumisen tae on vankka ".
														"laskurutiini, jonka saavuttamiseksi työskennellään kovasti koko lukuvuosi. Opettajat ovat usein ja ".
														"säännöllisesti opiskelijoiden tavoitettavissa henkilökohtaista laskuapua varten."),

						"oikeustiede" => array("name" => "Oikeustieteet",
											  "link" => "https://www.alkio.fi/opintolinjat/oikeustieteet", 
											  "desc" => "Me tarjoamme mahdollisuuden opiskella laajasti oikeustieteen opintoja, jotka auttavat sinua pääsemään ".
														"opiskelemaan haluamaasi alaa. Alkio-opistossa suorittamasi avoimen yliopiston opintokokonaisuudet voit ".
														"myöhemmin sisällyttää osaksi korkeakouluopintojasi. Lukuvuoden aikana kehität opiskelutekniikkaasi ja ".
														"ehdit myös kerrata kieliä ja suorittaa juridisen kirjoittamisen ja tiedonhankinnan perusteet. ".
														"Kevätlukukaudella valmistaudut oikeustieteellisiin valintakokeisiin."),
														
						"opetusala" => array("name" => "Opetusala",
											 "link" => "https://www.alkio.fi/opintolinjat/opetusala", 
											 "desc" => "Opettajat ja kasvatustieteilijät toimivat paitsi monenlaisissa opetustehtävissä varhaiskasvatuksesta ".
													   "aikuiskoulutukseen, myös monenlaisissa muissa työtehtävissä mm. hallintovirkamiehinä, kouluttajina, ".
													   "tutkijoina, koulutussuunnittelijoina ja henkilöstöpäällikköinä."),

						"psykologia" => array("name" => "Psykologia",
											  "link" => "https://www.alkio.fi/opintolinjat/psykologia", 
											  "desc" => "Psykologit toimivat hyvin monenlaisissa kunnallisissa, valtiollisissa ja ".
														"yksityisen alan työtehtävissä, sosiaali- ja terveysalalla sekä koulu- ja opetuspuolella. ".
														"Me tarjoamme 1–2 lukuvuoden pituisen opintokokonaisuuden, joka tukee valmistautumistasi ".
														"kevään valintakokeisiin haluamallesi alalle, opintoja, jotka voit liittää myöhemmin ".
														"korkeakoulututkintoosi sekä asiantuntevaa ohjausta ja opetusta."),
														
						"soteli" => 	array("name" => "Soteli – sosiaali- ja terveysalan opinnot",
											  "link" => "https://www.alkio.fi/opintolinjat/soteli", 
											  "desc" => "Sosiaali-, terveys- ja liikunta-alat tarjoavat työtä tulevaisuudessa! Alan tehtäviin ".
														"kouluttautuneiden tarve työmarkkiinoilla kasvaa, sillä eri-ikäinen väestö lapsista ".
														"vanhoihin tarvitsee osaavia ammattilaisia tuottamaan alojen monimuotoisia palveluita. ".
														"Jyväskylään vasta perustettu kuntoutusalan osaamiskeskittymä tarjoaa myös tulevaisuudessa ".
														"työllistymismahdollisuuksia kuntouksen asiantuntijoille. Soteli -linjalla voit aloittaa ".
														"korkeakouluopintosi. Samalla opit opiskelu- ja vuorovaikutustaitoja sekä laajennat yleissivistystäsi."),
														
						"yhteiskunta" =>array("name" => "Yhteiskunta- ja valtiotieteet",
											  "link" => "https://www.alkio.fi/opintolinjat/yhteiskunta-ja-valtiotieteet", 
											  "desc" => "Haluatko tietää, mitä ympärilläsi, suomalaisessa yhteiskunnassa, Euroopan unionissa ja ".
														"globaalissa järjestelmässä tapahtuu? Haluatko toimia aktiivisena yhteiskunnan jäsenenä ".
														"ja vaikuttaa yhteiskunnallisiin asioihin? Yhteiskunta- ja valtiotieteet antavat välineitä ".
														"vaikuttamiseen ja aktiiviseen yhteiskunnassaa toimimiseen sekä hyvät valmiudet hyvin ".
														"erilaisiin työtehtäviin. On hyvä opiskella taustoja ja teorioita dialogin, keskustelun, ".
														"ymmärtämisen ja  päättämisen pohjaksi."),
														
						"poliisi" => 	array("name" => "Suuntana poliisiala",
											  "link" => "https://www.alkio.fi/opintolinjat/suuntana-poliisiala", 
											  "desc" => "Poliisialalle valmentavalla linjalla treenaat fyysisen kunnon lisäksi kirjoittamista, tekstien lukemista ".
														"ja referointia, esseevastaamista, vahvistat opiskelutaitojasi, kertaat kieliä ja harjoittelet ".
														"valintakokeiden haastattelu-, yksilö- ja ryhmätilanteita. Lisäksi voit opiskella valitsemisiasi ".
														"korkeakouluopintoja mielenkiintosi mukaan. Henkilökohtainen ja ryhmänohjaus tukevat sinua opinnoissasi."),
													  );
		return $list[$name];
	}
?>

