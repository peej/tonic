<?php

namespace Tonic;

/**
 * Model a HTTP resource
 */
class Resource
{
    protected $app, $request;
    public $params;
    private $currentMethodName = '*';
    protected $before = array(), $after = array();

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
        $this->params = $request->getParams();
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
     * Check if a URL parameter exists
     * @param  str $name Name of the parameter
     * @return str
     */
    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Get the method name of the best matching resource method.
     *
     * @param  str[] $resourceMetadata
     * @return str
     */
    private function calculateMethodPriorities($resourceMetadata)
    {
        $methodPriorities = array();

        foreach ($resourceMetadata->getMethods() as $key => $methodMetadata) {
            if ($key != 'setup') {
                foreach ($methodMetadata->getConditions() as $conditionName => $conditionValues) {
                    if (method_exists($this, $conditionName)) {
                        $this->currentMethodName = $key;
                        $success = false;
                        $error = null;
                        if (!$conditionValues) { // empty condition, process once for null value
                            $conditionValues[] = null;
                        }
                        foreach ($conditionValues as $params) {
                            if (!isset($methodPriorities[$key]['value'])) {
                                $methodPriorities[$key]['value'] = 0;
                            }
                            try {
                                $condition = call_user_func_array(array($this, $conditionName), str_getcsv($params, ' '));
                                if ($condition === true) $condition = 1;
                                if (is_numeric($condition)) {
                                    $methodPriorities[$key]['value'] += $condition;
                                } elseif ($condition) {
                                    $methodPriorities[$key]['value']++;
                                    $methodPriorities[$key]['response'] = $condition;
                                }
                                $success = true;
                            } catch (ConditionException $e) {
                                unset($methodPriorities[$key]);
                                break 2;
                            } catch (Exception $e) {
                                $error = $e;
                            }
                        }
                        if (!$success && $error) {
                            $methodPriorities[$key]['exception'] = $error;
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

        return $methodPriorities;
    }

    /**
     * Run resource setup actions before executing the matched resource method.
     *
     * Condition annotations applied to the setup() method will be applied to all
     * resource methods within the class.
     */
    protected function setup() {}

    /**
     * Execute the resource, that is, find the correct resource method to call
     * based upon the request and then call it.
     *
     * @return Tonic\Response
     */
    public function exec()
    {
        $this->setup();

        // get the annotation metadata for this resource
        $resourceMetadata = $this->app->getResourceMetadata($this);

        $methodPriorities = $this->calculateMethodPriorities($resourceMetadata);

        $methodName = null;
        $bestMatch = -2;
        foreach ($methodPriorities as $name => $priority) {
            if ($priority['value'] > $bestMatch) {
                $bestMatch = $priority['value'];
                $methodName = $name;
            }
        }

        if (!$methodName) {
            throw new Exception('No method matches request method');

        } elseif (isset($methodPriorities[$methodName]['response'])) {
            $response = Response::create($methodPriorities[$methodName]['response']);

        } elseif (isset($methodPriorities[$methodName]['exception'])) {
            throw $methodPriorities[$methodName]['exception'];

        } else {
            foreach (array('*', $methodName) as $mn) {
                if (isset($this->before[$mn])) {
                    foreach ($this->before[$mn] as $action) {
                        call_user_func($action, $this->request, $mn);
                    }
                }
            }
            $response = Response::create(call_user_func_array(array($this, $methodName), $this->params));
            foreach (array('*', $methodName) as $mn) {
                if (isset($this->after[$mn])) {
                    foreach ($this->after[$mn] as $action) {
                        call_user_func($action, $response, $mn);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Show a options HTTP method response
     *
     * @method OPTIONS
     */
    public function options()
    {
        $options = array();

        $resourceMetadata = $this->app->getResourceMetadata($this);

        foreach ($resourceMetadata->getMethods() as $method => $methodMetadata) {
            $options[] = strtoupper($method);
        }

        return new Response(200, $options, array(
            'Allow' => implode(',', $options)
        ));
    }

    /**
     * Add a function to execute on the request before the resource method is called
     *
     * @param callable $action The function to execute
     */
    protected function before($action)
    {
        if (is_callable($action)) {
            $this->before[$this->currentMethodName][] = $action;
        }
    }

    /**
     * Add a function to execute on the response after the resource method is called
     *
     * @param callable $action The function to execute
     */
    protected function after($action)
    {
        if (is_callable($action)) {
            $this->after[$this->currentMethodName][] = $action;
        }
    }

    /**
     * HTTP method condition must match request method
     * @param str $method
     */
    protected function method($method)
    {
        if (strtolower($this->request->getMethod()) != strtolower($method)) {
            throw new MethodNotAllowedException('No matching method for HTTP method "'.$this->request->getMethod().'"');
        }
        return true;
    }

    /**
     * Higher priority method takes precident over other matches
     * @param int $priority
     */
    protected function priority($priority)
    {
        return intval($priority);
    }

    /**
     * Accepts condition mimetype must match request content type
     * @param str $mimetype
     */
    protected function accepts($mimetype)
    {
        if (strtolower($this->request->getContentType()) != strtolower($mimetype)) {
            throw new UnsupportedMediaTypeException('No matching method for content type "'.$this->request->getContentType().'"');
        }

        return true;
    }

    /**
     * Provides condition mimetype must be in request accept array, returns a number
     * based on the priority of the match.
     * @param  str $mimetype
     * @return int
     */
    protected function provides($mimetype)
    {
        if (count($this->request->getAccept()) == 0) return 0;
        $pos = array_search($mimetype, $this->request->getAccept());
        if ($pos === FALSE) {
            if (in_array('*/*', $this->request->getAccept())) {
                return 0;
            } else {
                throw new NotAcceptableException('No matching method for response type "'.join(', ', $this->request->getAccept()).'"');
            }
        } else {
            $this->after(function ($response) use ($mimetype) {
                $response->contentType = $mimetype;
            });

            return count($this->request->getAccept()) - $pos;
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
        $pos = array_search($language, $this->request->getAcceptLanguage());
        if ($pos === FALSE) {
            throw new NotAcceptableException('No matching method for response type "'.join(', ', $this->request->getAcceptLanguage()).'"');
        }
        return count($this->request->getAcceptLanguage()) - $pos;
    }

    /**
     * Set cache control header on response
     * @param int length Number of seconds to cache the response for
     */
    protected function cache($length)
    {
        $this->after(function ($response) use ($length) {
            if ($length == 0) {
                $response->cacheControl = 'no-cache';
            } else {
                $response->cacheControl = 'max-age='.$length.', must-revalidate';
            }
        });
    }

    public function allowedMethods()
    {
        $metadata = $this->app->getResourceMetadata($this);
        $allowedMethods = array();
        foreach ($metadata['methods'] as $method => $properties) {
            foreach ($properties['method'] as $method) {
                $allowedMethods[] = strtoupper($method[0]);
            }
        }
        return array_values(array_unique($allowedMethods));
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
        $class = $metadata->getClass();
        $uri = array();
        foreach ($metadata->getUri() as $u) {
            $uri[] = $u[0];
        }
        $uri = join(', ', $uri);

        try {
            $priorities = $this->calculateMethodPriorities($metadata);
        } catch (Exception $e) {}
        $methods = '';
        foreach ($metadata->getMethods() as $methodName => $method) {
            if ($methodName != 'setup') {
                $methods .= "\n\t".'[';
                if (isset($priorities[$methodName])) {
                    if (isset($priorities[$methodName]['exception'])) {
                        $methods .= get_class($priorities[$methodName]['exception']).' ';
                    }
                    $methods .= $priorities[$methodName]['value'];
                } else {
                    $methods .= '-';
                }
                $methods .= '] '.$methodName;
                if ($metadata->getMethod('setup')) {
                    $method += $metadata->getMethod('setup');
                }
                foreach ($method as $itemName => $items) {
                    foreach ($items as $item) {
                        $methods .= ' '.$itemName;
                        if ($item) {
                            $methods .= '="'.join(', ', $item).'"';
                        }
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
