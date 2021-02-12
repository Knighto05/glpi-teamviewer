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

$dbu = new DbUtils();

//TRANS: The name of the report = Tickets no closed, sorted by priority
#$report = new PluginReportsAutoReport(__('statticketsbypriority_report_title', 'reports'));
$report=new PluginReportsAutoReport("Rapport intervention");


//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));
#new PluginReportsDropdownCriteria($report, 'name', 'glpi_groups', __('Groupe'));
#new PluginReportsTicketStatusCriteria($report);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(new PluginReportsColumn('id', __('ID Groupe'))
	   , new PluginReportsColumn('name', __('Groupe'))
#	   , new PluginReportsColumn('users_id', __('ID User'))
	   , new PluginReportsColumn('realname', __('Nom'))
	   , new PluginReportsColumn('firstname', __('Prenoms'))
	   , new PluginReportsColumn('date', __('Date'))
	   , new PluginReportsColumn('closedate', __('Date fermeture'))
           , new PluginReportsColumn('solvedate', __('Date resolution'))
	   , new PluginReportsColumnFloat('waiting_duration', __('Delai d attente'))
	   , new PluginReportsColumnFloat('close_delay_stat', __('Delai de fermeture'))
	   , new PluginReportsColumnFloat('solve_delay_stat', __('Delai de resolution'))
	   , new PluginReportsColumnFloat('takeintoaccount_delay_stat', __('Delai de prise en charge'))
	   , new PluginReportsColumnFloat('actiontime', __('Duree action'))
	   , new PluginReportsColumn('Type', __('Type'))
	   , new PluginReportsColumn('Catégorie', __('Catégorie'))
   ));

								  
	    $query = "select DISTINCTROW `glpi_groups`.`id`, `glpi_groups`.`name`,
			`glpi_users`.`realname`, `glpi_users`.`firstname`, DATE(`glpi_tickets`.`date`) AS date, 
			`glpi_tickets`.`closedate`, `glpi_tickets`.`solvedate`,
			CONVERT(`glpi_tickets`.`waiting_duration`, UNSIGNED) /3600  as `temps_attente`,
			CONVERT(`glpi_tickets`.`close_delay_stat`, UNSIGNED) /3600 as `temps_cloture`,
			CONVERT(`glpi_tickets`.`solve_delay_stat`, UNSIGNED) /3600 as `temps_resolution`,
			CONVERT(`glpi_tickets`.`takeintoaccount_delay_stat`, UNSIGNED) /3600 as `temps_prise_en_compte`,
			CONVERT(`glpi_tickets`.`actiontime`, UNSIGNED) /3600 as 'actiontime',
			CASE `glpi_tickets`.`type` 
					WHEN 1 THEN 'Incident' 
					ELSE 'Demande' 
			END as 'Type',
			`glpi_itilcategories`.`name` as 'Catégorie'
			from `glpi_groups`, `glpi_groups_users`, `glpi_users`, `glpi_tickets_users`, `glpi_tickets`,`glpi_itilcategories`
			where `glpi_groups`.`name` = 'Infrastructure'
			and `glpi_groups`.`id` = `glpi_groups_users`.`groups_id`
			and `glpi_groups_users`.`users_id` = `glpi_users`.`id`
			and `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
			and `glpi_tickets`.`itilcategories_id` = `glpi_itilcategories`.`id` 
  
                  AND NOT `glpi_tickets`.`is_deleted` ".
                  $report->addSqlCriteriasRestriction() .
                  $dbu->getEntitiesRestrictRequest(' AND ', 'glpi_tickets');
             	  #$report->getOrderBy('date');

   $report->setSqlRequest($query);
   $report->execute();

} else {
   Html::footer();
}
