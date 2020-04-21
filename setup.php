<?php

global $CFG_GLPI;

define('PLUGIN_GESTAOHORAS_VERSION', '1.0.0');
define('GESTAOHORAS_ROOTDOC', $CFG_GLPI['root_doc'] . '/plugins/gestaohoras');
define('PLUGIN_GESTAOHORAS_GLPI_MIN_VERSION', '9.3.3');

/**
 * Define the plugin's version and informations
 *
 * @return Array [name, version, author, homepage, license, minGlpiVersion]
 */
if (!function_exists("plugin_version_gestaohoras")) {
   function plugin_version_gestaohoras()
   {
      $glpiVersion = rtrim(GLPI_VERSION, '-dev');

      if (!method_exists('Plugins', 'checkGlpiVersion') && version_compare($glpiVersion, PLUGIN_GESTAOHORAS_GLPI_MIN_VERSION, 'lt')) {
         echo 'This plugin requires GLPI >= ' . PLUGIN_GESTAOHORAS_GLPI_MIN_VERSION;
         return false;
      }

      $requirements = [
         'name' => _n('Gestão de Horas', 'Gestão de Horas', 2, 'gestaohoras'),
         'version' => PLUGIN_GESTAOHORAS_VERSION,
         'author' => '<b>Kedson Silva</b>',
         'homepage' => 'https://github.com/kedson42/gestaohoras',
         'requirements' => [
            'glpi' => [
               'min' => PLUGIN_GESTAOHORAS_GLPI_MIN_VERSION,
            ]
         ]
      ];

      return $requirements;
   }
}

/**
 * Check plugin's prerequisites before installation
 *
 * @return boolean
 */
if (!function_exists("plugin_gestaohoras_check_prerequisites")) {
   function plugin_gestaohoras_check_prerequisites()
   {
      return true;
   }
}

/**
 * Check plugin's config before activation (if needed)
 *
 * @return boolean
 */
if (!function_exists("plugin_gestaohoras_check_config")) {
   function plugin_gestaohoras_check_config()
   {
      return true;
   }
}

/**
 * Class autoload
 *
 * @param $classname
 * @return bool
 */
if (!function_exists("plugin_gestaohoras_autoload")) {
   function plugin_gestaohoras_autoload($classname)
   {
      if (strpos($classname, 'PluginGestaohoras') === 0) {

         $filename = __DIR__ . '/inc/' . strtolower(str_replace('PluginGestaohoras', '', $classname)) . '.class.php';

         if (is_readable($filename) && is_file($filename)) {
            include_once($filename);
            return true;
         }
      }
   }
}

/**
 * Initialize all classes and generic variables of the plugin
 */

if (!function_exists("plugin_init_gestaohoras")) {
   function plugin_init_gestaohoras()
   {
      global $PLUGIN_HOOKS;

      $PLUGIN_HOOKS['csrf_compliant']['gestaohoras'] = true;
      $PLUGIN_HOOKS['display_central']['gestaohoras'] = 'display_resume';

      // Menus
      $PLUGIN_HOOKS['config_page']['gestaohoras'] = 'front/balance_hour.php';
      $PLUGIN_HOOKS['menu_toadd']['gestaohoras']['admin'] = 'PluginGestaohorasBalance_Hour';

      $PLUGIN_HOOKS['add_javascript']['gestaohoras'][] = 'js/scripts.js.php';

   }
}
