<?php

namespace Tonic;

/**
 * Model a HTTP resource
 */
class Resource
{
    protected $app, $request;
    public $params;
    private $currentMethodName;
    protected $before = array(), $after = array();

    public function __construct(Application $app, Request $request, array $urlParams)
    {
        $this->app = $app;
        $this->request = $request;
        $this->params = $urlParams;
    }

    /**
     * Get a URL parameter as defined by this resource and it's URI
     * @param  str $name Name of the parameter
     * @return str
     */
    public function __get($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : NULL;
    }

    /**
     * Get the method name of the best matching resource method.
     *
     * @return str
     */
    private function calculateMethodPriorities()
    {
        // get the annotation metadata for this resource
        $resourceMetadata = $this->app->getResourceMetadata($this);

        $methodPriorities = array();

        if (isset($resourceMetadata['methods'])) {
            foreach ($resourceMetadata['methods'] as $key => $methodMetadata) {
                foreach ($methodMetadata as $conditionName => $conditions) { // process each method condition
                    if (method_exists($this, $conditionName)) {
                        $this->currentMethodName = $key;
                        $success = false;
                        foreach ($conditions as $params) {
                            try {
                                if (is_array($params)) {
                                    $condition = call_user_func_array(array($this, $conditionName), $params);
                                } else {
                                    $condition = call_user_func(array($this, $conditionName), $params);
                                }
                                if (!isset($methodPriorities[$key])) $methodPriorities[$key] = 0;
                                if (!$condition) $condition = 0;
                                if (is_numeric($condition)) {
                                    $methodPriorities[$key] += $condition;
                                } elseif ($condition) {
                                    $resourceMetadata['methods'][$key]['response'] = $condition;
                                }
                                $success = true;
                            } catch (ConditionException $e) {
                                unset($methodPriorities[$key]);
                                break 2;
                            } catch (Exception $e) {
                                $error = $e;
                            }
                        }
                        if (!$success) {
                            unset($methodPriorities[$key]);
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
            }
        }

        if ($methodPriorities) {
            return $methodPriorities;
        }

        throw $error;
    }

    /**
     * Execute the resource, that is, find the correct resource method to call
     * based upon the request and then call it.
     *
     * @return Tonic\Response
     */
    final public function exec()
    {

        $resourceMetadata = $this->app->getResourceMetadata($this);

        $methodPriorities = $this->calculateMethodPriorities();

        $methodPriorities = array_flip(array_reverse($methodPriorities));
        ksort($methodPriorities);
        $methodName = array_pop($methodPriorities);
        
        if (isset($resourceMetadata['methods'][$methodName]['response'])) {
            return Response::create($resourceMetadata['methods'][$methodName]['response']);

        } else {
            if (isset($this->before[$methodName])) {
                foreach ($this->before[$methodName] as $action) {
                    $action($this->request);
                }
            }
            $response = Response::create(call_user_func_array(array($this, $methodName), $this->params));
            if (isset($this->after[$methodName])) {
                foreach ($this->after[$methodName] as $action) {
                    $action($response);
                }
            }
        }

        return $response;
    }

    /**
     * Add a function to execute on the request before the resource method is called
     *
     * @param callable $action
     */
    final protected function before($action)
    {
        if (is_callable($action)) {
            $this->before[$this->currentMethodName][] = $action;
        }
    }

    /**
     * Add a function to execute on the response after the resource method is called
     *
     * @param callable $action
     */
    final protected function after($action)
    {
        if (is_callable($action)) {
            $this->after[$this->currentMethodName][] = $action;
        }
    }

    /**
     * HTTP method condition must match request method
     * @param str $method
     */
    final protected function method($method)
    {
        if (strtolower($this->request->method) != strtolower($method))
            throw new MethodNotAllowedException('No matching method for HTTP method "'.$this->request->method.'"');
    }

    /**
     * Higher priority method takes precident over other matches
     * @param int $priority
     */
    final protected function priority($priority)
    {
        return intval($priority);
    }

    /**
     * Accepts condition mimetype must match request content type
     * @param str $mimetype
     */
    protected function accepts($mimetype)
    {
        if (strtolower($this->request->contentType) != strtolower($mimetype)) {
            throw new UnsupportedMediaTypeException('No matching method for content type "'.$this->request->contentType.'"');
        }
    }

    /**
     * Provides condition mimetype must be in request accept array, returns a number
     * based on the priority of the match.
     * @param  str $mimetype
     * @return int
     */
    protected function provides($mimetype)
    {
        if (count($this->request->accept) == 0) return 0;
        $pos = array_search($mimetype, $this->request->accept);
        if ($pos === FALSE) {
            if (in_array('*/*', $this->request->accept)) {
                return 0;
            } else {
                throw new NotAcceptableException('No matching method for response type "'.join(', ', $this->request->accept).'"');
            }
        } else {
            $this->after(function ($response) use ($mimetype) {
                $response->contentType = $mimetype;
            });
            return count($this->request->accept) - $pos;
        }
    }

    /**
     * Lang condition language code must be in request accept lang array, returns a number
     * based on the priority of the match.
     * @param  str $language
     * @return int
     */
    protected function lang($language)
    {
        $pos = array_search($language, $this->request->acceptLanguage);
        if ($pos === FALSE)
            throw new NotAcceptableException('No matching method for response type "'.join(', ', $this->request->acceptLanguage).'"');

        return count($this->request->acceptLanguage) - $pos;
    }

    /**
     * Set cache control header on response
     * @param int length Number of seconds to cache the response for
     */
    protected function cache($length)
    {
        $this->addResponseAction(function ($response) use ($length) {
            if ($length == 0) {
                $response->cacheControl = 'no-cache';
            } else {
                $response->cacheControl = 'max-age='.$length.', must-revalidate';
            }
        });
    }

    public function __toString()
    {
        $params = array();
        if (is_array($this->params)) {
            foreach ($this->params as $name => $value) {
                $params[] = $name.' = "'.$value.'"';
            }
        }
        $params = join(', ', $params);
        $metadata = $this->app->getResourceMetadata($this);
        $class = $metadata['class'];
        $uri = array();
        foreach ($metadata['uri'] as $u) {
            $uri[] = $u[0];
        }
        $uri = join(', ', $uri);

        try {
            $priorities = $this->calculateMethodPriorities();
        } catch (Exception $e) {}
        $methods = '';
        foreach ($metadata['methods'] as $methodName => $method) {
            $methods .= "\n\t".'['.(isset($priorities[$methodName])?$priorities[$methodName]:'-').'] '.$methodName;
            foreach ($method as $itemName => $items) {
                foreach ($items as $item) {
                    $methods .= ' '.$itemName;
                    if ($item) {
                        $methods .= '="'.join(', ', $item).'"';
                    }
                }
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
