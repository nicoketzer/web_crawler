# Doku des Projekts
## Doku von Funktionen 
PHP Funktion crawler():

Die PHP - Funktion crawler() sucht sich aus der Datenbank ein Element heraus und "Crawlt" den URL des Elements durch.
Sollten Meta-Daten gefunden werden z.B. Keywords oder Beschreibung werden diese für diese Element auch gleich gespeichert.
Danach Speichert sie alle Gefundenen URL´s und startet die Funktion "data_crawler()".

PHP Funktion crawler_cli():

Die PHP - Funktion crawler_cli() ruft die Funktion crawler() in einer Endlosschleife auf. Nach jeder Erfolgreichen rückgabe von 
crawler() wird für x ms geweartet. Danach ruft sie sich wieder selber auf und startet den Vorgang somit von vorne.
--> Funktion soll durch While-Schleife ersetzt werden da bei While im normalfall keine PHP-Timeout fehler kommen

PHP Funktion data_crawler():

Crawlt 30 Random aus der DB gewählten URL´s nach Meta-Daten und gibt anschließend True zurück. Nach den URL´s in den Seiten wo er Metadaten sucht
wird nicht geschaut da ansonsten die Menge der Gefundenen URL´s im vergleich zur Menge der fertig verarbeiteten URL´s immer größer werden würde.

Es wurden 30 Elemente gewählt da es ansonsten zu PHP-Timeout Fehlern kommen könnte. 30 Elemente genügen auch föllig da die Elemente die Gefunden werden von 
mal zu mal kleiner werden muss aus dem Grund weil ja "schon vorhandene" - URL´s nicht noch ein zweites mal in die DB. eingetragen werden.

