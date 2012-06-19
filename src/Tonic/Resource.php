<?php

namespace Tonic;

class Resource {

    protected $request;
    public $params;

    function __construct(Request $request, array $urlParams) {

        $this->request = $request;
        $this->params = $urlParams;

    }

    /**
     * Execute the resource, that is, find the correct resource method to call
     * based upon the request and then call it.
     *
     * @param str methodName Optional name of method to execute, bypasing annotations, useful for debugging
     * @return Tonic\Response
     */
    function exec($methodName = NULL) {

        // get the annotation metadata for this resource
        $resourceMetadata = $this->request->getResourceMetadata($this);

        $error = new NotAcceptableException;
        $methodPriorities = array();

        if (isset($resourceMetadata['methods'])) {
            foreach ($resourceMetadata['methods'] as $key => $methodMetadata) {
                if (!$methodName || $methodName == $key) {
                    $methodPriorities[$key] = 0;
                    foreach ($methodMetadata as $conditionName => $params) {
                        if (method_exists($this, $conditionName)) {
                            try {
                                if (is_array($params)) {
                                    $condition = call_user_func_array(array($this, $conditionName), $params);
                                } else {
                                    $condition = call_user_func(array($this, $conditionName), $params);
                                }
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
            if (is_object($response)) {
                return $response;
            } elseif (is_array($response)) {
                return new Response($response[0], $response[1]);
            } elseif (is_int($response)) {
                return new Response($response);
            } elseif ($response) {
                return new Response(200, $response);
            } else {
                return new Response;
            }
        }

        throw $error;
    }

    /**
     * HTTP method condition must match request method
     * @param str $method
     */
    protected function method($method) {
        if (strtolower($this->request->method) != strtolower($method))
            throw new MethodNotAllowedException('No matching method for HTTP method "'.$this->request->method.'"');
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
        return count($this->request->accept) - $pos;
    }

}