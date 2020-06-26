<?php
//Einbinden von var.php
include("var.php");
// Array mit Optionen zum Context
$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: " . $user_agent . "\n"));
// Create the stream context.
$context = stream_context_create($options);

function get_details($url){
    //Einbinden der db-Variablen
    global $db_tbl;
    global $db_tbl_url;
    global $db_tbl_indexed_last;
    global $db_tbl_title;
    global $db_tbl_desc;
    global $db_tbl_favicon;
    global $db_tbl_keywords;
    //Einbinden des Favicon - Standart URL&apos;s
    global $favicon_standart_url;
    //Verf&uuml;gbar machen des $context in der Funktion
    global $context;
	// Neues Dom-Objekt
	$doc = new DOMDocument();
	// Die Seite $url wird &uuml;ber file_get_contents() heruntergeladen und tempor&auml;r in 
	// $side gespeichert
	$side = @file_get_contents($url, false, $context);
	//Der HTML-Code wird in das Dom-Objekt "hinein" geladen
    @$doc->loadHTML($side);
	//Festlegen eines Titels falls kein Titeltag vorhanden ist
	$title = "Kein Titel vorhanden";
	//Titel aus HTML-Splitten
	//HTML hinter Titel-Element "weg"-splitten
	$split_1 = explode("</title>",$side);
	//Rest-St&uuml;ck w&auml;hlen
	$temp_1 = $split_1[0];
	//HTML vor Titel-Element "weg"-splitten
	$split_2 = explode("<title>",$temp_1);
	//Schauen ob "HTML" bzw. Text &uuml;brig bleibt und wenn ja 
	//das als Titel Setzen
	if(isset($split_2[1])){
        $title = $split_2[1];
    }
	// Eine Beschreibung und Keywords setzen falls keine im HTML gefunden werden
	$description = "Keine Beschreibung vorhanden";
	$keywords = "Keine Keywords vorhanden";
	// Alle Meta-Tag&apos;s aus dem HTML nehemen
	$metas = $doc->getElementsByTagName("meta");
	// Alle Meta-Tags durchgehen
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		// Schauen ob Beschreibungs- oder Keyword-Metatag gefunden werden
		if(strtolower($meta->getAttribute("name")) == "description"){
			//Wenn eine Beschreibung gefunden wurde diese hier &uuml;bernehmen
            $description = $meta->getAttribute("content");
		}
		if(strtolower($meta->getAttribute("name")) == "keywords"){
			//Wenn Keywords gefunden wurden hier setzen
            $keywords = $meta->getAttribute("content");
        }
	}	
    //Versuchen ein Bild zu Bekommen (Favicon)
    $links = $doc->getElementsByTagName("link");
	for ($i = 0; $i < $links->length; $i++) {
		$link_tab = $links->item($i);
		// Schauen ob passender Tag gefunden wird
		if(strtolower($link_tab->getAttribute("rel")) == "shortcut icon"){
            $favico = $link_tab->getAttribute("href");
        }
    }
    //Fix f&uuml;r Favicon falls der tag anderen rel-Parameter hatte 
    //oder das Element nicht existierte
    if(!isset($favico)){
        //Bekommen des Protokolls auf einfache Art
        $tmp = explode("://",$url);
        //Temp. Festlegen bzw. Speichern des Protokolls
        $prot = $tmp[0];
        //Restlicher URL
        $url_n = $tmp[1];
        //Splitten aller Ordner etc.
        $tmp = explode("/",$url_n);
        //Nur Pure Domain behalten
        $url_n = $tmp[0];
        //Temp. zusammensetzen eines Link&apos;s f&uuml;r Favicon
        $fav_url = $prot . "://" . $url_n . "/favicon.ico";
        //Abfrage Starten
        $tmp_ico = file_get_contents($fav_url,false,$context);
        //R&uuml;ckgabe der Abfrage einfangen
        $status_line = $http_response_header[0];
        //Extrahieren des Status-Codes
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $status = $match[1];
        //&Uuml;berpr&uuml;fen ob ein favicon vorhanden war
        if ($status !== "200") {
            //Wenn nicht wird Standart - Icon gesetzt
            $favico = $favicon_standart_url;
        }else{
            //Wenn schon wird dieser URL gesetzt
            $favico = $fav_url;
        }
    }
    //&Uuml;berpr&uuml;fen der Werte
    if($description == ""){
        //Falls Beschreibung doch leer war
        $description = "Keine Beschreibung vorhanden";
    }
    if($keywords == ""){
        //Falls doch keine Keywords da sind
        $keywords = "Keine Keywords vorhanden";
    }
    if($title == ""){
        //Falls doch kein Titel vorhanden ist
        $title = "Kein Titel vorhanden";
    }
    
    ###########################################    
    ##Um die Performance besser zu machen und##
    ##um den Traffic zu reduzieren wird bei####
    ##einem Data-Crawl auch sofort ein#########
    ##normaler Crawl des URL&apos;s############
    ##durchgef&uuml;hrt.#######################   
    ###########################################    
        
	// Array Erstellen das alle "a"-Tags umfasst
	$linklist = $doc->getElementsByTagName("a");
	//Array erstellen wo alle Verwertbaren url&apos;s gespeichert werden
	$links_found = array();
	// Schleife die alle URL&apos;s abarbeitet
	foreach ($linklist as $link) {
	    //Bekommen des URL&apos;s von dem "a"-Tag   
    	$l =  $link->getAttribute("href");
    	// URL-Parsen und verschiedenen Controllen unterziehen
    	if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
    		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
    	} else if (substr($l, 0, 2) == "//") {
    		$l = parse_url($url)["scheme"].":".$l;
    	} else if (substr($l, 0, 2) == "./") {
    		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
    	} else if (substr($l, 0, 1) == "#") {
    	   //&Uuml;berspringen wenn href nur ein Platzhalter war bzw. ein #-Verweiß
    		continue;
    	} else if (substr($l, 0, 3) == "../") {
    		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
    	} else if (substr($l, 0, 11) == "javascript:") {
    	   //&Uuml;berspringen wenn href ein javascript ausf&uuml;hren sollte
    		continue;
    	} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
    		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
    	}
        //Passende URLs hinzuf&uuml;gen
        if(!in_array($l,$links_found)){
            array_push($links_found,$l);
        }
	}
	//Erstellen eines neuen Mysqli-Objekt&apos;s
	$mysqli = new_mysqli();
	//Abarbeiten aller verwertbaren URL&apos;s
    foreach($links_found as $push_url){
        //&Uuml;berpr&uuml;fung ob schon vorhanden
        $sql = "SELECT * FROM `" . $db_tbl . "` WHERE `" . $db_tbl_url . "` = '" . bin2hex($push_url) . "'";
        //Ausf&uuml;hren und verarbeiten des Befehls
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        //&Uuml;berpr&uuml;fen
        if(!isset($res[0]["url"])){
            //Befehl zum hinzuf&uuml;gen des Datensatzes
            $sql = "INSERT INTO `" . $db_tbl . "`(`" . $db_tbl_url . "`, `" . $db_tbl_indexed_last . "`, `" . $db_tbl_title . "`, `" . $db_tbl_desc . "`, `" . $db_tbl_favicon . "`, `" . $db_tbl_keywords . "`) VALUES ('" . bin2hex($push_url) . "','0','','','','')";
            //Ausf&uuml;hren des Befehls --> Hinzuf&uuml;gen
            start_sql($mysqli,$sql);
        }else{
            //&Uuml;berspringen da schon vorhanden in DB
            continue;
        }
    }
    //Schließen des Mysqli-Objekt&apos;s    
    close_mysqli($mysqli);
    //R&uuml;ckgabe der Details
    return array("title"=>bin2hex($title),"desc"=>bin2hex($description),"favico"=>bin2hex($favico),"keywords"=>bin2hex($keywords));
}
function data_crawl(){
    //Einbinden der db-Variablen
    global $db_tbl;
    global $db_tbl_url;
    global $db_tbl_indexed_last;
    global $db_tbl_title;
    global $db_tbl_desc;
    global $db_tbl_favicon;
    global $db_tbl_keywords;
    //Einbinden des Hauptmodus
    global $main_crawler;
    //Einbinden des Namen&apos;s des ".run"-Files
    global $name;
    //Einbinden der Maximal zu abfertigenden Elemente
    global $max_el_data_crawl;
    //Befehl um Elemente zu bekommen die noch keine Daten haben    
    $sql = "SELECT * FROM `" . $db_tbl . "` WHERE `" . $db_tbl_title . "` = '' OR `" . $db_tbl_desc . "` = '' OR `" . $db_tbl_favicon . "` = '' OR `" . $db_tbl_keywords . "` = ''";
    //Erstellen eines neuen Mysqli-Objekts
    $mysqli = new_mysqli();
    //Ausf&uuml;hren und auswerten des Sql-Ergebnisses aus dem Befehl
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    //Setzen des Anfangswertes des Z&auml;hlers
    $i = 0;
    //Abgehen jedes Elements
    foreach($res as $get_all){
        if($main_crawler == "web"){
            //&Uuml;berpr&uuml;fen ob counter noch unter festgelegtem Wert
            if($i < $max_el_data_crawl){
                //Hochrechnen des Counter&apos;s
                $i++;
                //Umwandeln des URL&apos;s
                $url = hex2bin($get_all["url"]);
                //Alle Details bekommen
                //R&uuml;ckgabe der Werte erfolgt bereits im hex-Format
                $details = get_details($url);
                //Erstellen eines "Speichern-Der-Daten"-Sql Befehl
                $sql = "UPDATE `" . $db_tbl . "` SET `" . $db_tbl_title . "`='" . $details["title"] . "', `" . $db_tbl_desc . "`='" . $details["desc"] . "',`" . $db_tbl_favicon . "`='" . $details["favico"] . "',`" . $db_tbl_keywords . "`='" . $details["keywords"] . "',`" . $db_tbl_indexed_last . "`='" . time() . "' WHERE `" . $db_tbl_url . "` = '" . $get_all["url"] . "'";
                //Ausf&uuml;hren des Befehls
                start_sql($mysqli,$sql);
            }else{
                //Beenden des Loop&apos;s
                break;
            }
        }else{
            //&Uuml;berpr&uuml;fen ob ".run" - File noch existiert und maximale anzahl 
            //noch nicht &uuml;berschritten wurde
            if(file_exists($name) && $i < $max_el_data_crawl){
                //Hochrechnen des Counter&apos;s
                $i++;
                //Umwandeln des URL&apos;s
                $url = hex2bin($get_all["url"]);
                //Alle Details bekommen
                //R&uuml;ckgabe der Werte erfolgt bereits im hex-Format
                $details = get_details($url);
                //Erstellen eines "Speichern-Der-Daten"-Sql Befehl
                $sql = "UPDATE `" . $db_tbl . "` SET `" . $db_tbl_title . "`='" . $details["title"] . "', `" . $db_tbl_desc . "`='" . $details["desc"] . "',`" . $db_tbl_favicon . "`='" . $details["favico"] . "',`" . $db_tbl_keywords . "`='" . $details["keywords"] . "',`" . $db_tbl_indexed_last . "`='" . time() . "' WHERE `" . $db_tbl_url . "` = '" . $get_all["url"] . "'";
                //Ausf&uuml;hren des Befehls
                start_sql($mysqli,$sql);
            }else{
                //Beenden des Loop&apos;s
                break;
            }
        }
    }
    //Schließen des Mysqli-Objekts
    close_mysqli($mysqli);
    //R&uuml;ckgabe
    return true;
}
function recrawl(){
    //Hier funktion bauen die vorhandene URL&apos;s nochmal Crawlt
    return true;
}

