<?php

/**
 *  @uri /rel/:name
 */
class Rel extends Tonic\Resource
{

    /**
     * @method get
     */
    function html($name)
    {
        $smarty = $this->container['smarty'];
        $smarty->assign('rel', $name);
        try {
            return $smarty->fetch('rel-'.$name.'.html');
        } catch (Exception $e) {
            throw new Tonic\NotFoundException;
        }
    }

}