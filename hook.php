<?php

function display_resume()
{
   $plugin = new Plugin();
   $pluginSaldo = current($plugin->find("directory = 'gestaohoras' AND state = 1"));

   if ($pluginSaldo) {

      $saldoHoras = PluginGestaohorasBalance_Hour::getSaldos();

      if ($saldoHoras) {
         echo "<p><b>Saldo de horas: {$saldoHoras['total']}h</b></p>";
         echo "<p><b>Limite diário: {$saldoHoras['limite']}</b></p>";
      }
   }
}

/**
 * Install all necessary elements for the plugin
 * @return boolean True if success
 */
function plugin_gestaohoras_install()
{
   require_once(__DIR__ . '/install/install.php');

   spl_autoload_register('plugin_gestaohoras_autoload');

   $version = plugin_version_gestaohoras();
   $migration = new Migration($version['version']);

   $install = new PluginGestaohorasInstall();
   $install->install($migration);

   return true;
}

/**
 * Uninstall previously installed elements of the plugin
 */
function plugin_gestaohoras_uninstall()
{
   require_once(__DIR__ . '/install/install.php');

   $install = new PluginGestaohorasInstall();
   $install->uninstall();
}
