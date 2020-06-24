<?php
#Erstmal alle abhängigkeiten einbinden
include("functions.php");

########################################
##Starten des Codes mit while-Schleife##
##Vorher muss ausgelesen werden ob web##
##-Use oder CLI-Use ausgewählt wurde####
########################################

if($main_crawler == "web"){
  #######
  ##Web##
  #######
  crawler();
}else{
  #######
  ##CLI##
  #######
  crawler_cli();
}

?>
