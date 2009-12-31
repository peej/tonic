<?php

/**
 * Load files from the filesystem as resource representations
 * @uri / 9999
 */
class FilesystemResource extends Resource {
    
    var $path = '../representations',
        $defaultDocument = 'default.html';
    
    function get($request) {
        
        foreach ($request->uris as $uri) {
            
            $filePath = $this->path.str_replace('/', DIRECTORY_SEPARATOR, $uri);
            
            if (substr($filePath, -1, 1) == '/') {
                $filePath .= $this->defaultDocument;
            }
            
            if (file_exists($filePath)) {
                
                $response = new Response($request);
                
                $extension = array_pop(explode('.', $filePath));
                if (isset($request->mimetypes[$extension])) {
                    $response->addHeader('content-type', $request->mimetypes[$extension]);
                }
                
                $response->body = file_get_contents($filePath);
                return $response;
                
            }
            
        }
        
        $response = new Response($request);
        $response->code = Response::NOTFOUND;
        return $response;
        
    }
    
}

?>
