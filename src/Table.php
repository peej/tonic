<?php

/**
 *  @uri /:database/:table
 */
class Table extends Base
{

    private function fetchTableData($database, $table, $page)
    {
        $db = $this->getDB($database);

        $statement = $db->query('SELECT * FROM '.$table.' LIMIT '.$page.',10;');
        if (!$statement) {
            throw new Tonic\NotFoundException;
        }

        $data = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[] = $row;
        }
        return $data;
    }

    private function getPage()
    {
        return isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    }

    /**
     * @method get
     */
    function html($database, $table)
    {
        $smarty = $this->container['smarty'];

        $page = $this->getPage();
        $data = $this->fetchTableData($database, $table, $page);

        if ($data) {
            $smarty->assign('fields', array_keys($data[0]));
            $smarty->assign('data', $data);
        }
        $smarty->assign('table', $table);
        $smarty->assign('page', $page);

        return $smarty->fetch('table.html');
    }

    /**
     * @method get
     * @provides application/hal+json
     */
    function hal($database, $table)
    {
        $page = $this->getPage();
        $data = $this->fetchTableData($database, $table, $page);

        $hal = new Nocarrier\Hal('/tables/'.$table, $data);

        if ($page > 1) $hal->addLink('prev', '/'.$database.'/'.$table.'.hal?page='.($page - 1), 'Previous page');
        $hal->addLink('next', '/'.$database.'/'.$table.'.hal?page='.($page + 1), 'Next page');

        return $hal->asJson(true);
    }

}