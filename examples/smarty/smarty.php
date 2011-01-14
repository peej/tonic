<?php

namespace Tonic\Examples\Smarty;

use Tonic as Tonic;
use Smarty as Smarty; // smarty not namespace aware yet so mapping to root.

require_once 'smarty/Smarty.class.php';

/**
 * Smarty template example
 *
 * Using Smarty for representation generation
 *
 * @uri /smarty
 */
class SmartyResource extends Tonic\Resource {
    
    protected $smarty;
    
    function __construct() {
        
        $this->smarty = new Smarty(); 
        $this->smarty->template_dir = '../examples/smarty/representations';
        $this->smarty->compile_dir = sys_get_temp_dir();
        
    }
    
    function get($request) {
        
        $response = new Tonic\Response($request);
        
        $this->smarty->assign('title', 'Smarty template');
        $body = $this->render('default');
        
        $etag = md5($body);
        if ($request->ifNoneMatch($etag)) {
            
            $response->code = Tonic\Response::NOTMODIFIED;
            
        } else {
        
            $response->code = Tonic\Response::OK;
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
