<?php
//Einbinden von var.php
include("var.php");
// Array mit Optionen zum Context
$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: web_crawler_by_nicoketzer/github\n"));
// Create the stream context.
$context = stream_context_create($options);

function get_details($url){
    global $context;
	// Create a new instance of PHP's DOMDocument class.
	$doc = new DOMDocument();
	// Use file_get_contents() to download the page, pass the output of file_get_contents()
	// to PHP's DOMDocument class.
	$side = @file_get_contents($url, false, $context);
	@$doc->loadHTML($side);
	//Festlegen eines Titels falls kein Titeltag vorhanden ist
	$title = "Kein Titel vorhanden";
	//Titelbug fixen
	$split_1 = explode("</title>",$side);
	$temp_1 = $split_1[0];
	$split_2 = explode("<title>",$temp_1);
	if(isset($split_2[1])){
        $title = $split_2[1];
    }
	//Titeelbug fix ende
	// Give $description and $keywords no value initially. We do this to prevent errors.
	$description = "Keine Beschreibung vorhanden";
	$keywords = "Keine Keywords vorhanden";
	// Create an array of all of the pages <meta> tags. There will probably be lots of these.
	$metas = $doc->getElementsByTagName("meta");
	// Loop through all of the <meta> tags we find.
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		// Get the description and the keywords.
		if(strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if(strtolower($meta->getAttribute("name")) == "keywords")
			$keywords = $meta->getAttribute("content");
	}
    //Versuchen ein Bild zu Bekommen
    $links = $doc->getElementsByTagName("link");
	for ($i = 0; $i < $links->length; $i++) {
		$link_tab = $links->item($i);
		// Get the description and the keywords.
		if(strtolower($link_tab->getAttribute("rel")) == "shortcut icon"){
            $favico = $link_tab->getAttribute("href");
        }
    }
    if(!isset($favico)){
        $tmp = explode("://",$url);
        $prot = $tmp[0];
        $url_n = $tmp[1];
        $tmp = explode("/",$url_n);
        $url_n = $tmp[0];
        $fav_url = $prot . "://" . $url_n . "/favicon.ico";
        $tmp_ico = file_get_contents($fav_url,false,$context);
        $status_line = $http_response_header[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $status = $match[1];
        if ($status !== "200") {
            $favico = "/search/no_fav.ico";
        }else{
            $favico = $fav_url;
        }
    }
    if($description == ""){
        $description = "Keine Beschreibung vorhanden";
    }
    if($keywords == ""){
        $keywords = "Keine Keywords vorhanden";
    }
    if($title == ""){
        $title = "Kein Titel vorhanden";
    }
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
	$mysqli = new_mysqli();
    foreach($links_found as $push_url){
        //&Uuml;berpr&uuml;fung ob schon vorhanden
        $sql = "SELECT * FROM `search` WHERE `url` = '" . bin2hex($push_url) . "'";
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        if(!isset($res[0]["url"])){
            $sql = "INSERT INTO `search`(`url`, `indexed_last`, `title`, `description`, `favico_url`, `keywords`) VALUES ('" . bin2hex($push_url) . "','0','','','','')";
            start_sql($mysqli,$sql);
        }else{
            continue;
        }
    }
    close_mysqli($mysqli);
    return array("title"=>bin2hex($title),"desc"=>bin2hex($description),"favico"=>bin2hex($favico),"keywords"=>bin2hex($keywords));
}
function data_crawl(){
    global $name;
    //Hier Funktion bauen das zu allen URL&apos;s Details wie Titel beschreibung und ICON gespeichert werden
    //Evtl. Localen Ordner erstellen wo icon&apos;s gespeichert werden
    $sql = "SELECT * FROM `search` WHERE `title` = '' OR `description` = '' OR `favico_url` = '' OR `keywords` = ''";
    $mysqli = new_mysqli();
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    $i = 0;
    foreach($res as $get_all){
        if(file_exists($name) && $i < 30){
            $i++;
            $url = hex2bin($get_all["url"]);
            //Alle Details bekommen
            //R&uuml;ckgabe der Werte erfolgt bereits im hex-Format
            $details = get_details($url);
            $sql = "UPDATE `search` SET `title`='" . $details["title"] . "', `description`='" . $details["desc"] . "',`favico_url`='" . $details["favico"] . "',`keywords`='" . $details["keywords"] . "',`indexed_last`='" . time() . "' WHERE `url` = '" . $get_all["url"] . "'";
            start_sql($mysqli,$sql);
        }else{
            break;
        }
    }
    close_mysqli($mysqli);
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
    //SQL Befehl der alle Datens&auml;tze w&auml;hlt die noch nicht ge-crawlt wurde
    $sql = "SELECT * FROM `search` WHERE `indexed_last` = '0'";
    //Neues Mysqli-Objekt erstellen
    $mysqli = new_mysqli();
    //R&uuml;ckgabe des Befehls auswerten und in Array speichern
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    //Datenbank-Verbindung schlie�en
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
        $sql = "UPDATE `search` SET `indexed_last`='" . time() . "' WHERE `url` = '" . $crawl_el["url"] . "'";
        //Neuse Mysqli-Objekt erstellen
        $mysqli = new_mysqli();
        //Updaten des Verwendeten URL&apos;s durch ausf&uuml;hren des Befehls
        start_sql($mysqli,$sql);
        //Speichern der gefundenen URL&apos;s
        foreach($found_urls as $push_url){
            //&Uuml;berpr&uuml;fung ob schon vorhanden
            $sql = "SELECT * FROM `search` WHERE `url` = '" . bin2hex($push_url) . "'";
            //R&uuml;ckgabe auswerten
            $res = sql_result_to_array(start_sql($mysqli,$sql));
            //Entscheidung treffen
            if(!isset($res[0]["url"])){
                //Wenn hier reingegangen wird existiert der Datensatz noch nicht
                
                //Befehl der den neuen URL zur DB hinzuf&uuml;gt
                $sql = "INSERT INTO `search`(`url`, `indexed_last`, `title`, `description`, `favico_url`, `keywords`) VALUES ('" . bin2hex($push_url) . "','0','','','','')";
                //Ausf&uuml;hren des Befehls
                start_sql($mysqli,$sql);
            }else{
                //Wenn hier reingegangen wird existiert der Datensatz bzw. der URL schon
                //in der DB und wird &uuml;bersprungen
                continue;
            }
        }
        //Schlie�en des Mysqli Objekts
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
