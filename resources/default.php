<?php

/**
 * Default resource to use, matches all URIs
 * @uri / 9999
 */
class DefaultResource extends Resource {
    
    function get() {
        
        return parent::get();
        
    }
    
}


/**
 *  @uri /one 10
 */
class SpecificResource extends Resource {
    
    
    
}

?>