function crawl($url){
    global $context;
    //Eine neues DOMDocument erstellen
	$doc = new DOMDocument();
	//Zwischenspeichern der Seite
	$side = @file_get_contents($url,false,$context);
    //Das downloaden der Seite
	@$doc->loadHTML($side);
	// Create an array of all of the links we find on the page.
	$linklist = $doc->getElementsByTagName("a");
	//Array erstellen wo alle Verwertbaren url&apos;s gespeichert werden
	$links_found = array();
	// Loop through all of the links we find.
	foreach ($linklist as $link) {
		$l =  $link->getAttribute("href");
		// Process all of the links we find. This is covered in part 2 and part 3 of the video series.
		if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
		} else if (substr($l, 0, 2) == "//") {
			$l = parse_url($url)["scheme"].":".$l;
		} else if (substr($l, 0, 2) == "./") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
		} else if (substr($l, 0, 1) == "#") {
			continue;
		} else if (substr($l, 0, 3) == "../") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		} else if (substr($l, 0, 11) == "javascript:") {
			continue;
		} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		}
        //Passende URLs hinzuf&uuml;gen
        if(!in_array($l,$links_found)){
            array_push($links_found,$l);
        }
	}
    return $links_found;
}
//Funktion die aufgerufen wird wenn meist &uuml;ber web genutzt wird
function crawler(){
    //Einbinden der db-Variablen
    global $db_tbl;
    global $db_tbl_url;
    global $db_tbl_indexed_last;
    global $db_tbl_title;
    global $db_tbl_desc;
    global $db_tbl_favicon;
    global $db_tbl_keywords;
    //SQL Befehl der alle Datens&auml;tze w&auml;hlt die noch nicht ge-crawlt wurde
    $sql = "SELECT * FROM `" . $db_tbl . "` WHERE `" . $db_tbl_indexed_last . "` = '0'";
    //Neues Mysqli-Objekt erstellen
    $mysqli = new_mysqli();
    //R&uuml;ckgabe des Befehls auswerten und in Array speichern
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    //Datenbank-Verbindung schließen
    close_mysqli($mysqli);
    //&Uuml;berpr&uuml;fen ob ein Datensatz existiert der noch nicht gecrawlt wurde
    if(isset($res[0])){
        //Wenn hier rein gegangen wird dann existiert ein Element das noch nicht gecrawlt wird
        
        //Random ein Element aus der Menge nehmen
        $crawl_el = $res[array_rand($res)];
        //URL von Hex nach Bin umwandeln
        $url = hex2bin($crawl_el["url"]);
        //Alle URL&apos;s die bei dem gecrawlten URL gefunden wurden als R&uuml;ckgabe im Array speichern
        $found_urls = crawl($url);
        //SQL-Befehl der den gerade ge-crawlten Datensatz updatet
        $sql = "UPDATE `" . $db_tbl . "` SET `" . $db_tbl_indexed_last . "`='" . time() . "' WHERE `" . $db_tbl_url . "` = '" . $crawl_el["url"] . "'";
        //Neuse Mysqli-Objekt erstellen
        $mysqli = new_mysqli();
        //Updaten des Verwendeten URL&apos;s durch ausf&uuml;hren des Befehls
        start_sql($mysqli,$sql);
        //Speichern der gefundenen URL&apos;s
        foreach($found_urls as $push_url){
            //&Uuml;berpr&uuml;fung ob schon vorhanden
            $sql = "SELECT * FROM `" . $db_tbl . "` WHERE `" . $db_tbl_url . "` = '" . bin2hex($push_url) . "'";
            //R&uuml;ckgabe auswerten
            $res = sql_result_to_array(start_sql($mysqli,$sql));
            //Entscheidung treffen
            if(!isset($res[0]["url"])){
                //Wenn hier reingegangen wird existiert der Datensatz noch nicht
                
                //Befehl der den neuen URL zur DB hinzuf&uuml;gt
                $sql = "INSERT INTO `" . $db_tbl . "`(`" . $db_tbl_url . "`, `" . $db_tbl_indexed_last . "`, `" . $db_tbl_title . "`, `" . $db_tbl_desc . "`, `" . $db_tbl_favicon . "`, `" . $db_tbl_keywords . "`) VALUES ('" . bin2hex($push_url) . "','0','','','','')";
                //Ausf&uuml;hren des Befehls
                start_sql($mysqli,$sql);
            }else{
                //Wenn hier reingegangen wird existiert der Datensatz bzw. der URL schon
                //in der DB und wird &uuml;bersprungen
                continue;
            }
        }
        //Schließen des Mysqli Objekts
        close_mysqli($mysqli);
        //Datencrawl starten
        data_crawl();
        //R&uuml;ckgabe true
        return true;
    }else{
        //Keine Crawlbaren Datens&auml;tze da daher wird die recrawl Funktion aufgerufen
        return recrawl();
    }    
}
//Funktion die aufgerufen wird wenn meist &uuml;ber cli genutzt wird
function crawler_cli(){
    //Starten der while-Schleife
    while(file_exists($name)){
        //Starten des Crawl - Prozesses
        crawler();
    }
    //Schleife l&auml;uft solange wie der Dienst l&auml;uft da bei stopen des dienstes die 
    //datei $name gel&ouml;scht wird und somit der Loop beendet wird.    
}


