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
    , new PluginReportsColumnInteger('NB_OK', __('nb atteint', 'reports'), array('withtotal' => true, 'sorton' => 'NB_OK'))
	, new PluginReportsColumn('PRC_OK', __('% atteint', 'reports'))
	, new PluginReportsColumn('Moyen_T_OK', __('Tps résol. moyen atteint(h)', 'reports'))
	, new PluginReportsColumnInteger('NB_KO', __('nb dépassé', 'reports'), array('withtotal' => true, 'sorton' => 'NB_KO'))
	, new PluginReportsColumn('PRC_KO', __('% dépassé', 'reports'))
	, new PluginReportsColumn('Moyen_T_KO', __('Tps résol. moyen dépassé(h)', 'reports'))
	, new PluginReportsColumnInteger('NB_INCONNU', __('Nb status Inconnu', 'reports'), array('withtotal' => true, 'sorton' => 'NB_INCONNU'))
	, new PluginReportsColumnInteger('Total', __('Total', 'reports'), array('withtotal' => true, 'sorton' => 'Total'))
	);
    $report->setColumns($cols);
 
# requete principale		   
	$query = "SELECT priority
,CASE WHEN priority = 1 then 'très basse' 
	WHEN priority = 2 then 'basse' 
	WHEN priority = 3 then 'moyenne' 
	WHEN priority = 4 then 'haute' 
	WHEN priority = 5 then 'très haute' 
	WHEN priority = 6 THEN 'majeure' 
END AS priorite 
,Rq2.NB_OK, IF(Rq2.Total>0,ROUND((Rq2.NB_OK*100)/Rq2.Total,2),0) as PRC_OK ,IF(Rq2.Total>0,ROUND((Rq2.SUM_Diff_OK)/Rq2.Total,2),0) as Moyen_T_OK
,Rq2.NB_KO, IF(Rq2.Total>0,ROUND((Rq2.NB_KO*100)/Rq2.Total,2),0) as PRC_KO ,IF(Rq2.Total>0,ROUND((Rq2.SUM_Diff_KO)/Rq2.Total,2),0) as Moyen_T_KO
,NB_INCONNU
,Rq2.Total
FROM (
SELECT priority
, SUM(CASE WHEN Statut='OK'  THEN 1 ELSE 0 END ) as NB_OK
, SUM(CASE WHEN Statut='OK'  THEN solve_delay_stat/3600 ELSE 0 END ) as SUM_OK
, SUM(CASE WHEN Statut='OK'  THEN diff/3600 ELSE 0 END ) as SUM_Diff_OK
, SUM(CASE WHEN Statut='KO'  THEN 1 ELSE 0 END ) as NB_KO
, SUM(CASE WHEN Statut='KO'  THEN solve_delay_stat/3600 ELSE 0 END ) as SUM_KO
, SUM(CASE WHEN Statut='KO'  THEN diff/3600 ELSE 0 END ) as SUM_Diff_KO
, SUM(CASE WHEN Statut='INCONNU'  THEN 1 ELSE 0 END ) as NB_INCONNU
, count(*) as Total
FROM (
SELECT  tik.id, tik.priority
, tik.solve_delay_stat
, CASE 
   WHEN tik.time_to_resolve > tik.solvedate THEN 'OK'
   WHEN tik.time_to_resolve < tik.solvedate THEN 'KO'
   WHEN tik.time_to_resolve = tik.solvedate THEN 'IN TIME'
   ELSE 'INCONNU'
   END  as Statut 
,CASE   WHEN sla.definition_time = 'second' then tik.solve_delay_stat - sla.number_time 
		WHEN sla.definition_time = 'minute' then tik.solve_delay_stat - (sla.number_time * 60) 
		WHEN sla.definition_time = 'hour' then tik.solve_delay_stat - (sla.number_time * 3600)  
		WHEN sla.definition_time = 'day' then tik.solve_delay_stat - (sla.number_time * 3600 *24)  
		WHEN sla.definition_time = 'week' then tik.solve_delay_stat - (sla.number_time * 3600 * 24 * 7)  
		WHEN sla.definition_time = 'month' then tik.solve_delay_stat - (sla.number_time * 3600 * 24 * 7 * 30) 
	END AS diff
FROM glpi_tickets tik
left outer join `glpi_slas` sla ON sla.`id`= tik.`slas_id_ttr`
WHERE tik.type=1
and tik.is_deleted = '0' 
and tik.date between '" .$date01->getStartDate() . " 00:00:00' and '" .$date01->getEndDate() . " 23:59:59') as Rq1";
$query .= " group by priority desc) as Rq2";
	
	$report->setSqlRequest($query);
	$report->execute(array('withtotal' => true));
} else {
   Html::footer();
}

