<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginGestaohorasBalance_History extends CommonDBTM
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
   public static function canCreate()
   {
      return Session::haveRight("entity", UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canDelete()
   {
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
      return 'Extrato';
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
      switch ($item->getType()) {
         case "PluginGestaohorasBalance_Hour":
            $nb        = count($this->getHistorys($item->getID()));
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }

      return '';
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
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      $inimes = strtotime('first day of this month');

      echo '<table class="tab_cadre_fixe history">';
      echo '<tr>';
      echo '<th colspan="6">Histórico de lançamentos (Extrato)</th>';
      echo '</tr>';
      echo '<tr>';
      echo '<th colspan="6"> Filtrar por período <input type="date" id="data-ini" value="' . date('Y-m-d', $inimes) . '"> a <input type="date" id="data-fim" value="' . date('Y-m-d') . '"></th>';
      echo '</tr>';
      echo '<tr style="font-weight: bold; background: #F1F1F1;">';
      echo '<td>Tipo</td>';
      echo '<td>Quantidade</td>';
      echo '<td>Data</td>';
      echo '<td>Ticket ID</td>';
      echo '<td>Categoria</td>';
      echo '<td>Justificativa</td>';
      echo '</tr>';

      $historys = self::getHistorys($item->getID());

      $i = 0;
      foreach ($historys as $history) {
         $i++;

         $history['type'] = $history['1'] == 'C' ? '<span style="color: darkgreen">Crédito</span>' : '<span style="color: darkred">Débito</span>';
         $data = new DateTime($history['3']);
         $history['ticket'] = $history['6'] ? $history['6'] : '-';
         $history['category'] = $history['7'] ? $history['7'] : '-';
         $history['justification'] = $history['8'] ? $history['8'] : '-';

         echo '<tr class="line' . ($i % 2) . '">';
         echo "<td><b>{$history['type']}</b></td>";
         echo "<td>{$history['2']}</td>";
         echo "<td data-date=" . $data->format('Y-m-d') . ">{$data->format('d/m/Y - H:i:s')}</td>";
         echo "<td>{$history['ticket']}</td>";
         echo "<td>{$history['category']}</td>";
         echo "<td>{$history['justification']}</td>";
         echo '</tr>';
      }

      echo "</table>";
      echo "<script>
      function selectRows(ini,fim){
         $('.tab_cadre_fixe.history tr').map((i,linha)=>{
            if (i>1) {
               col = $(linha).find('td').get(2);
               dia = strtodate($(col).data('date'));
               if (dia < ini || dia > fim) $(linha).hide();
               else $(linha).show();
            }
         });
         ncols = $('.tab_cadre_fixe tr:not(:hidden)').length;
         if (ncols==2) {
            $('.tab_cadre_fixe').append('<tr id=\"no-records\"><td colspan=4>Nenhum lançamento encontrado, altere os parâmetros da busca</td></tr>');
         } else $('#no-records').remove();
      }
      function strtodate(str){
         return Date.parse(str);
      }
      $(document).ready(function () {
         $('#data-ini, #data-fim').on('change',function(e){
            ini = strtodate($('#data-ini').get(0).value);
            fim = strtodate($('#data-fim').get(0).value);
            selectRows(ini,fim);
         });
         $('#data-ini').change();
      });
      </script>";
   }

   /**
    * @param $data
    * @return bool|mysqli_result
    */
   public function newHistory($data)
   {
      global $DB;

      $data['ticket_id'] = isset($data['ticket_id']) ? $data['ticket_id'] : 0;
      $data['category'] = $data['ticket_id'] > 0 ? 'Ticket' : 'Job';


      $history = $DB->insert(
         'glpi_plugin_gestaohoras_balances_historys',
         [
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'date_operation' => $data['date'],
            'plugin_gestaohoras_balances_hours_id' => $data['balance_id'],
            'users_id' => $data['user_id'],
            'tickets_id' => $data['ticket_id'],
            'category' => $data['category']
         ]
      );

      return $history;
   }

   /**
    * @param $data
    * @return bool|mysqli_result
    */
   public function newHistoryAvulso($data)
   {
      global $DB;

      $history =   $DB->insert(
         'glpi_plugin_gestaohoras_balances_historys',
         [
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'date_operation' => $data['date_operation'],
            'plugin_gestaohoras_balances_hours_id' => $data['plugin_gestaohoras_balances_hours_id'],
            'users_id' => $data['users_id'],
            'tickets_id' => $data['tickets_id'],
            'category' => $data['category'],
            'justification' => $data['justification']
         ]
      );

      return $history;
   }

   /**
    * List History
    *
    * @param $itemId
    * @return mixed
    */
   protected function getHistorys($itemId)
   {
      global $DB;

      $history = $DB->query("SELECT * FROM glpi_plugin_gestaohoras_balances_historys
                WHERE plugin_gestaohoras_balances_hours_id = {$itemId}");


      return $history->fetch_all();
   }
}
