<?php

/**
 * Display and process a HTML form via a HTTP POST request
 *
 * This example outputs a simple HTML form and gathers the POSTed data
 *
 * @namespace Tonic\Examples\HTMLForm
 * @uri /htmlform
 */
class HTMLForm extends Resource {
    
    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get($request, $name) {
        
        $response = new Response($request);
        
        if ($name) {
            $welcome = "<p>Hello $name.</p>".
                "<p>Raw POST data:</p>".
                "<blockquote>$request->data</blockquote>";
        } else {
            $welcome = "<p>Enter your name.</p>";
        }
        
        $response->addHeader('Content-type', 'text/html');
        $response->body = <<<EOF
<!DOCTYPE html>
<html>
    <body>
        <form action="htmlform" method="post">
            <label>Name: <input type="text" name="name"></label>
            <input type="submit">
        </form>
        $welcome
    </body>
</html>
EOF;
        
        return $response;
        
    }
    
    /**
     * Handle a POST request for this resource
     * @param Request request
     * @return Response
     */
    function post($request) {
        
        if (isset($_POST['name'])) {
            $name = htmlspecialchars($_POST['name']);
        } else {
            $name = '';
        }
        
        return $this->get($request, $name);
        
    }
    
}

