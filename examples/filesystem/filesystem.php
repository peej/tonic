<?php

/**
 * Use the filesystem as resource representations
 *
 * This example loads files from the filesystem to be used as resource representations
 * and provides a resource collection to contain them within. It also demonstrates
 * the ability to add resources to the collection, and update and delete resources
 * from within the collection.
 *
 * @namespace Tonic\Examples\Filesystem
 * @uri /filesystem(/.*)?
 */
class FilesystemResource extends Resource {
    
    /**
     * Path to the files to use
     * @var str
     */
    var $path;
    
    /**
     * URI stub
     * @var str
     */
    var $uriStub = '/filesystem/';
    
    /**
     * The default document to use if the request is for a URI that maps to a directory
     * @var str
     */
    var $defaultDocument = 'default.html';
    
    function __construct($parameters) {
        parent::__construct($parameters);
        $this->path = dirname(__FILE__).'/representations/';
    }
    
    protected function turnUriIntoFilePath($uri, $request) {
        return $this->path.DIRECTORY_SEPARATOR.substr($uri, strlen($request->baseUri.$this->uriStub));
    }
    
    protected function turnFilePathIntoUri($path, $request) {
        return $request->baseUri.$this->uriStub.substr($path, strlen($this->path));
    }
    
    /**
     * Handle a GET request for this resource by returning the contents of a file matching the request URI
     * @param Request request
     * @return Response
     */
    function get($request) {
        
        // look at all candidate URIs in turn and stop when we find a file that matches one
        foreach ($request->negotiatedUris as $uri) {
            
            // convert URI into filesystem path
            $filePath = $this->turnUriIntoFilePath($uri, $request);
            
            if (substr($filePath, -1, 1) == DIRECTORY_SEPARATOR) { // add a default filename to the path
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
                    
                    $explodedPath = explode('.', $filePath);
                    $extension = array_pop($explodedPath);
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
        $response->addHeader('Content-Type', $request->mimetypes['html']);
        $response->body = '<p>404, nothing found</p>';
        return $response;
        
    }
    
    /**
     * Handle a PUT request for this resource by overwriting the resources contents
     * @param Request request
     * @return Response
     */
    function put($request) {
        
        $response = new Response($request);
        
        if ($request->data) {
            
            $filePath = $this->turnUriIntoFilePath($request->uri, $request);
            
            file_put_contents($filePath, $request->data);
            
            $response->code = Response::NOCONTENT;
            
        } else {
            
            $response->code = Response::LENGTHREQUIRED;
            
        }
        
        return $response;
        
    }
    
    /**
     * Handle a DELETE request for this resource by removing the resources file
     * @param Request request
     * @return Response
     */
    function delete($request) {
        
        $response = new Response($request);
        
        $filePath = $this->turnUriIntoFilePath($request->uri, $request);
        
        if (file_exists($filePath)) {
            
            if (unlink($filePath)) {
                $response->code = Response::NOCONTENT;
            } else {
                $response->code = Response::INTERNALSERVERERROR;
            }
            
            return $response;
            
        } else { // nothing found, send 404 response
            $response->code = Response::NOTFOUND;
        }
        
        return $response;
        
    }
    
}

