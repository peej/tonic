<?php

/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/one
 * @uri /requesttest/three/(.+)/four 12
 */
class NewResource extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/one/two
 */
class ChildResource extends NewResource {

}

/**
 * @namespace Tonic\Tests
 */
class NewNoResource extends NoResource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/railsstyle/:param/:param2
 * @uri /requesttest/uritemplatestyle/{param}/{param2}
 */
class TwoUriParams extends Resource {

    var $params;
    
    function get($request, $param, $param2) {
        $this->receivedParams = array(
            'param' => $param,
            'param2' => $param2
        );
        return new Response($request);
    }
    
}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/railsmixedstyle/{param}/(.+)/{param2}/(.+)
 * @uri /requesttest/uritemplatemixedstyle/{param}/(.+)/{param2}/(.+)
 * @uri /requesttest/mixedstyle/:param/(.+)/{param2}/(.+)
 */
class FourUriParams extends Resource {
    
    var $params;
    
    function get($request, $something, $otherthing, $param, $param2) {
        $this->receivedParams = array(
            'param' => $param,
            'param2' => $param2,
            'something' => $something,
            'otherthing' => $otherthing
        );
        return new Response($request);
    }
    
}

