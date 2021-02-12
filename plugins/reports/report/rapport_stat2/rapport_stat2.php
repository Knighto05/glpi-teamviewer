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
include_once ('rapport_stat2.fr_FR.php');

$dbu = new DbUtils();

$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_stat2']);

//Report's search criterias
//new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));

//Display criterias form is needed
//$report->displayCriteriasForm();

//If criterias have been validated
//if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(
	    new PluginReportsColumn('Catégorie', __('Catégorie'))
	   , new PluginReportsColumn('statut', __('Statut'))
	   , new PluginReportsColumnInteger('Week_3', __('Week-3'))
	   , new PluginReportsColumnInteger('Week_2', __('Week-2'))
	   , new PluginReportsColumnInteger('Week_1', __('Week-1'))
	   , new PluginReportsColumnInteger('Week_0', __('Week'))
	   , new PluginReportsColumnInteger('Total', __('Total'))
   ));
     
	 //requete principale
    $query = "SELECT Catégorie ,statut ,Week_3,Week_2 ,Week_1 ,Week_0 ,Total
	FROM 
	(SELECT CASE type 
		WHEN 1 THEN 'Incident' 
		ELSE 'Demande' 
		END as Catégorie 
	,T.Type,'nouveau' as statut
	,(SELECT count(T3.`id`)  FROM `glpi_tickets` AS T3 WHERE  T3.type=T.Type and  T3.`is_deleted` = '0' AND YEARWEEK(T3.`date`)= YEARWEEK(T.`date`)-3 )  AS Week_3
	,(SELECT count(T2.`id`)  FROM `glpi_tickets` AS T2 WHERE  T2.type=T.Type and  T2.`is_deleted` = '0' AND YEARWEEK(T2.`date`)= YEARWEEK(T.`date`)-2 )  AS Week_2
	,(SELECT count(T1.`id`)  FROM `glpi_tickets` AS T1 WHERE  T1.type=T.Type and  T1.`is_deleted` = '0' AND YEARWEEK(T1.`date`)= YEARWEEK(T.`date`)-1 )  AS Week_1 
	,(SELECT count(T0.`id`)  FROM `glpi_tickets` AS T0 WHERE  T0.type=T.Type and  T0.`is_deleted` = '0' AND YEARWEEK(T0.`date`)= YEARWEEK(T.`date`))  AS  Week_0 
	,(SELECT count(Tt.`id`)  FROM `glpi_tickets` AS Tt WHERE  Tt.type=T.Type and  Tt.`is_deleted` = '0' AND (YEARWEEK(Tt.`date`) between  ((YEARWEEK(T.`date`))-3) and (YEARWEEK(T.`date`))) ) AS Total 
	FROM `glpi_tickets` AS T 
	WHERE T.`is_deleted` = '0' 
	AND YEARWEEK(T.`date`) = YEARWEEK(CURDATE())
	GROUP BY  T.Type, YEARWEEK(T.`date`)

	UNION

	SELECT CASE type 
		WHEN 1 THEN 'Incident' 
		ELSE 'Demande' 
		END as Catégorie
	,T.Type,'résolu' as statut
	,(SELECT count(T3.`id`)  FROM `glpi_tickets` AS T3 WHERE  T3.type=T.Type and  T3.`is_deleted` = '0' AND YEARWEEK(T3.`solvedate`)= YEARWEEK(T.`solvedate`)-3 )  AS Week_3
	,(SELECT count(T2.`id`)  FROM `glpi_tickets` AS T2 WHERE  T2.type=T.Type and  T2.`is_deleted` = '0' AND YEARWEEK(T2.`solvedate`)= YEARWEEK(T.`solvedate`)-2 )  AS Week_2
	,(SELECT count(T1.`id`)  FROM `glpi_tickets` AS T1 WHERE  T1.type=T.Type and  T1.`is_deleted` = '0' AND YEARWEEK(T1.`solvedate`)= YEARWEEK(T.`solvedate`)-1 )  AS Week_1  
	,(SELECT count(T0.`id`)  FROM `glpi_tickets` AS T0 WHERE  T0.type=T.Type and  T0.`is_deleted` = '0' AND YEARWEEK(T0.`solvedate`)= YEARWEEK(T.`solvedate`))  AS Week_0 
	,(SELECT count(Tt.`id`)  FROM `glpi_tickets` AS Tt WHERE  Tt.type=T.Type and  Tt.`is_deleted` = '0' AND (YEARWEEK(Tt.`solvedate`) between  ((YEARWEEK(T.`solvedate`))-3) and (YEARWEEK(T.`solvedate`))) ) AS Total 
	FROM `glpi_tickets` AS T 
	WHERE T.`is_deleted` = '0' 
	AND  YEARWEEK(T.`solvedate`) = YEARWEEK(CURDATE())
	GROUP BY  T.Type, YEARWEEK(T.`solvedate`)) as RQ_01
	ORDER BY Type, statut";

   $report->setSqlRequest($query);
   $report->execute();
//} else {
   Html::footer();
//}
	