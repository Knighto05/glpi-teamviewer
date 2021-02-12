<?php
/**
 * @version $Id: statticketsbypriority.php 348 2018-01-15 14:28:15Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2018 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");
include_once ('rapport_test.fr_FR.php');

$dbu = new DbUtils();

//TRANS:Average time to resolve tickets = Moyen de temps de résolution des tickets
$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_solve']);
$report02 = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_solve']);
#$report=new PluginReportsAutoReport("Moyen de temps de résolution des tickets par  type et par catégorie");

//Report's search criterias
$data_filtre = new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
    $cols = array( new PluginReportsColumn('Type', __('Type'), array('sorton' => '`glpi_tickets`.`type`'))
	, new PluginReportsColumn('Catégorie', __('Catégorie'), array('sorton' => '`glpi_itilcategories`.`name`'))
	, new PluginReportsColumnInteger('Nb_Ticket', __('Nb Tickets', 'reports'), array('withtotal' => true, 'sorton' => 'Nb_Ticket'))
	 );
    $report->setColumns($cols);
  
 
# requete principale		   
		   $query = "select CASE glpi_tickets.type
                                        WHEN 1 THEN 'Incident'
                                        ELSE 'Demande'
			END as 'Type'
			,`glpi_itilcategories`.`name` as 'Catégorie'
			,COUNT(glpi_itilcategories.name) as 'Nb_Ticket'
			, ROUND(AVG(glpi_tickets.solve_delay_stat /3600) ,2) as 'Temps_resol_moyen'
			FROM `glpi_tickets`,`glpi_itilcategories`
			WHERE `glpi_tickets`.`itilcategories_id` = `glpi_itilcategories`.`id`
			AND glpi_tickets.is_deleted = 0 ".
			$report->addSqlCriteriasRestriction() .
			$dbu->getEntitiesRestrictRequest(' AND ', 'glpi_tickets').
			" GROUP BY glpi_tickets.type, glpi_itilcategories.name";
			
# compter le nombre des lignes par catégorie:
$query02 = "select  req1.type, count(req1.catégorie) as nb, sum(req1.Nb_Ticket) as Tot_nb_ticket  from ($query) as req1 GROUP BY req1.type" ; 			
$reponse02 = $DB->query($query02);
$span_lig=0;
$Span = array();
while ($data02 = $DB->fetch_assoc($reponse02)) {
	$vCat=$data02['type'];
	$Span[$vCat][0]= $data02['nb'];
	$Span[$vCat][1]= $data02['Tot_nb_ticket'];
	$span_lig++;
}

# création d'un formulaire
#echo "<form name='form1' method='POST' action=".$_SERVER['PHP_SELF'].">";
echo "<form name='form1'>";
echo "<center><table border=2  width=60%>";
echo "<caption><b> Temps moyen de résolution par type et par catégorie: ".$data_filtre->getStartDate()." à ".$data_filtre->getEndDate()." </b></caption>";
echo "<thead><tr><th>Type</th><th>Catégorie</th><th>Moyen de Temps de résolution</th><th>Nombre tickets</th><th>Total nombre</th></tr></thead>";
echo"<tbody>";
$reponse = $DB->query($query);
$Sum_Temps_moy=0;
$Sum_Nb_ticket=0;
$Type_02="";
$_SESSION['dataExport']=array();
while ($data = $DB->fetch_assoc($reponse)) {
    $Type = $data['Type'];
	$Cat = $data['Catégorie'];
	$Nb_ticket = $data['Nb_Ticket'];
	$Temps_Moyn = $data['Temps_resol_moyen'];
	
	$Sum_Temps_moy+=$Temps_Moyn;
	$Sum_Nb_ticket+=$Nb_ticket;
	if ($Type_02<>$Type){
		echo "<tr><th>$Type</th><td>$Cat</td><td>$Temps_Moyn</td><td>$Nb_ticket</td><td rowspan=".$Span[$Type][0].">".$Span[$Type][1]."</td></tr>";
		$dataExport[]="$Type;$Temps_Moyn;$Nb_ticket;".$Span[$Type][0].";".$Span[$Type][1].";";
		$Type_02=$Type;
	} else {
		echo "<tr><th>$Type</th><td>$Cat</td><td>$Temps_Moyn</td><td>$Nb_ticket</td></tr>";
		$dataExport[]="$Type;$Temps_Moyn;$Nb_ticket;";
	}
};
$_SESSION['dataExport'] = $dataExport;
echo"</tbody>";
echo "<tfoot><tr><th> Total</th><td></td><td>".ROUND(($Sum_Temps_moy/$Sum_Nb_ticket),2)."</td><td>$Sum_Nb_ticket</td><td>$Sum_Nb_ticket</td></tr></tfoot>";
echo "</table></center>";
#echo "<center><b><input type='submit' name='Export xls' value='Export' onclick='export()' /></b></center>";
echo "<center><b><input type='submit' name='Export' value='Export xls' /></b></center>";
echo "</form>";

if(isset($_REQUEST['Export'])) {
	$fic =fopen('php://output', 'w');
#	$fic = fopen('.//db_export_'. date ( 'Ym-d' ).'.csv» ', 'w');
	if (!$fic) {
	   die("ERROR: Can't write $fic\n");
	};	

	header('Content-Type: text/csv');
#	header('Content-Disposition: attachment; filename="export.csv"');
	header('Content-Disposition: attachment; filename=userdata_'.date("H:i:s").'.csv');
	header('Pragma: no-cache');    
	header('Expires: 0');
	fputcsv($fp, $headers); 
	
	$tab_data=$_REQUEST['dataExport'];
	foreach($tab_data as $row) {
		echo $row;
		fputcsv($fic, $row);
	
	};
	readfile($fic);
	fclose($fic);	
} else {
	echo " 333 ";
};

$report->setSqlRequest($query);
$report->execute(array('withtotal' => true));

 

} else {
   Html::footer();
}

function export() {
	if(isset($_POST['submit'])) { 
		if(isset($_SESSION['dataExport'])){ 
			header('Location: static.html');
			$fh = fopen('./export.txt', 'a');
			$tab_data=$_SESSION['dataExport'];
			foreach($tab_data as $row) {
				echo $row;
				fwrite($fh, $row . "\n");
			};
			fclose($fh);	
		}
	}
}	