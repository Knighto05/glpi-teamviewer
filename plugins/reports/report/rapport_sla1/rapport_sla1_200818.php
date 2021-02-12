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
include_once ('rapport_sla1.fr_FR.php');

$dbu = new DbUtils();

//TRANS:Average time to resolve tickets = Moyen de temps de résolution des tickets
$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_sla1']);

//Report's search criterias
$date01 = new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
    $cols = array( new PluginReportsColumn('priorite', __('PRIORITE'), array('sorton' => 'priorite'))
    , new PluginReportsColumnInteger('Tot_OK', __('nb atteint', 'reports'), array('withtotal' => true, 'sorton' => 'Tot_OK'))
	, new PluginReportsColumn('Prc_OK', __('% atteint', 'reports'))
	, new PluginReportsColumnInteger('Tot_KO', __('nb dépassé', 'reports'), array('withtotal' => true, 'sorton' => 'Tot_KO'))
	, new PluginReportsColumn('Prc_KO', __('% dépassé', 'reports'))
	, new PluginReportsColumnInteger('Total', __('Total', 'reports'), array('withtotal' => true, 'sorton' => 'Total'))
	);
    $report->setColumns($cols);
 
# requete principale		   
	$query = "SELECT X.priority
	, CASE WHEN X.priority = 1 then 'très basse' 
			WHEN X.priority = 2 then 'basse' 
			WHEN X.priority = 3 then 'moyenne' 
			WHEN X.priority = 4 then 'haute' 
			WHEN X.priority = 5 then 'très haute' 
			WHEN X.priority = 6 THEN 'majeure' 
		END AS priorite 
	, sum(X.OK) as Tot_OK, round(COALESCE(sum(X.OK)*100/(sum(X.OK)+sum(X.KO)),0),2) as Prc_OK , sum(X.KO) as Tot_KO
	, round(COALESCE(sum(X.KO)*100/(sum(X.OK)+sum(X.KO)),0),2) as Prc_KO, sum(X.OK)+sum(X.KO) as Total
	FROM  (SELECT t0.priority
	, COALESCE((select count(t2.id)
	FROM glpi_tickets t2
	WHERE  t0.id=t2.id
	and t2.solvedate is not null   
	and t2.time_to_resolve < t2.solvedate
	and t2.type=1 and t2.is_deleted = '0'
	GROUP by t2.priority
	 ),0) 	as OK
	 , COALESCE((select count(t1.id)
	FROM glpi_tickets t1
	WHERE  t0.id=t1.id 
	and t1.solvedate is not null   
	and t1.time_to_resolve >= t1.solvedate
	and t1.type=1 and t1.is_deleted = '0'
	GROUP by t1.priority
	 ),0) 	as KO
	FROM glpi_tickets t0
	WHERE  t0.is_deleted = '0'
	and  t0.date between '" .$date01->getStartDate() . " 00:00:00' and '" .$date01->getEndDate() . " 23:59:59'";
	$query .= " and t0.time_to_resolve is not null";
	$query .= " and t0.solvedate is not null";
	$query .= " and t0.type=1 ";
	$query .= " and t0.is_deleted = '0'";
	$query .= " ORDER by t0.priority) as X";
	$query .= " GROUP BY X.priority desc";

	$report->setSqlRequest($query);
	$report->execute(array('withtotal' => true));
} else {
   Html::footer();
}

