<?php


if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}



class PluginGestaohorasCategory extends CommonDBTM
{
    public function updateFields(array $array)
    {
        global $DB;

        $debito = isset($array['debitofield']) ? 1 : 0;


        if ($this->checkCategoryFlags($array['categoria_id'])) {

            $DB->update(
                'glpi_plugin_gestaohoras_itilcategorycategorias',
                [
                    'debitofield' => $debito
                ],
                ['items_id' => $array['categoria_id']]
            );
        } else {
            $DB->insert(
                'glpi_plugin_gestaohoras_itilcategorycategorias',
                [
                    'debitofield' => $debito,
                    'items_id' => $array['categoria_id']
                ]
            );
        }

        return true;
    }


    public function showTable()
    {

        global $DB;

        $iterator = $DB->request(
            'glpi_plugin_gestaohoras_itilcategorycategorias',
            ['INNER JOIN' =>
            ['glpi_itilcategories' => ['FKEY' => [
                'glpi_itilcategories'      => 'id',
                'glpi_plugin_gestaohoras_itilcategorycategorias' => 'items_id'
            ]]]]
        );

        $values = [];


        if (count($iterator)) {
            while ($data = $iterator->next()) {
                $values[] = $data;
            }
        }

        foreach ($values as $val) {
            echo '<tr class="tab_bg_2">';
            echo '<td>';
            echo $val['name'];
            echo '</td>';

            echo '<td valign="top">';
            if ($val['debitofield']) {
                echo 'Ativo';
            } else {
                echo 'Inativo';
            }
            echo '</td>';

            echo '</tr>';
        }
    }

    public function checkCategoryFlags(int $id)
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['debitofield'],
            'FROM'   => 'glpi_plugin_gestaohoras_itilcategorycategorias',
            'WHERE'  => ['items_id' => $id]
        ]);

        if (count($iterator)) {
            while ($data = $iterator->next()) {
                $retorno = $data;
            }
        }
        return $retorno;
    }
}
