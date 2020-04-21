<?php

require_once('../../../inc/includes.php');

// Check if current user have config right
Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('gestaohoras')) {
    Html::displayNotFoundError();
}

if (PluginGestaohorasBalance_Hour::canView()) {

    Html::header(
        'Gestão de Horas',
        $_SERVER['PHP_SELF'],
        'admin',
        'PluginGestaohorasBalance_Hour'
    );

    echo "<div style='text-align: center; margin-bottom:20px'>
            <a href='" . $CFG_GLPI["root_doc"] . "/plugins/gestaohoras/front/form.category.php' style='color: #fff; background: #3A5693; padding: 6px;'>Configuração Categorias</a> 
         </div>";

    Search::show('PluginGestaohorasBalance_Hour');

    echo "<script type='text/javascript'>
        
        $(document).ready(function() {
            
            // Adiciona Link na coluna grupo de chamado
            var row = $('.tab_cadrehov tbody tr');
            row.each(function() {
                var td = $(this).find('td');
                
                if (td.length > 0) {
                    link = $($(td[2]).find('a')).attr('href');
                    group = $(td[1]).html();
                    $(td[1]).html('<a href=\''+link+'\'>'+ group +'</a>');
                } 
               
            });
            
            // Remove os botões de ações massivas
            var actions = $('.tab_glpi');
            actions.each(function() {
                $(this).remove();
            })
            
        });
        
    </script>";

    Html::footer();
} else {
    Html::displayRightError();
}
