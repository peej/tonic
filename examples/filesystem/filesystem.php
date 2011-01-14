<?php

namespace Tonic\Examples\Filesystem;

use Tonic as Tonic;

/**
 * Use the filesystem as resource representations
 *
 * This example loads files from the filesystem to be used as resource representations
 * and provides a resource collection to contain them within. It also demonstrates
 * the ability to add resources to the collection, and update and delete resources
 * from within the collection.
 *
 * @uri /filesystem(/.*)?
 */
class FilesystemResource extends Tonic\Resource {
    
    /**
     * Path to the files to use
     * @var string
     */
    protected $path = '../examples/filesystem/representations';
    
    /**
     * URI stub
     * @var string
     */
    protected $uriStub = '/filesystem/';
    
    /**
     * The default document to use if the request is for a URI that maps to a directory
     * @var string
     */
    protected $defaultDocument = 'default.html';
    
    protected function turnUriIntoFilePath($uri) {
        return $this->path.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, substr($uri, strlen($this->uriStub)));
    }
    
    /**
     * Handle a GET request for this resource by returning the contents of a file matching the request URI
     * @param Tonic\Request request
     * @return Tonic\Response
     */
    function get($request) {
        
        // look at all candidate URIs in turn and stop when we find a file that matches one
        foreach ($request->negotiatedUris as $uri) {
            
            // convert URI into filesystem path
            $filePath = $this->turnUriIntoFilePath($uri);
            
            if (substr($filePath, -1, 1) == DIRECTORY_SEPARATOR) { // add a default filename to the path
                $filePath .= $this->defaultDocument;
                $uri .= $this->defaultDocument;
            }
            
            if (file_exists($filePath)) { // use this file
                
                $response = new Tonic\Response($request, $uri);
                
                // generate etag for the resource based on the files modified date
                $etag = md5(filemtime($filePath));
                if ($request->ifNoneMatch($etag)) { // client has matching etag
                    
                    $response->code = Tonic\Response::NOTMODIFIED;
                    
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
        $response = new Tonic\Response($request);
        $response->code = Tonic\Response::NOTFOUND;
        $response->addHeader('Content-Type', $request->mimetypes['html']);
        $response->body = '<p>404, nothing found</p>';
        return $response;
        
    }
    
    /**
     * Handle a PUT request for this resource by overwriting the resources contents
     * @param Tonic\Request request
     * @return Tonic\Response
     */
    function put($request) {
        
        $response = new Tonic\Response($request);
        
        if ($request->data) {
            
            $filePath = $this->turnUriIntoFilePath($request->uri);
            
            file_put_contents($filePath, $request->data);
            
            $response->code = Tonic\Response::NOCONTENT;
            
        } else {
            
            $response->code = Tonic\Response::LENGTHREQUIRED;
            
        }
        
        return $response;
        
    }
    
    /**
     * Handle a DELETE request for this resource by removing the resources file
     * @param Tonic\Request request
     * @return Tonic\Response
     */
    function delete($request) {
        
        $response = new Tonic\Response($request);
        
        $filePath = $this->turnUriIntoFilePath($request->uri);
        
        if (file_exists($filePath)) {
            
            if (unlink($filePath)) {
                $response->code = Tonic\Response::NOCONTENT;
            } else {
                $response->code = Tonic\Response::INTERNALSERVERERROR;
            }
            
            return $response;
            
        } else { // nothing found, send 404 response
            $response->code = Tonic\Response::NOTFOUND;
        }
        
        return $response;
        
    }
    
}

?>
