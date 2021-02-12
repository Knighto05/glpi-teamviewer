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

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0; // not really a big SQL request

include ("../../../../inc/includes.php");
#include_once ('rapport_act01.fr_FR.php');

$dbu = new DbUtils();


//TRANS: The name of the report = Search in the financial information (plural)
$report = new PluginReportsAutoReport(__('rapport_act01_report_title', 'reports'));
#$report = new PluginReportsAutoReport(__('searchinfocom_report_title', 'reports'));
#$report = new PluginReportsAutoReport('Activités par techniciens');


//Report's search criterias
$date01 = new PluginReportsDateIntervalCriteria($report, '`glpi_plugin_activity_activities`.`begin`', __('Opening date'));

$tab = array(0 => __('Jour'),1 => __('Semaine'),2 => __('Mois'),3 => __('Année'));
$filter = new PluginReportsArrayCriteria($report, 'Période', 'Période', $tab);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
	$report->setSubNameAuto();
	
   $Periode="";
   $masque="'%Y-%m-%d'";
   switch ($filter->getParameterValue()) {
	case 0:
		$masque="'%Y-%m-%d'";
		$Periode="Jour";
	break;
	case 1:
		$masque="'%Y-%u'";
		$Periode="Semaine";
	break;
	case 2:
		$masque="'%Y-%m'";
		$Periode="Mois";
	break;
	case 3:
		$masque="'%Y'";
		$Periode="Année";
	break;
   };
   
   //Names of the columns to be displayed
   $cols = array( new PluginReportsColumnLink('Nom', 'Nom','Nom')
    , new PluginReportsColumn('XDate', $Periode)
	, new PluginReportsColumnLink('Activite', 'Activité','Activité')
	, new PluginReportsColumn('Tps', 'Temps')
	);
	$report->setColumns($cols); 
	
	 //requete principale
	 $query = "SELECT  act.users_id as ID, gu.firstname as Nom, DATE_FORMAT(act.begin,$masque) as XDate, gaa.name as Activite, sum(round(TIMESTAMPDIFF(second, act.begin,act.end)/3600,2)) as Tps
	FROM `glpi_planningexternalevents`  act, `glpi_users` gu, `glpi_planningeventcategories` gaa
	WHERE act.users_id=gu.id
	AND (act.begin between '".$date01->getStartDate()." 00:00:00' and '" .$date01->getEndDate()." 23:59:59')
	AND  act.`planningeventcategories_id`= gaa.`id`
    AND act.users_id in (select users_id  FROM `glpi_groups_users`
	WHERE `glpi_groups_users`.`groups_id` in (SELECT id FROM `glpi_groups` where `glpi_groups`.name ='Techniciens'))
	GROUP BY users_id, XDate, gaa.name
	ORDER BY users_id, XDate, gaa.name";

	$report->setSqlRequest($query);
	$report->execute();
} else {
	Html::footer();
}
	