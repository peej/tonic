<?php

namespace Tonic;

class Resource {

    protected $request;
    public $params;
    protected $responseActions = array();

    function __construct(Request $request, array $urlParams) {

        $this->request = $request;
        $this->params = $urlParams;

    }

    function __get($name) {
        return isset($this->params[$name]) ? $this->params[$name] : NULL;
    }

    /**
     * Execute the resource, that is, find the correct resource method to call
     * based upon the request and then call it.
     *
     * @param str methodName Optional name of method to execute, bypasing annotations, useful for debugging
     * @return Tonic\Response
     */
    public final function exec($methodName = NULL) {

        // get the annotation metadata for this resource
        $resourceMetadata = $this->request->getResourceMetadata($this);

        $error = new NotAcceptableException;
        $methodPriorities = array();

        if (isset($resourceMetadata['methods'])) {
            foreach ($resourceMetadata['methods'] as $key => $methodMetadata) {
                if (!$methodName || $methodName == $key) {
                    $methodPriorities[$key] = 0;
                    foreach ($methodMetadata as $conditionName => $params) { // process each method condition
                        if (method_exists($this, $conditionName)) {
                            try {
                                if (is_array($params)) {
                                    $condition = call_user_func_array(array($this, $conditionName), $params);
                                } else {
                                    $condition = call_user_func(array($this, $conditionName), $params);
                                }
                                if (!$condition) $condition = 0;
                                $methodPriorities[$key] += $condition;
                            } catch (Exception $e) {
                                unset($methodPriorities[$key]);
                                $error = $e;
                                $error->appendMessage(' for method "'.get_class($this).'::'.$key.'"');
                                break;
                            }
                        } else {
                            throw new \Exception(sprintf(
                                'Condition method "%s" not found in Resource class "%s"',
                                $conditionName,
                                get_class($this)
                            ));
                        }
                    }
                } else {
                    unset($methodPriorities[$key]);
                }
            }
        }
        
        if ($methodPriorities) {
            $methodPriorities = array_flip($methodPriorities);
            ksort($methodPriorities);
            $methodName = array_pop($methodPriorities);

            $response = call_user_func_array(array($this, $methodName), $this->params);
            if (is_array($response)) {
                $response = new Response($response[0], $response[1]);
            } elseif (is_int($response)) {
                $response = new Response($response);
            } elseif (is_string($response)) {
                $response = new Response(200, $response);
            } elseif (!is_a($response, 'Tonic\Response')) {
                $response = new Response;
            }

            foreach ($this->responseActions as $action) {
                $action($response);
            }

            return $response;
        }

        throw $error;
    }

    /**
     * Add a function to execute on the response before it is returned
     *
     * @param callable $action
     */
    protected final function addResponseAction($action) {
        if (is_callable($action)) {
            $this->responseActions[] = $action;
        }
    }

    /**
     * HTTP method condition must match request method
     * @param str $method
     */
    protected final function method($method) {
        $methods = explode(' ', $method);
        foreach ($methods as $method) {
            if (strtolower($this->request->method) == strtolower($method)) return;
        }
        throw new MethodNotAllowedException('No matching method for HTTP method "'.$this->request->method.'"');
    }

    /**
     * Higher priority method takes precident over other matches
     * @param int $priority
     */
    protected final function priority($priority) {
        return $priority;
    }

    /**
     * Accepts condition mimetype must match request content type
     * @param str $mimetype
     */
    protected function accepts($mimetype) {
        if (strtolower($this->request->contentType) != strtolower($mimetype))
            throw new UnsupportedMediaTypeException('No matching method for content type "'.$this->request->contentType.'"');
    }

    /**
     * Provides condition mimetype must be in request accept array, returns a number
     * based on the priority of the match.
     * @param str $mimetype
     * @return int
     */
    protected function provides($mimetype) {
        $pos = array_search($mimetype, $this->request->accept);
        if ($pos === FALSE)
            throw new NotAcceptableException('No matching method for response type "'.join(', ', $this->request->accept).'"');
        $this->addResponseAction(function ($response) use ($mimetype) {
            $response->header('content-type', $mimetype);
        });
        return count($this->request->accept) - $pos;
    }

    /**
     * Set cache control header on response
     * @param int length Number of seconds to cache the response for
     */
    protected function cache($length) {
        $this->addResponseAction(function ($response) use ($length) {
            if ($length == 0) {
                $response->header('cache-control', 'no-cache');
            } else {
                $response->header('cache-control', 'max-age='.$length.', must-revalidate');
            }
        });
    }

    public function __toString() {
        $params = array();
        foreach ($this->params as $name => $value) {
            $params[] = $name.' = "'.$value.'"';
        }
        $params = join(', ', $params);
        $methodMetadata = $this->request->getResourceMetadata($this);
        $class = $methodMetadata['class'];
        $uri = array();
        foreach($methodMetadata['uri'] as $u) {
            $uri[] = $u[0];
        }
        $uri = join(', ', $uri);
        $methods = '';
        foreach ($methodMetadata['methods'] as $methodName => $method) {
            $methods .= "\n\t".$methodName;
            foreach ($method as $itemName => $item) {
                $methods .= ' '.$itemName.'="'.join(', ', $item).'"';
            }
        }
        return <<<EOF
==============
Tonic\Resource
==============
Class: $class
URI regex: $uri
Params: $params
Methods: $methods

EOF;
    }

}