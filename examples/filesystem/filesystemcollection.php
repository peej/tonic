<?php

namespace Tonic\Examples\Filesystem;

use Tonic as Tonic;

/**
 * Collection resource
 *
 * @uri /filesystem/collection
 */
class FilesystemCollection extends FilesystemResource {
    
    /**
     * Path to the collections files
     * @var string
     */
    protected $collection = '../examples/filesystem/representations/collection';
    
    /**
     * Handle a GET request
     * @param Tonic\Request request
     * @return Tonic\Response
     */
    function get($request) {
        
        $response = new Tonic\Response($request);
        $collection = str_replace('/', DIRECTORY_SEPARATOR, $this->collection);
        
        $resourceUris = '';
        $files = glob( $collection.DIRECTORY_SEPARATOR.'*' );
        if ($files) {
            foreach ($files as $filepath) {
            	$filepath = str_replace( DIRECTORY_SEPARATOR, '/', $filepath);
                $resourceUris .= '<li><a href="'.$this->uriStub.substr($filepath, strlen($this->path) + 1).'">'.basename($filepath).'</a></li>';
            }
        } else {
            $resourceUris .= '<li>Empty collection</li>';
        }
        
        $response->body = '<ul>'.$resourceUris.'</ul>';
        
        return $response;
        
    }
    
    protected function getNextAvailableItemUri() {
    	$collection = str_replace('/', DIRECTORY_SEPARATOR, $this->collection);
        $filename = 1;
        while (file_exists($collection.DIRECTORY_SEPARATOR.$filename)) {
            $filename++;
        }
        return $this->uriStub.substr($this->collection, strlen($this->path) + 1).'/'.$filename;
    }
    
    function post($request) {
        
        $response = new Tonic\Response($request);
        
        if ($request->data) {
            $uri = $this->getNextAvailableItemUri();
            $filePath = $this->turnUriIntoFilePath($uri);
            if (file_put_contents($filePath, $request->data)) {
                $response->code = Tonic\Response::CREATED;
                $response->addHeader('Location', $uri);
            } else {
                $response->code = Tonic\Response::INTERNALSERVERERROR;
            }
        } else {
            $response->code = Tonic\Response::LENGTHREQUIRED;
        }
        
        return $response;
        
    }
    
}

?>
