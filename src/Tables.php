<?php

/**
 *  @uri /:database
 */
class Tables extends Base
{

    private function fetchTables($database)
    {
        $db = $this->getDB($database);

        $statement = $db->query('SHOW TABLES;', PDO::FETCH_NUM);

        $tables = array();
        foreach ($statement as $table) {
            $tables[] = $table[0];
        }
        return $tables;
    }

    /**
     * @method get
     */
    function html($database)
    {
        $smarty = $this->container['smarty'];
        $smarty->assign('database', $database);
        $smarty->assign('tables', $this->fetchTables($database));
        $smarty->assign('rel', $this->getRel('table'));
        return $smarty->fetch('tables.html');
    }

    /**
     * @method get
     * @provides application/hal+json
     */
    function hal($database)
    {
        $hal = new Nocarrier\Hal('/'.$database);

        $rel = $this->getRel('table');

        foreach ($this->fetchTables($database) as $table) {
            $hal->addLink($rel, '/'.$database.'/'.$table.'.hal', $table);
        }

        return $hal->asJson(true);
    }

}