<?php

/**
 *  @uri /:tablename
 */
class Table extends Tonic\Resource
{

    /**
     * @method get
     */
    function renderTable($tableName)
    {
        $db = $this->container['database'];
        $smarty = $this->container['smarty'];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

        $statement = $db->query('SELECT * FROM '.$tableName.' LIMIT '.$page.',10;');

        $data = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[] = $row;
        }
        
        $smarty->assign('fields', array_keys($data[0]));
        $smarty->assign('data', $data);

        $smarty->assign('page', $page);

        return $smarty->fetch('table.html');
    }

}