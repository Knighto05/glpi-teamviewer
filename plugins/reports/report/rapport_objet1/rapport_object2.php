<?php

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php"); 

$dbu = new DbUtils();

$report = new PluginReportsAutoReport(" Détails ");

$cols = array( 
	new PluginReportsColumn('name',__('Type'))
	, new PluginReportsColumn('otherserial', __('N° Inventaire'))
	, new PluginReportsColumn('date_mod', __('Date de modification'))
	, new PluginReportsColumn('Temps_resol', __('Durée de résolution (h)'))
	 );
$report->setColumns($cols);
// $report->displayCriteriasForm();
$report->setSubNameAuto();

if(isset($_GET['id_Objet'])) {
	$_SESSION['id_Objet']=$_GET['id_Objet'];
} else {
	$_GET['id_Objet']=$_SESSION['id_Objet'];
};

if(isset($_GET['etat'])) {
	$_SESSION['etat']=$_GET['etat'];
} else {
	$_GET['etat']=$_SESSION['etat'];	
};		

if($_GET['id_Objet']==0){

	if ($_SESSION['etat']<>'*') {
		$query = "SELECT name, `otherserial`, `date_mod`  from `glpi_computers` tab WHERE tab.`is_deleted`=0  and states_id=".$_GET['etat']." order  BY `otherserial`";
	} else {
		$query = "SELECT name, `otherserial`, `date_mod`  from `glpi_computers` tab WHERE tab.`is_deleted`=0 and states_id>0 order  BY `otherserial`";
	};
	// echo "$query<br>";
	$report->setSqlRequest($query);
	$report->execute();

} else { 	

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
			if ($_SESSION['etat']<>'*') {
				$query = "SELECT name, `otherserial`, `date_mod`  from `".$data01['table_name']."` tab WHERE tab.`is_deleted`=0  and states_id=".$_GET['etat']." order  BY `otherserial`";
			} else {
				$query = "SELECT name, `otherserial`, `date_mod`  from `".$data01['table_name']."` tab WHERE tab.`is_deleted`=0 and states_id>0 order  BY `otherserial`";
			};
			// echo "$query<br>";
			$report->setSqlRequest($query);
			$report->execute();
		};
	};
};	

Html::footer();
	