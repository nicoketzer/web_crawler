<?php
    #####################################
    ##MySql - Daten wie Benutzername#####
    ##und Passwort damit man dynamisch###
    ##anpassen kann wenn sich Daten######
    ##ändern sollten.####################
    #####################################
    $db_user = "[DATENBANK_NUTZER]";
    $db_pass = "[DATENBANK_PASSWORT]";
    $db_name = "[DATENBANK_NAME]";
    $db_addr = "[DATENBANK_IP]";
    $db_port = "[DATENBANK_PORT]";
    #####################################
    ##Pfad zur ".run" Datei f&uuml;r ####
    ##Schleifen o.&auml;. damit man aus##
    ##diesen "ausbrechen" kann und ######
    ##diese beenden kann.################
    #####################################
    $name = "[DIR_FOR_RUN_FILE].run";
    #####################################
    ##Optionale Variablen mit ###########
    ##voreingestellten Werten############
    #####################################
    $main_crawler = "web";
    $user_agent = "web_crawler_by_nicoketzer/github";
    $timeout = 20;
    $transmit_data = true;
    $transmit_node_data = false;
    $transmit_error = true;
    $update_auto = false;
?>
