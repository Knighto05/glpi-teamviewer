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
$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_objet1']);

//Names of the columns to be displayed
$cols = array( 
	new PluginReportsColumn('nom',__('Objet'))
	, new PluginReportsColumn('Deploye', __('Deployé'))
	, new PluginReportsColumn('HS', __('HS'))
	, new PluginReportsColumn('Maintenance', __('En Maintenance'))
	, new PluginReportsColumn('Stock', __('Stock'))
	, new PluginReportsColumn('Neuf', __('Neuf'))
	, new PluginReportsColumn('Usage01', __('Usagé'))
	, new PluginReportsColumn('total', __('Total'))
	 );

// $report->setColumns($cols);

$report->displayCriteriasForm();
$report->setSubNameAuto();

# requete principale		   
$query = "SELECT table_schema, table_name, table_comment 
FROM `information_schema`.`TABLES`
WHERE `TABLE_SCHEMA` = 'glpi' AND `TABLE_TYPE` = 'BASE TABLE' AND `TABLE_NAME` LIKE '%glpi_plugin_genericobject%'
AND table_comment LIKE '%PluginGenericobject%'";
$reponse = $DB->query($query);
$rqt="";
$num_tab=0;
 while ($data = $DB->fetch_assoc($reponse)) {
	$table_schema=$data['table_schema'];
	$table_name=$data['table_name'];
	$table_comment=$data['table_comment'];
	
	$query00 = "SELECT id as id_objet, name FROM `glpi_plugin_genericobject_types` where itemtype='".$table_comment."'";
	$tbl_name = $DB->query($query00);
	$numrows = $DB->numRows($tbl_name);
	$query01 ="";
	if ($numrows>0) { 
		$data00 = $DB->fetch_assoc($tbl_name);
		if ($data00['name']<>'importmoto') {
			$name = $data00['name'];
			$id_objet = $data00['id_objet'];
			$query01 = "SELECT '".$name."' as Objet,'".$id_objet."' as id_Objet, count(id) as Total
			,( select count(tmp1.id) from ( SELECT id,states_id,  max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=1 GROUP BY tab.id) as tmp1 ) as Deploye
			,( select count(tmp2.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=2 GROUP BY tab.id) as tmp2 ) as HS
			,( select count(tmp3.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=3 GROUP BY tab.id) as tmp3 ) as Maintenance
			,( select count(tmp4.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=4 GROUP BY tab.id) as tmp4 ) as Stock_usage
			,( select count(tmp5.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=5 GROUP BY tab.id) as tmp5 ) as Stock_Neuf
			,( select count(tmp6.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  and states_id=6 GROUP BY tab.id) as tmp6 ) as Usage01
			FROM (SELECT id, MAX(date_mod) as max_date from `$table_name` tab WHERE tab.`is_deleted`=0  GROUP BY id )  tab1";
			if($num_tab==0){
				$rqt=$query01;
			} else {	
				$rqt=$rqt." UNION ".$query01;
			};
		};	
	};
	$num_tab++;
};
// echo $rqt."<br>";
$tab_rqt = $DB->query($rqt);
$numrows = $DB->numRows($tab_rqt);
if ($numrows>0) {
	echo "<form name='form1' method='POST'>";
	echo "<center><table border=2  width=60%>";
	echo "<caption><b>Situation des équipenments </b></caption>";
	echo "<thead><tr><th>Objet</th><th>Déployé</th><th>HS</th><th>En Maintenance</th><th width=50>Stock usagé</th><th>Stock neuf</th><th>Usagé</th><th>Total </th></tr></thead>";
	echo"<tbody>";
	while ($tab = $DB->fetch_assoc($tab_rqt)) {
		$Objet = $tab['Objet'];
		$id_Objet = $tab['id_Objet'];
		$Deploye = $tab['Deploye'];
		$HS = $tab['HS'];
		$Maintenance = $tab['Maintenance'];
		$Stock_usage = $tab['Stock_usage'];
		$Stock_Neuf = $tab['Stock_Neuf'];
		$Usage01 = $tab['Usage01'];
		$Total = $tab['Total'];
		
		echo "<tr><th>$Objet</th>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=1'>$Deploye</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=2'>$HS</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=3'>$Maintenance</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=4'>$Stock_usage</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=5'>$Stock_Neuf</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=6'>$Usage01</a></td>";
		echo "<td align='right'><a target='_BLANK' title='voir détail' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?id_Objet=".$id_Objet."&etat=*'>$Total</a></td>";
		
	};
	echo"</tbody>";
	echo "</table></center>";
	echo "</form>";
};
	
$details=1;
if ($details<>1){
	$report->setSqlRequest();
	$report->execute();
};
#echo "$rqt<br>";

Html::footer();

