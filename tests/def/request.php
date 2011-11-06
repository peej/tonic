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
 * @uri /requesttest/railsstyle/:param/:param2
 * @uri /requesttest/uritemplatestyle/{param}/{param2}
 */
class TwoUriParams extends Resource {

    var $receivedParams;
    
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
    
    var $receivedParams;
    
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

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/trailingslashurl/
 */
class TrailingSlashUrl extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/optional(?:/param1/([^/]*))?
 * @uri /requesttest/optional(?:/([a-z]+))?
 */
class OptionalParams extends Resource {
    
}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/squiggly/(\d+-[0-9a-f]{8}-[0-9a-f]{6})
 * @uri /requesttest/noncapture/(?:something)?
 */
class SquigglyRegexResource extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/httpmethods
 */
class MethodTestResource extends Resource {
    
    function options($request) {
        return new Response($request);
    }
    
    function woot($request) {
        return new Response($request);
    }
    
}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/priority 1
  */
class PriorityTestLessImportantResource extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/priority 2
  */
class PriorityTestMoreImportantResource extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/differenturi(priority) 1
  */
class PriorityTestDifferntURILessImportantResource extends Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/differenturipriority 2
  */
class PriorityTestDifferntURIMoreImportantResource extends Resource {

}
