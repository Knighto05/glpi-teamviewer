<?php

function plugin_teamviewer_install(){
    global $DB;

    $migration = new Migration("2.6.0");
    if ($DB->tableExists('glpi_computers')) {  
        $migration->addField('glpi_computers', 'teamviewer_id', 'string');
    }

    $migration->executeMigration();
    return true;
}

function plugin_teamviewer_uninstall(){
    global $DB;
    if ($DB->tableExists('glpi_computers')) { 
        $DB->queryOrDie('ALTER TABLE glpi_computers DROP COLUMN teamviewer_id', $DB->error());
    }

    return true;
}