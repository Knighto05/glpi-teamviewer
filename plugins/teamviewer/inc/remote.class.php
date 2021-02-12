<?php 
class PluginTeamviewerRemote extends CommonGLPI 
{
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return self::createTabEntry('TeamViewer');
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate = 0) {
        ?>
        <h2>Définir l'id de l'ordinateur</h2>
        <form action="../plugins/teamviewer/front/remote.form.php" method="post">
            <?php echo Html::hidden('id', array('value' => $item->getID())); ?>
            <?php echo Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken())); ?>
            <div class="spaced" id="tabsbody">
                <table class="tab_cadre_fixe">
                    <tr class="tab_bg_1">
                        <td>
                            TeamViewer ID de la machine distante: &nbsp;&nbsp;&nbsp;
                            <input type="text" name="teamviewer_id" size="40" class="ui-autocomplete-input" autocomplete="off" value="<?php  echo $item->fields['teamviewer_id'] ?>"> &nbsp;&nbsp;&nbsp;
                            <input type="submit" class="submit" value="Enregistrer" name="enregistrer"/>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <h2>Se connecter au TeamViewer de cet ordinateur</h2>
        <p>
            <a target="_blank" href="https://start.teamviewer.com/device/<?php  echo $item->fields['teamviewer_id'] ?>/authorization/password/mode/control" class="vsubmit">Se connecter</a>
        </p>
        <?php
        return true;
    }
}
