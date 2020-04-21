<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginGestaohorasJob extends CommonDBTM
{
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
         case 'RecarregarSaldos':
            return ['description' => 'Recarrega os saldos dos grupos de acordo com o saldo padrão. O saldo não é acumulativo.'];
      }
   }


   /**
    * JOB para recarregar saldos iniciais
    *
    * @param CronTask $task
    */
   public static function cronRecarregarSaldos(CronTask $task)
   {
      global $DB;

      $task->log("Rotina para recarregar saldos iniciada.");

      $balances = $DB->query("SELECT
                                           id,
                                           total,
                                           `default`
                                    FROM glpi_plugin_gestaohoras_balances_hours");

      while ($row = $balances->fetch_assoc()) {

            $DB->update(
               'glpi_plugin_gestaohoras_balances_hours',
               [
                  'total' => $row['default']
               ],
               ['id' => $row['id']]
            );

         $DB->insert(
            'glpi_plugin_gestaohoras_balances_historys',
            [
               'type' => 'C',
               'quantity' => $row['default'],
               'date_operation' => new \QueryExpression('NOW()'),
               'plugin_gestaohoras_balances_hours_id' => $row['id'],
               'category' => 'Job',
               'justification' => 'Recarga mensal de saldo'
            ]
         );
      }
   }
}
