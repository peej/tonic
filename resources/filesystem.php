<?php

/**
 * Load files from the filesystem as resource representations
 * @package Tonic/Resources
 */
class FilesystemResource extends Resource {
    
    /**
     * Path to the files to use
     * @var str
     */
    var $path = '../representations';
    
    /**
     * URI stub
     * @var str
     */
    var $uriStub = '/';
    
    /**
     * The default document to use if the request is for a URI that maps to a directory
     * @var str
     */
    var $defaultDocument = 'default.html';
    
    /**
     * Handle a GET request for this resource by returning the contents of a file matching the request URI
     * @param Request request
     * @return Response
     */
    function get($request) {
        
        // look at all candidate URIs in turn and stop when we find a file that matches one
        foreach ($request->negotiatedUris as $uri) {
            
            // convert URI into filesystem path
            $filePath = $this->path.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, substr($uri, strlen($this->uriStub)));
            
            if (substr($filePath, -1, 1) == '/') { // add a default filename to the path
                $filePath .= $this->defaultDocument;
                $uri .= $this->defaultDocument;
            }
            
            if (file_exists($filePath)) { // use this file
                
                $response = new Response($request, $uri);
                
                // generate etag for the resource based on the files modified date
                $etag = md5(filemtime($filePath));
                if ($request->ifNoneMatch($etag)) { // client has matching etag
                    
                    $response->code = Response::NOTMODIFIED;
                    
                } else {
                    
                    $extension = array_pop(explode('.', $filePath));
                    if (isset($request->mimetypes[$extension])) { // add content type header
                        $response->addHeader('Content-Type', $request->mimetypes[$extension]);
                    }
                    
                    $response->addEtag($etag); // add etag header
                    
                    $response->body = file_get_contents($filePath); // set contents
                    
                }
                return $response;
                
            }
            
        }
        
        // nothing found, send 404 response
        $response = new Response($request);
        $response->code = Response::NOTFOUND;
        return $response;
        
    }
    
}

?>
