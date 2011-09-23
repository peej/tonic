<?php

require_once 'helloworld/helloworld.php';
require_once 'filesystem/filesystem.php';
require_once 'filesystem/filesystemcollection.php';
require_once 'smarty/smarty.php';
require_once 'htmlform/htmlform.php';
require_once 'auth/auth.php';
require_once 'multirep/multirep.php';

/**
 * Examples listing
 * @namespace Tonic\Examples
 * @uri /
 */
class ExamplesListResource extends Resource {
    
    function get($request) {
        
        $response = new Response($request);
        
        $examples = '';
        $dirs = glob(dirname(__FILE__).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
        if ($dirs) {
            foreach ($dirs as $path) {
                $location = basename($path);
                $readme = $path.DIRECTORY_SEPARATOR.$location.'.php';
                if (file_exists($readme)) {
                    preg_match('|/\*\*\s*\*\s*(.+?)\*/|s', file_get_contents($readme), $match);
                    $comment = preg_replace('|\s*\*\s*(@.+)?|', "\n", $match[1]);
                    $parts = explode("\n\n", $comment);
                    $name = array_shift($parts);
                    $description = join(' ', $parts);
                } else {
                    $name = $location;
                    $description = '';
                }
                $examples .= 
                    '<li>'.
                    '<h3><a href="'.$location.'">'.$name.'</a></h3>'.
                    '<p>'.$description.'</p>'.
                    '</li>';
            }
        } else {
            $examples .= '<li>No examples</li>';
        }
        
        $response->body = <<<END
<h1>Welcome to Tonic</h1>
<p>Below is a list of example uses of the Tonic library. View each example to see it in action.</p>
<h2>Examples</h2>
END;
        $response->body .= '<ul>'.$examples.'</ul>';
        
        return $response;
        
    }
    
}

