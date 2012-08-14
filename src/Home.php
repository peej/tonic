<?php

/**
 *  @uri /
 */
class Home extends Tonic\Resource
{

    /**
     * @method get
     */
    function renderHomepage()
    {
        $db = $this->container['database'];
        $smarty = $this->container['smarty'];

        $statement = $db->query('SHOW TABLES;', PDO::FETCH_NUM);
        $tables = array();
        foreach ($statement as $table) {
            $tables[] = $table[0];
        }
        
        $smarty->assign('tables', $tables);

        return $smarty->fetch('tables.html');
    }

}