/////////////////////////
//AB HIER GEHT DER //////
//MYSQL - PART AN  //////
/////////////////////////

//Mysql Anfrage starten
if(!function_exists("start_sql")){
    function start_sql($mysqli,$sql){
        if($mysqli->prepare($sql)){
            $statement = $mysqli->prepare($sql);
            $statement->execute();
        }else{
            return mysqli_error($mysqli);
        }
        $result = $statement->get_result();
        return $result;
    }
}
//Sql - Resultat als Array zur&uuml;ckgeben
if(!function_exists("sql_result_to_array")){
    function sql_result_to_array($result){
        $array = array();
        while($row = $result->fetch_assoc()) {
            array_push($array,$row);
        }
        return $array;
    }
}
if(!function_exists("new_mysqli")){
function new_mysqli(){
    global $db_user;
    global $db_pass;
    global $db_name;
    global $db_addr;
    global $db_port;
    if($db_port == ""){
        $mysqli = new mysqli($db_addr,$db_user,$db_pass,$db_name);
    }else{
        $mysqli = new mysqli($db_addr.":".$db_port,$db_user,$db_pass,$db_name);
    }
    //echo mysqli_get_host_info($mysqli);
    if ($mysqli->connect_errno) {
        echo "Verbindung fehlgeschlagen: " . $mysqli->connect_error;
        return false;
    }else{
        return $mysqli;
    }
}
}
if(!function_exists("close_mysqli")){
function close_mysqli($mysqli){
    return $mysqli->close();
}
}
?>
