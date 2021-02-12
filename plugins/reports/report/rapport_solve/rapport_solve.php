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
include_once ('rapport_solve.fr_FR.php');

$dbu = new DbUtils();

//TRANS:Average time to resolve tickets = Moyen de temps de résolution des tickets
$report = new PluginReportsAutoReport($LANG['plugin_reports']['rapport_solve']);
#$report=new PluginReportsAutoReport("Moyen de temps de résolution des tickets par  type et par catégorie");

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(
	    new PluginReportsColumn('Type', __('Type'))
	   , new PluginReportsColumn('Catégorie', __('Catégorie'))
	   , new PluginReportsColumn('Temps_resol_moyen', __('T moyen'))
   ));
           $query = "select CASE glpi_tickets.type
                                        WHEN 1 THEN 'Incident'
                                        ELSE 'Demande'
			END as 'Type'
			, `glpi_itilcategories`.`name` as 'Catégorie'	
			, ROUND(AVG(glpi_tickets.solve_delay_stat /3600) ,2) as 'Temps_resol_moyen'
			FROM `glpi_tickets`,`glpi_itilcategories`
			WHERE `glpi_tickets`.`itilcategories_id` = `glpi_itilcategories`.`id` 
			AND glpi_tickets.solve_delay_stat > 0
  	                AND glpi_tickets.is_deleted = 0 ".
			$report->addSqlCriteriasRestriction() .
			$dbu->getEntitiesRestrictRequest(' AND ', 'glpi_tickets').
                        " GROUP BY glpi_tickets.type, glpi_itilcategories.name";

   $report->setSqlRequest($query);
   $report->execute();

} else {
   Html::footer();
}
