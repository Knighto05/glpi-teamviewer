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
$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_test']);
 

$details=1;


$report->setSubNameAuto();

//Names of the columns to be displayed
$cols = array();
$report->setColumns($cols);

# création d'un formulaire
echo "<form name='form1' method='POST'>";
echo "<center><table border=2  width=60%>";
echo "<caption><b> Tableau de bord des objets du ".date("m/d/y")."  </b></caption>";
echo "<thead><tr><th width=150>Nom</th><th width=50>Déployé</th><th width=50>HS</th><th width=50>En Maintenance</th><th width=50> En Stock</th><th width=50>Neuf</th><th width=50>Usagé</th><th width=50>Total</th></tr></thead>";
echo"<tbody>";

# requete principale		   
$query = "SELECT table_schema, table_name, table_comment 
FROM `information_schema`.`TABLES`
WHERE `TABLE_SCHEMA` = 'glpi' AND `TABLE_TYPE` = 'BASE TABLE' AND `TABLE_NAME` LIKE '%glpi_plugin_genericobject%'
AND table_comment LIKE '%PluginGenericobject%'";
$reponse = $DB->query($query);

 while ($data = $DB->fetch_assoc($reponse)) {
	$data = $DB->fetch_assoc($reponse);
	$table_schema=$data['table_schema'];
	$table_name=$data['table_name'];
	$table_comment=$data['table_comment'];
	
	$query00 = "SELECT name FROM `glpi_plugin_genericobject_types` where itemtype='".$table_comment."'";
	$tbl_name = $DB->query($query00);
	$numrows = $DB->numrows($tbl_name);
	if ($numrows>0) { 
		$data00 = $DB->fetch_assoc($tbl_name);
		$name = $data00['name'];

		$query01 = "SELECT  count(id) as total
		,( select count(tmp1.id) from ( SELECT id,  max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=1 GROUP BY tab.id) as tmp1 ) as Deploye
		,( select count(tmp2.id) from ( SELECT id,  max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=2 GROUP BY tab.id) as tmp2 ) as HS
		,( select count(tmp3.id) from ( SELECT id,  max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=3 GROUP BY tab.id) as tmp3 ) as Maintenance
		,( select count(tmp4.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=4 GROUP BY tab.id) as tmp4 ) as Stock
		,( select count(tmp5.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=5 GROUP BY tab.id) as tmp5 ) as Neuf
		,( select count(tmp6.id) from ( SELECT id, max(date_mod) as max_date from `$table_name` tab WHERE is_deleted=0 and states_id=6 GROUP BY tab.id) as tmp6 ) as Usage01
		FROM (SELECT id, MAX(date_mod) as max_date from `$table_name` WHERE is_deleted=0  GROUP BY id )  tab1";
		
		$reponse01 = $DB->query($query01);
		echo "$query01<br>"	;

		while ($data01 = $DB->fetch_assoc($reponse01)) {
				echo "<tr><th><a target='_BLANK' href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/rapport_test/rapport_test2.php?name=".$name."&type='Total'>".$data00['name']."</a></th><td>".$data01['Deploye']."</td><td>".$data01['HS']."</td><td>".$data01['Maintenance']."</td><td>".$data01['Stock']."</td><td>".$data01['Neuf']."</td><td>".$data01['Usage01']."</td><td>".$data01['total']."</td>";
				echo"</tr>";
		};
	};
	
};

echo"</tbody>";
echo "<tfoot></tfoot>";
echo "</table></center>";
echo "</form>";	

$details=0;
if ($details<>1){
		"select id from ticket where 1=0";
		$report->setSqlRequest($query);
		$report->execute(array('withtotal' => true));
};

Html::footer();

