<?php

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");
#include_once ('rapport_test.fr_FR.php');

$dbu = new DbUtils();

echo "id_Objet : ".$_GET['id_Objet']."<br>";
echo "etat : ".$_GET['etat']."<br>";

$report = new PluginReportsAutoReport(" Détails ");

$cols = array( 
	new PluginReportsColumn('name',__('Type'))
	, new PluginReportsColumn('otherserial', __('N° Inventaire'))
	, new PluginReportsColumn('date_mod', __('Date de modification'))
	, new PluginReportsColumn('Temps_resol', __('Durée de résolution (h)'))
	 );
$report->setColumns($cols);
$report->displayCriteriasForm();
$report->setSubNameAuto();


$query00 = "SELECT itemtype, name FROM `glpi_plugin_genericobject_types` where id=".$_GET['id_Objet']."";
// echo "$query00<br>";
$object_types = $DB->query($query00);
$numrows = $DB->numRows($object_types);
if ($numrows>0) { 
	$data00 = $DB->fetch_assoc($object_types);
	
	$query01 = "SELECT table_name FROM `information_schema`.`TABLES`
	WHERE `TABLE_SCHEMA` = 'glpi' AND `TABLE_TYPE` = 'BASE TABLE' AND `TABLE_NAME` LIKE '%glpi_plugin_genericobject%'
	AND table_comment LIKE '%".$data00['itemtype']."%'";
	$table_name = $DB->query($query01);
	// echo "$query01<br>";
	$numrows = $DB->numRows($table_name);
	if ($numrows>0) {
		$data01 = $DB->fetch_assoc($table_name);
		$query = "SELECT name, `otherserial`, `date_mod`  from `".$data01['table_name']."` tab WHERE tab.`is_deleted`=0  and states_id=".$_GET['etat']." order  BY `otherserial`";
		// echo "$query<br>";
		$report->setSqlRequest($query);
		$report->execute();
	};
};

Html::footer();
	