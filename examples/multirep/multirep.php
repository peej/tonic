<?php

/**
 * Multiple representation example
 *
 * A representation is an output format for a resource. A single resource can have
 * many output representations that can differ depending on the request data.
 *
 * Representations are built up as strings within the Response object and it is
 * down to the programmer to implement the desired logic for choosing and building
 * a required representation.
 *
 * This example produces three different representations of the resource data
 * depending on the request accept header information.
 *
 * @namespace Tonic\Examples\Multirep
 * @uri /multirep
 */
class MultiRepresentationResource extends Resource {
    
    var $data = array(
        'secret' => 'monkey',
        'hidden' => 'squirrel'
    );
    
    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get($request) {
        
        $response = new Response($request);
        $response->code = Response::OK;
        
        // select the most acceptable format of the given types from the request
        $format = $request->mostAcceptable(array(
            'json', 'html', 'txt'
        ));
        
        switch ($format) {
        
        // obviously it might make more sense to put the HTML in another file
        // and use some kind of templating system
        case 'html':
            $response->addHeader('Content-type', 'text/html');
            $response->body = '<!DOCTYPE html>';
            $response->body .= '<html>';
            $response->body .= '<head>';
            $response->body .= '<title>HTML Representation</title>';
            $response->body .= '</head>';
            $response->body .= '<body>';
            $response->body .= '<h1>HTML Representation</h1>';
            $response->body .= '<table border="1">';
            foreach ($this->data as $field => $value) {
                $response->body .= '<tr>';
                $response->body .= '<th>'.htmlspecialchars($field).'</th>';
                $response->body .= '<td>'.htmlspecialchars($value).'</td>';
                $response->body .= '</tr>';
            }
            $response->body .= '</table>';
            $response->body .= '<h2>Explicit links to available formats</h2>';
            $response->body .= '<a href="/multirep.txt">Plain text</a> ';
            $response->body .= '<a href="/multirep.json">JSON</a> ';
            $response->body .= '<a href="/multirep.html">HTML</a>';
            $response->body .= '</body>';
            $response->body .= '</html>';
            break;
        
        case 'json':
            $response->addHeader('Content-type', 'application/json');
            $response->body = json_encode($this->data);
            break;
        
        case 'txt':
            $response->addHeader('Content-type', 'text/plain');
            $response->body = "Plain Text Representation\n";
            $response->body .= "\n";
            ob_start();
            var_dump($this->data);
            $response->body .= ob_get_contents();
            ob_end_clean();
            break;
            
        // we don't have a suitable format, so do a 406 response instead
        default:
            $response->code = Response::NOTACCEPTABLE;
            $response->addHeader('Content-type', 'text/plain');
            $response->body = "406 Not Acceptable\n";
            break;
            
        }
        
        return $response;
        
    }
    
}

