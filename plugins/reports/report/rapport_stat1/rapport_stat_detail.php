<?php

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

$dbu = new DbUtils();

#echo "type : ".$_GET['type']."<br>";
#echo "cat : ".$_GET['cat']."<br>";
#echo "priority : ".$_GET['priority']."<br>";
#echo "date_debut : ".$_GET['date_debut']."<br>";
#echo "date_fin : ".$_GET['date_fin']."<br>";

$data_debut=$_GET['date_debut'];
$data_fin=$_GET['date_fin'];

$report = new PluginReportsAutoReport(" Détails ");

$cols = array( 
	new PluginReportsColumn('num_ticket',__('N°'))
	,new PluginReportsColumnLink('id','Titre','Ticket', array('with_navigate' => true, 'sorton' => 'glpi_tickets.id'))
	, new PluginReportsColumn('date_ticket', __('Date d ouverture'))
	, new PluginReportsColumn('date_resol', __('Date de résolution'))
	, new PluginReportsColumn('Temps_resol', __('Durée de résolution (h)'))
	 );
    $report->setColumns($cols);

	$query = "select glpi_tickets.id as num_ticket, glpi_tickets.id , glpi_tickets.date as date_ticket, glpi_tickets.solvedate as date_resol,glpi_tickets.name 
	,ROUND(glpi_tickets.solve_delay_stat /3600 ,2) as 'Temps_resol'
	FROM `glpi_tickets`
	WHERE glpi_tickets.is_deleted = 0 ".
	" AND glpi_tickets.date between '".$data_debut."' and '".$data_fin."'";
	$query .=" AND glpi_tickets.type=".$_GET['type'];
	$query .=" AND glpi_tickets.itilcategories_id=".$_GET['cat'];
	$query .=" AND glpi_tickets.priority=".$_GET['priority'];
	$query .=" ORDER BY glpi_tickets.type,  glpi_tickets.priority";
	
	$report->setSqlRequest($query);
	$report->execute();
	