# Webspider / Webcrawler auf Basis von PHP
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



