<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginGestaohorasBalance_Hour extends CommonDBTM
{
   static $rightname = 'entity';

   const ACCESS_PUBLIC = 0;
   const ACCESS_PRIVATE = 1;
   const ACCESS_RESTRICTED = 2;

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
    * Returns the type name with consideration of plural
    *
    * @param int $nb
    * @return string
    */
   public static function getTypeName($nb = 0)
   {
      return 'Gestão de Horas';
   }

   /**
    * Search form
    *
    * @return array
    */
   public function getSearchOptionsNew()
   {
      return $this->rawSearchOptions();
   }

   /**
    * @return array
    */
   public function rawSearchOptions()
   {
      //2019-01-20 10:56:00
      $tab = [];

      $tab[] = [
         'id' => 'common',
         'name' => __('Characteristics'),
         'massiveaction' => false
      ];

      $tab[] = [
         'id' => '4',
         'table' => $this->getTable(),
         'field' => 'id',
         'name' => 'ID',
         'searchtype' => 'contains',
         'massiveaction' => false
      ];

      $tab[] = [
         'id' => '1',
         'table' => 'glpi_groups',
         'field' => 'name',
         'name' => 'Grupo de Usuário',
         'datatype' => 'dropdown',
         'massiveaction' => false
      ];

      $tab[] = [
         'id' => '2',
         'table' => $this->getTable(),
         'field' => 'total',
         'name' => __('Saldo Total'),
         'datatype' => 'itemlink',
         'massiveaction' => false
      ];

      $tab[] = [
         'id' => '3',
         'table' => $this->getTable(),
         'field' => 'daily',
         'name' => __('Limite Diário'),
         'datatype' => 'itemlink',
         'massiveaction' => false
      ];

      return $tab;
   }

   /**
    * Balance Hours Form
    *
    * @param $ID
    * @param array $options
    */
   public function showForm($ID, $options = [])
   {
      global $DB;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td>Grupo de Usuário<span class="red">*</span></td>';
      echo '<td>';
      Group::dropdown([
         'name' => 'groups_id',
         'value' => ($ID != 0) ? $this->fields["groups_id"] : 0
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td width="20%">Saldo Padrão <span class="red">*</span></td>';
      echo '<td width="80%"><input type="text" name="default" value="' . $this->fields["default"] . '" size="35"/></td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%">Limite Diário <span class="red">*</span></td>';
      echo '<td width="80%"><input type="text" name="daily" value="' . $this->fields["daily"] . '" size="35"/></td>';
      echo '</tr>';

      if ($ID) {
         echo '<tr class="tab_bg_1">';
         echo '<td width="20%">Total</td>';
         echo '<td width="80%"><b>'.$this->fields["total"].'</b></td>';
         echo '</tr>';
      }

      $this->showFormButtons($options);
   }

   public function defineTabs($options = [])
   {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginGestaohorasBalance_Input', $ong, $options);
      $this->addStandardTab('PluginGestaohorasBalance_History', $ong, $options);
      return $ong;
   }

   /**
    * Balanced group exists
    *
    * @param $groupId
    * @return boolean
    */
   public function groupExistis($groupId)
   {
      $group = $this->find("groups_id = '{$groupId}'");
      return empty($group) ? false : true;
   }

   /**
    * @param $id
    * @param $quantity
    * @return bool|mysqli_result
    */
   public function balanceHistoryAdd($id, $quantity)
   {
      // History object
      $data = array(
         'type' => 'C',
         'quantity' => $quantity,
         'date' => date('Y-m-d H:i:s'),
	 'ticket_id' => 0,
         'balance_id' => $id,
         'user_id' => Session::getLoginUserID(),
	 'category' => 'Job',

      );

      $history = new PluginGestaohorasBalance_History();

      return $history->newHistory($data);
   }

   /**
    * @param $id
    * @param $newBalance
    */
   public function balanceUpdate($id, $newBalance)
   {

      $balance = current($this->find("id = '{$id}'"));

      // History object
      $data = array(
         'date' => date('Y-m-d H:i:s'),
         'balance_id' => $id,
         'user_id' => Session::getLoginUserID()
      );
      $history = new PluginGestaohorasBalance_History();

      // Débito
      if ($newBalance < $balance['total']) {
         $value = $balance['total'] - $newBalance;
         $data['type'] = 'D';
         $data['quantity'] = $value;
         $history->newHistory($data);
      }

      // Crédito
      if ($newBalance > $balance['total']) {
         $value = $newBalance - $balance['total'];
         $data['type'] = 'C';
         $data['quantity'] = $value;
         $history->newHistory($data);
      }
   }


   /**
    * @param $groupId
    * @return mixed
    */
   protected function getTotalTicketsByGroupIdPerDate($groupId)
   {
      global $DB;

      $dt_init = date('Y-m-d');

      $historys = $DB->query("SELECT COUNT(t.id) AS total FROM glpi_tickets t
                                    INNER JOIN glpi_users u ON t.users_id_recipient = u.id
                                    INNER JOIN glpi_groups_users g ON u.id = g.users_id AND g.groups_id = {$groupId}
				    INNER JOIN glpi_plugin_gestaohoras_itilcategorycategorias fic on fic.items_id = t.itilcategories_id
                                    WHERE t.date_creation between '{$dt_init} 00:00:00' AND NOW() AND fic.limitefield=1");

      $result = $historys->fetch_array();

      return $result['total'];
   }

   /**
    * get Cron description parameter for this class
    *
    * @param $name string name of the task
    *
    * @return array of string
    */
   static function cronInfo($name)
   {
      switch ($name) {
         case 'DebitoDeHoras':
            return ['description' => 'Efetua os débitos de horas dos grupos requerentes'];
      }
   }

   /**
    * @param CronTask $task
    *
    * @return number
    */
   public static function cronDebitoDeHoras(CronTask $task)
   {
      global $DB;

      $task->log("Rotina para gerar débitos dos grupos iniciada.");

      $data = date('Y-m') . '-01 00:00:00';

      $query = $DB->query(" SELECT 
	                   b_hr.id as balance_id,
			   b_hr.default,
			   t.id as ticket_id,
			   t.date_creation,
			   b_hr.groups_id,
			   'Ticket' as category,
			   ROUND((SUM(tt.actiontime))/3600,2) AS total
                   FROM glpi_tickettasks tt
                       INNER JOIN glpi_tickets t ON tt.tickets_id=t.id
                       INNER JOIN glpi_groups_tickets gt ON gt.tickets_id=t.id AND gt.type=1
	 	       INNER JOIN glpi_plugin_gestaohoras_balances_hours b_hr ON gt.groups_id=b_hr.groups_id
                   WHERE 
                     t.status=6
                     AND t.is_deleted=0 
                     AND tt.actiontime > 0
				AND t.date_creation between '{$data}' AND NOW()
	         GROUP BY t.id
         
              UNION
         
		   SELECT
		           b_hr.id,
		           b_hr.default,
		           0 as ticket_id,
		           date_operation,
		           b_hr.groups_id,
			  'Avulso' as category,
		           CASE WHEN b_hist.type='C' THEN -b_hist.quantity ELSE b_hist.quantity end as total
	         FROM glpi_plugin_gestaohoras_balances_historys b_hist
	           INNER JOIN glpi_plugin_gestaohoras_balances_hours b_hr ON b_hist.plugin_gestaohoras_balances_hours_id=b_hr.id
	         WHERE category='Avulso' AND date_operation between '{$data}' AND NOW()

								");

      $ticket = [];

      if ($query->num_rows) {
         while ($row = $query->fetch_assoc()) {
            $ticket[$row['balance_id']]['total'] += $row['total'];
            $ticket[$row['balance_id']]['default'] = $row['default'];
            self::updateBalanceHistoryGroupTicket($row['balance_id'], $row['groups_id'], $row['ticket_id'], $row['user_id'], $row['total'], $row['category']);
         }

         foreach ($ticket as $key => $values) {
            $total = ($values['default'] - $values['total']);
            self::updateBalanceGroupTicket($key, $total);
         }
      }
      return 1;
   }

   /**
    * Atualiza o saldo do grupo
    *
    * @param $balanceId
    * @param $total
    */
   public static function updateBalanceGroupTicket($balanceId, $total)
   {
      global $DB;
      $DB->update('glpi_plugin_gestaohoras_balances_hours', ['total' =>  $total], ['id' => $balanceId]);
   }

   /**
    * Adiciona/Atualiza o histórico
    *
    * @param $balanceId
    * @param $groupId
    * @param $ticketId
    * @param $userId
    * @param $total
    */
   public static function updateBalanceHistoryGroupTicket($balanceId, $groupId, $ticketId, $userId, $total, $category)
   {
      global $DB;

      $query = $DB->query("SELECT h.id
                                  FROM glpi_plugin_gestaohoras_balances_historys h
                                  INNER JOIN glpi_plugin_gestaohoras_balances_hours b ON h.plugin_gestaohoras_balances_hours_id = b.id
                                  WHERE category='Ticket' AND groups_id = {$groupId} AND tickets_id = {$ticketId}");

      $history = $query->fetch_assoc();

      // Atualiza débito no histórico
      if ($query->num_rows) {

         $DB->update('glpi_plugin_gestaohoras_balances_historys', ['quantity' => $total], ['id' => $history['id']]);

      }	else if ($category=='Ticket') {

         // Adiciona novo débito no histórico nas aberturas de chamado
         $newHistory = new PluginGestaohorasBalance_History();

         $newHistory->newHistory([
            'type' => 'D',
            'quantity' => $total,
            'date' => date('Y-m-d H:i:s'),
            'balance_id' => $balanceId,
            'ticket_id' => $ticketId,
            'user_id' => $userId,
	    'category' => $category,
         ]);

      }
   }

   /**
    * Retorna o saldo total e o limite restante do grupo para o usuário logado.
    *
    * @return mixed
    */
   public static function getSaldos()
   {
      // Saldo total
      $groupId = current($_SESSION['glpigroups']);
      $balance = new PluginGestaohorasBalance_Hour();
      $balance = current($balance->find("groups_id = '{$groupId}'"));

      if (!$balance) {
         return false;
      }

      // Limite Restante
      $self = new PluginGestaohorasBalance_Hour();
      $limite = $self->getTotalTicketsByGroupIdPerDate($balance['groups_id']);
      $limiteTotal = $balance['daily'] - $limite;

      return [
         'total' => $balance['total'],
         'limite' => $limiteTotal
      ];
   }

}
