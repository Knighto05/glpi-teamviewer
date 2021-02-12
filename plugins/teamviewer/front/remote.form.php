<?php 

define('GLPI_ROOT', '../../..');
include(GLPI_ROOT . '/inc/includes.php');

if ($_POST && isset($_POST['teamviewer_id']) && isset($_POST['id'])) {
    global $DB;
    $DB->queryOrDie('UPDATE glpi_computers SET teamviewer_id = '.$_POST['teamviewer_id'].' WHERE id = '. $_POST['id'], $DB->error());
    Html::redirect($_SERVER['HTTP_REFERER']);
}