<?php

@include_once 'smarty/Smarty.class.php';

/**
 * Smarty template example
 *
 * Using Smarty for representation generation
 *
 * @namespace Tonic\Examples\Filesystem
 * @uri /smarty
 */
class SmartyResource extends Resource {
    
    protected $smarty;
    
    function __construct() {
        
        $this->smarty = new Smarty();
        $this->smarty->template_dir = '../examples/smarty/representations';
        $this->smarty->compile_dir = sys_get_temp_dir();
        
    }
    
    function get($request) {
        
        $response = new Response($request);
        
        $this->smarty->assign('title', 'Smarty template');
        $body = $this->render('default');
        
        $etag = md5($body);
        if ($request->ifNoneMatch($etag)) {
            
            $response->code = Response::NOTMODIFIED;
            
        } else {
        
            $response->code = Response::OK;
            $response->addHeader('Content-type', 'text/html');
            $response->body = $body;
            
        }
        
        return $response;
        
    }
    
    protected function render($view, $format = 'html', $useShell = TRUE) {
        
        if ($format == 'html' && $useShell) {
            $this->smarty->assign('body', $this->smarty->fetch($view.'.'.$format));
            return $this->smarty->fetch('shell.'.$format);
        } else {
            return $this->smarty->fetch($view.'.'.$format);
        }
        
    }
    
}
