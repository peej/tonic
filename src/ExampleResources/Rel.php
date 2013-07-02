<?php
namespace ExampleResources;

use Exception;
use Tonic\NotFoundException;
use Tonic\Resource;

/**
 *  @uri /rel/:name
 */
class Rel extends Resource
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
            throw new NotFoundException;
        }
    }

}