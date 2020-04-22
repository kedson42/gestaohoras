<?php



include('../../../inc/includes.php');


$plugin = new Plugin();



if (!$plugin->isInstalled('gestaohoras') || !$plugin->isActivated('gestaohoras')) {
    Html::displayNotFoundError();
}


if (PluginGestaohorasBalance_Hour::canView()) {

    Html::header(
        'Gestão de Horas - Configuração do Grupo',
        $_SERVER['PHP_SELF'],
        'admin',
        'PluginGestaohorasBalance_Hour'
    );

    global $DB;

    $category = new ITILCategory();

    $arraySelect = $DB->request([
        'SELECT' => ['id', 'completename'],
        'FROM'  => $category->getTable(),
    ]);

    $categorias = [];
    while ($categoria = $arraySelect->next()) {
        $categorias[] = $categoria;
    }


    echo "<form action='../ajax/category.php' method='POST'>";
    echo "<table class='tab_cadre_central' style='background:" . '#ffffff' . "'>";
    echo "<tbody>";
    echo "<tr class='noHover' style='padding:20px'>";
    echo "<td class='top' width='10%' style='margin-bottom:30px'>";

    $p = [];

    if (count($categorias)) {
        foreach ($categorias as $key => $val) {
            $p[$val['id']] = $val['completename'];
        }
    }

    Dropdown::showFromArray('categoria_id', $p, ['on_change' => "checkCategoryFlags()"]);
    echo Html::scriptBlock('     
        
  $( document ).ready(function() {   checkCategoryFlags(); });

        function checkCategoryFlags(){ 

        $.ajax({  type: "GET",
                    url: "../ajax/category.php",
                    data:  $("select[name=categoria_id]"),
                    success: function(response)
                {      

                  var response = JSON.parse(response);
                               

                    if( response.debitofield == true && $("input[name=debitofield]").is(":checked") == false ){
                        $("input[name=debitofield]").click();
                    }else if (response.debitofield == false && $("input[name=debitofield]").is(":checked") == true){
                        $("input[name=debitofield]").click();
                    }
                }});

     } ');
    echo "</td>";
    echo "<td class='top' width='5%'>";

    echo "<label style='margin-left:20px'> Debito </label>";
    echo Html::getCheckbox([$p, 'name' => 'debitofield']);

    echo "</td>";

    echo "<td class='top' width='5%'>";

    echo Html::submit('Salvar');
    echo "</td>";

    echo "<td class='top' width='40%'>";
    echo "<b>Somente chamados das categorias selecionadas entrarão no fluxo do plugin.</b>";

    echo "</tr>";

    echo "</tbody>";
    echo "</table>";
    echo "</form>";

    echo '<table border="0" class="tab_cadrehov"><thead><tr class="tab_bg_2"><th class="">Categoria</th><th class="">Débito</th></tr></thead><tbody>';

    PluginGestaohorasCategory::showTable();
    echo '</tbody></table>';

    Html::footer();
} else {
    Html::displayRightError();
}
