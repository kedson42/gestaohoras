<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginGestaohorasInstall
{

   protected $migration;

   /**
    * Install the plugin
    * @param Migration $migration
    */
   public function install(Migration $migration)
   {
      $this->migration = $migration;
      $this->installSchema();
      $this->createDefaultDisplayPreferences();
      $this->createCronTasks();
   }

   /**
    * Uninstall plugin
    */
   public function uninstall()
   {
      $this->deleteTables();
   }

   /**
    * Create tables in database
    */
   protected function installSchema()
   {
      global $DB;

      $this->migration->displayMessage("create database schema");

      $dbFile = __DIR__ . '/mysql/plugin_gestaohoras_empty.sql';

      if (!$DB->runFile($dbFile)) {
         $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
         die('Giving up');
      }
   }

   /**
    * Cleanups the database from plugin's itemtypes (tables and relations)
    */
   protected function deleteTables()
   {
      global $DB;

      // Delete display preferences
      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(['itemtype' => 'PluginGestaohorasBalance_Hour']);

      // Drop tables
      $tables = [
         'glpi_plugin_gestaohoras_balances_historys',
         'glpi_plugin_gestaohoras_balances_hours',
         'glpi_plugin_gestaohoras_itilcategorycategorias',

      ];
      $DB->query("DROP TABLE IF EXISTS " . implode(',', $tables));
   }

   /**
    * Create display preferences
    */
   protected function createDefaultDisplayPreferences()
   {
      global $DB;

      $this->migration->displayMessage("create default display preferences");

      // Create standard display preferences
      $displayprefs = new DisplayPreference();
      $found_dprefs = $displayprefs->find("`itemtype` = 'PluginGestaohorasBalance_Hour'");

      if (count($found_dprefs) == 0) {

         $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginGestaohorasBalance_Hour', 2, 1, 0),
                   (NULL, 'PluginGestaohorasBalance_Hour', 3, 2, 0)";

         $DB->query($query) or die ($DB->error());
      }
   }

   /**
    * Create cron tasks
    */
   protected function createCronTasks() {

      // Debitar saldos
      CronTask::Register(PluginGestaohorasBalance_Hour::class, 'DebitoDeHoras', MINUTE_TIMESTAMP,
         [
            'comment'   => 'Gestão de Horas - Efetua os débitos de horas dos grupos requerentes',
            'mode'      => CronTask::MODE_EXTERNAL
         ]
      );

      // Recarrega os saldos dos grupos de acordo com o saldo padrão
      CronTask::Register(PluginGestaohorasJob::class, 'RecarregarSaldos', MONTH_TIMESTAMP,
         [
            'comment'   => 'Recarrega os saldos dos grupos de acordo com o saldo padrão',
            'mode'      => CronTask::MODE_EXTERNAL
         ]
      );

   }
}
