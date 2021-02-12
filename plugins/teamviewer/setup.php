<?php
/**
 * Teamviewer - 1.0.0
 */

 function plugin_init_teamviewer(){
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['teamviewer'] = true;

    Plugin::registerClass('PluginTeamviewerRemote', array('addtabon' => array('Computer')));
 }

 function plugin_version_teamviewer() {
    return array(   'name'      => "Teamviewer Remote",
                    'version'   => '1.0.0',
                    'author'    => 'Hasina Zo Ambinintsoa',
                    'license'   => 'GPLv2+',
                    'homepage'  => '',
                    'minGlpiVersion'    => '0.85'
                );
 }

 function plugin_teamviewer_check_config() {
     return true;
 }

 function plugin_teamviewer_check_prerequisites() {
     if(version_compare(GLPI_VERSION, '0.85', 'lt')){
         echo "This plugin requires GLPI = 0.85";
         return false;
     }

     return true;
 }