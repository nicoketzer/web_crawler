# Webspider / Webcrawler auf Basis von PHP
## Gedanke hinter Projekt
Der Gedanke hinter dem Projekt ist die Funktionsweiße eines Crawlers anhand einer "Einfachen" Programmiersprache darzustellen.

Noch dazu können die PHP-Scripte mit leichten anpassungen auf jedem System ausgeführt werden egal welches Betriebssystem verwendet wird.

Zudem wird ja gleichzeitig Versucht das PHP-Projekt auch in C/C++ bzw. C# umzusetzen (noch nicht öffentlich)
## Setup was Benötigt wird:
Ein Server der eine Linux-Dist. als OS verwendet 

Mysql-Datenbank (am besten ohne Speicher-Begrenzung), optional mit anschließender PHPMyAdmin - Installation

Webserver(z.B. Apache2 oder nginx)

Eine PHP - Installation (ab PHP 7.x)

Shell Zugriff auf den Server(nur für Einrichten und Wartung)

Stabile Internetverbindung(am Besten ab 10Mbit/s Down und 2Mbit/s Up)

## Ratsam:
Wenn man ein "schwaches" Setup hat ist es sehr anzuraten seinen DB-Server auf einem
externen (oder zumindest andern) Rechner zu hosten.

Hat den Grund damit nicht unnötig die Zeiten in die Höhe gehen die gebraucht werden um 
unter Last einen Neuen DB-Eintrag zu erstellen / diesen zu aktualisieren

## Installation
Zuerst müssen alle Pakete etc. auf den neusten Stand gebracht werden

(Root-Zugang wird auf den meisten Systemen benötigt, dieser kann mit "sudo -i" errungen werden)
 ```
apt-get update -y && apt-get upgrade -y
 ```
Anschließend wird der Apache2 - Server installiert
 ```
apt-get install apache2 -y
 ```
Nun folgt die installation von PHP, Mysql-Server und PHPMyAdmin
 ```
apt-get install php mysql-server phpmyadmin -y
 ```

Um PHPMyAdmin auch nutzen zu können muss jetzt noch ein Nutzer dafür angelegt werden (Root-Nutzer funktioniert nicht)
 ```
sudo mysql -u root -p [von_ihnen_vorher_festgelegtes_Passwort]
 ```
 ```
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'ihr_passwort';
 ```
Anschließend müssen sie den neuen Admin-User noch die Rechte über alle DB´s etc. übertragen.
 ```
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
 ```
Anschließend können sie mit dem Befehl
 ```
exit;
 ```
Mysql in der Console verlassen.


Ab jetzt sind sie fähig sich mit dem Account "admin" über das phpMyAdmin-Interface einzuloggen.

Anschließend wird auf den firsch eingerichteten apache2 server zugegriffen (um die Funktion zu Prüfen)

Öffne hierzu den Browser und tippe "http://[IP_ADRESSE_DES_SERVERS]" ein.

Hierbei sollte eine Art "It work´s" - Seite erscheinen.

Sollte dies geklappt haben so kann man nun an den URL ein "/phpmyadmin" anhängen und dürfte auf das Login-Interface der 
phpMyAdmin-Oberfläche kommen.

Hier können wir uns jetzt mit dem oben erstellten Nutzer "admin" einloggen.


### Datenbank-Setup
Zunächst erstellen wir eine Datenbank (am besten mit dem namen "webserver")

In dieser Datenbank erstellen wir anschließend eine Tabelle mit dem Namen "Search".

(DETAILS ZUM ERSTELLEN DER DATENBANK ODER SOGAR EIN SQL-BEFEHL FOLGEN WENN DER CODE HOCHGELADEN WIRD)


Zusätzlich müssen wir über die Oberfläche einen Neuen Benutzer anlegen der Nur auf diese Datenbank Zugriff hat.
Hinzu kommt das dieser Benutzer nur die Befehle "SELECT","INSERT" und "UPDATE" können darf.


### PHP - Codesetup
Zum Konfigurieren des Codes öffne die "var.php" und passe folgende Sachen an:
#### Wichtig
$db_user --> Trage hier den Oben erstellten Benutzer ein

$db_pass --> Trage hier das Passende Passwort ein

$db_port --> Ändere hier den Port falls dein Mysql - Server nicht den Standart-Port verwendet

$db_name --> Trage hier den Namen deiner neuen Datenbank ein

$db_addr --> Trage hier die IP oder Domain deines Mysql - Servers ein (oder wenn er sich auf dem Selben Server befindet leer lasen)

#### Optional
$user_agent --> Hier kannst du einen String eintragen unter dem dein Crawler arbeitet (Standart: web_crawler_by_nicoketzer/github)

$timeout --> Hier kannst du von zu Crawlenden URL´s die maximale Antwortzeit einstellen (Standart: 20 Sekunden)

$transmit_data --> Kannst du auf "true" oder "false" setzen(Standart: true). Legt fest ob du gefundene URL´s in eine Globale Datenbank überträgst oder nicht

$transmit_node_data --> Kannst du auf "true" oder "false" setzen (Standart: false). Ist nur wirksam wenn $transmit_data auf true ist, und übermittelt dann beim finden neuer 
Url´s Daten wie Verwendetes Betriebssystem etc. an das Projekt.

$transmit_error --> Kannst du auf "true" oder "false" setzen(Standart: true). Legt fest ob Fehler die auftretten an mich übermittelt werden oder nicht

$main_crawler --> Kannst du auf "cli" oder "web" setzen(Standart: web). Gibt an ob du Hauptsächlich als Hintergrunddienst arbeitest oder über den Broser crawelen willst. Die Option CLI ist leistungsfähiger und wird empfohlen, jedoch kann es dann zu PHP-Skript-Timeout fehlern kommen über die Weboberfläche

$update_auto --> Kannst du auf "true" oder "false" setzen(Standart: false). Gibt an ob der Crawler sich automatisch aktualisiert oder nicht.



## Suche aus den gefundenen Ergebnissen
Die Suche kann auf zwei Arten durchgeführt werden:
### 1. Lokale Suche
Bei der Lokalen suche werden nur die selbstgefundenen Ergebnisse durchforstet. Hierfür musst du auf "http://[SERVER_IP_ADRESSE]/search.php" gehen.
Anschließend kannst du nach Seiten Beschreibungen oder Keywords (oder eine kombi daraus) suchen
### 2. Globale Suche
Solltest du deine Lokalen Suchfunde übermitteln so beinhaltet die Globale suche auch die suche in deinen Lokalen Funden. Wie oben auch kannst du hier nach 
Beschreibungen, Keywords oder Seiten (oder eine kombi daraus) suchen. Um eine Globale suche zu starten musst du auf:

<https://search.german-backup.de>

gehen. Anschließend steht dir die selbe Oberfläche als die Lokale zur Verfügung.



