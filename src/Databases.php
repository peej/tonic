<?php

/**
 *  @uri /
 */
class Databases extends Base
{

    private function fetchDatabases()
    {
        $db = $this->getDB('');
        
        $statement = $db->query('SHOW DATABASES;', PDO::FETCH_NUM);

        $dbs = array();
        foreach ($statement as $db) {
            $dbs[] = $db[0];
        }
        return $dbs;
    }

    /**
     * @method get
     */
    function html()
    {
        $smarty = $this->container['smarty'];
        $smarty->assign('databases', $this->fetchDatabases());
        $smarty->assign('rel', $this->getRel('database'));
        return $smarty->fetch('databases.html');
    }

    /**
     * @method get
     * @provides application/hal+json
     */
    function hal()
    {
        $hal = new Nocarrier\Hal('/');

        $rel = $this->getRel('database');

        foreach ($this->fetchDatabases() as $database) {
            $hal->addLink($rel, '/'.$database.'.hal', $database);
        }

        return $hal->asJson(true);
    }

}