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
$report=new PluginReportsAutoReport("Rapport détaillé des interventions IT");


//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));
#new PluginReportsTicketStatusCriteria($report);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(
	   #new PluginReportsColumn('id', __('ID Groupe'))
	   #, new PluginReportsColumn('name', __('Groupe'))
	    new PluginReportsColumn('realname', __('Nom'))
	   , new PluginReportsColumn('firstname', __('Prenoms'))
	   , new PluginReportsColumn('date', __('Date ouverture'))
	   #, new PluginReportsColumn('closedate', __('Date fermeture'))
	   #, new PluginReportsColumn('solvedate', __('Date resolution'))
	   , new PluginReportsColumn('catégorie', __('Catégorie'))
	   , new PluginReportsColumn('type', __('Type'))
           , new PluginReportsColumnInteger('waiting_duration', __('Delai d attente'))
	   , new PluginReportsColumnInteger('close_delay_stat', __('Delai de fermeture'))
	   , new PluginReportsColumn('solve_delay_stat', __('Delai de resolution'))
	   , new PluginReportsColumn('takeintoaccount_delay_stat', __('Delai de prise en charge'))
	   , new PluginReportsColumn('actiontime', __('Duree action'))
	   
   ));

  $query = "select DISTINCT `glpi_groups`.`id`, `glpi_groups`.`name`
	,`glpi_users`.`realname`, `glpi_users`.`firstname`, DATE(`glpi_tickets`.`date`) AS date
	,`glpi_tickets`.`closedate`, `glpi_tickets`.`solvedate`,`glpi_itilcategories`.`name` as 'Catégorie'
	,CASE `glpi_tickets`.`type`
		WHEN 1 THEN 'Incident'
		ELSE 'Demande'
	END as 'Type'
	,`glpi_tickets`.`waiting_duration` as `temps_attente`
	,`glpi_tickets`.`close_delay_stat` as `temps_cloture`
	,`glpi_tickets`.`solve_delay_stat` as `temps_resolution`
	,`glpi_tickets`.`takeintoaccount_delay_stat` as `temps_prise_en_compte`
	,`glpi_tickets`.`actiontime` as 'actiontime'
	FROM  `glpi_groups`, `glpi_groups_users`, `glpi_users`, `glpi_tickets_users`, `glpi_tickets`,`glpi_itilcategories`
	WHERE `glpi_groups`.`name` = 'Infrastructure'
	and `glpi_groups`.`id` = `glpi_groups_users`.`groups_id`
	and `glpi_groups_users`.`users_id` = `glpi_users`.`id`
	and `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
	and `glpi_tickets`.`itilcategories_id` = `glpi_itilcategories`.`id` 
	AND NOT `glpi_tickets`.`is_deleted` "
	
	
	$query = "SELECT DISTINCT `glpi_users`.`realname`, `glpi_users`.`firstname`,`glpi_tickets`.`id` as 'ticketID'
	, `glpi_tickets`.`date`, `glpi_tickets`.`solvedate`
	, `glpi_tickets`.`close_delay_stat`, `glpi_tickets`.`solve_delay_stat`, `glpi_tickets`.`takeintoaccount_delay_stat`, `glpi_tickets`.`actiontime`
	 
	FROM `glpi_tickets` 
        left outer join  `glpi_tickets_users` on  `glpi_tickets`.`id` = `glpi_tickets_users`.`tickets_id`
        left  outer join  `glpi_groups_users` on  `glpi_groups_users`.`users_id` = `glpi_tickets_users`.`users_id`
        left  outer join  `glpi_users` on  `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
	
	WHERE `glpi_groups_users`.`groups_id` in (SELECT `glpi_groups`.`id` FROM `glpi_groups` WHERE `glpi_groups`.`name` ='Infrastructure')
	AND NOT `glpi_tickets`.`is_deleted` ".
	
	$report->addSqlCriteriasRestriction() .
	$dbu->getEntitiesRestrictRequest(' AND ', 'glpi_tickets');
	$report->getOrderBy('glpi_tickets_users.users_id');

   $report->setSqlRequest($query);
   $report->execute();

} else {
   Html::footer();
}
