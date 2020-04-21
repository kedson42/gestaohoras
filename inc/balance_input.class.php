<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginGestaohorasBalance_Input extends CommonDBTM
{
   static $rightname = 'entity';

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return Session::haveRight("entity", UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canDelete() {
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Item name
    *
    * @param int $nb
    * @return string
    */
   public static function getTypeName($nb = 1)
   {
      return 'Lançamentos Avulsos';
   }

   /**
    * Return tabs
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      /*switch ($item->getType()) {
         case "PluginGestaohorasBalance_Hour":
            $nb        = count($this->getHistorys($item->getID()));
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }*/
      return self::createTabEntry(self::getTypeName(false), false);
      //return '';
   }

   /**
    * Return Balance History
    *
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|void
    * @throws Exception
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 2, $withtemplate = 0)
   {
      echo '<form method="post">';
      echo '<input type="hidden" name="id" value="'.$item->fields['id'].'">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="headerRow"><th colspan="2" class="">Lançamentos Avulsos</th><th colspan="2" class=""></th></tr>';
      
      echo '<tr class="tab_bg_2">';
      echo '<td width="20%">Tipo <span class="red">*</span></td>';
      echo '<td width="80%">
      <select name="tipos_id" required id="dropdown_tipos_id" tabindex="-1" class="select2"><option value="C" selected="selected">Crédito</option><option value="D" selected="selected">Débito</option></select>
      </td>';
      echo '</tr>';
      
      echo '<tr class="tab_bg_2">';
      echo '<td width="20%">Valor <span class="red">*</span></td>';
      echo '<td width="80%"><input required oninput="this.value=this.value.replace(/[^0-9]/g,\'\');"  type="text" name="valor" value="" size="35"/></td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%">Justificativa</td>';
      echo '<td width="80%"><input type="text" name="justificativa" value="" size="35"/></td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2"><td class="center" colspan="4">
      <input type="submit" value="Salvar" name="avulso" class="submit"></td></tr>';

      echo '</table>';
      Html::closeForm();
      
   }

   /**
    * @param $id
    * @param $quantity
    * @param $justification
    * @return bool|mysqli_result
    */
    public function balanceInputAdd($post)
    {
       // History object
       $data = array(
          'type' => $post['tipos_id'],
          'quantity' => $post['valor'],
          'date_operation' => date('Y-m-d H:i:s'),
          'plugin_gestaohoras_balances_hours_id' => $post['id'],
          'users_id' => Session::getLoginUserID(),
          'tickets_id' => 0,
          'category' => 'Avulso',
          'justification' => $post['justificativa']
       );
       $history = new PluginGestaohorasBalance_History();
       $history->newHistoryAvulso($data);
       return Html::back();
    }

}
