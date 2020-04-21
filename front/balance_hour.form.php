<?php

include("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('gestaohoras') || !$plugin->isActivated('gestaohoras')) {
   Html::displayNotFoundError();
}

$object = new PluginGestaohorasBalance_Hour();
$input = new PluginGestaohorasBalance_Input();

if (isset($_POST['avulso'])) {
   $input->balanceInputAdd($_POST);
}

if (isset($_POST['add'])) {

   // Complement
   $_POST['total'] = $_POST['default'];
   $_POST['users_id'] = Session::getLoginUserID();

   //Check CREATE ACL
   $object->check(-1, CREATE, $_POST);

   if ($object->groupExistis($_POST['groups_id'])) {
      echo "<script>alert('Grupo já cadastrado!'); window.location = '{$CFG_GLPI['root_doc']}/plugins/gestaohoras/front/balance_hour.form.php'</script>";
      die;
   }

   //Do object creation
   $newid = $object->add($_POST);

   // Add History
   if ($newid) {
      $object->balanceHistoryAdd($newid, $_POST['default']);
   }

   //Redirect to newly created object form
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/gestaohoras/front/balance_hour.form.php?id=$newid");
} else if (isset($_POST['update'])) {

   //Check UPDATE ACL
   $object->check($_POST['id'], UPDATE);

   // History balance
   // Desabilitando atualizacao do saldo pela tela
   //  $object->balanceUpdate($_POST['id'], $_POST['total']);

   //Do object update
   $object->update($_POST);
   //Redirect to object form
   Html::back();
} else if (isset($_POST['purge'])) {

   // Delete histórico
   $result = $DB->delete(
      'glpi_plugin_gestaohoras_balances_historys',
      ['plugin_gestaohoras_balances_hours_id' => $_POST['id']]
   );

   // Deleta o saldo  
   $result = $DB->delete(
      'glpi_plugin_gestaohoras_balances_hours',
      ['id' => $_POST['id']]
   );

   //Redirect to objects list
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/gestaohoras/front/balance_hour.php");
} else {

   if (PluginGestaohorasBalance_Hour::canView()) {

      Html::header(
         'Gestão de horas (Grupo)',
         $_SERVER['PHP_SELF'],
         'admin',
         'PluginGestaohorasBalance_Hour'
      );

      $_GET['id'] = isset($_GET['id']) ? intval($_GET['id']) : -1;

      $object->display($_GET);

      Html::footer();
   } else {
      Html::displayRightError();
   }
}
