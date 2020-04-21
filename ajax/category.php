<?php

include('../../../inc/includes.php');

$categorias = new PluginGestaohorasCategory();
if ($_POST) {
    
    
    $categorias->updateFields($_POST);
    if ($categorias) {
        Html::redirect($CFG_GLPI["root_doc"] . "/plugins/gestaohoras/front/form.category.php");
        Session::addMessageAfterRedirect(__('The question has been successfully saved!', 'formcreator'), true, INFO);
    }
}

if ($_GET['categoria_id']) {
    $array = $categorias->checkCategoryFlags($_GET['categoria_id']);
    echo json_encode($array);
}
