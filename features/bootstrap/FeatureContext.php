<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Tonic\Application,
    Tonic\Request,
    Tonic\Resource,
    Tonic\Response;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{

    private $app, $request, $resource, $response, $exception, $error;

    private $createMethod = array();
    private $data;

    private $options = array();

    /**
     * @BeforeFeature
     */
    public static function setupFeature()
    {
        unset($_SERVER);
        unset($_GET);
        unset($_POST);
    }

    /**
     * @Given /^the request URI of "([^"]*)"$/
     */
    public function theRequestUriOf($uri)
    {
        $parsedUri = parse_url($uri);
        $_SERVER['REDIRECT_URL'] = $parsedUri['path'];
        if (isset($parsedUri['query'])) {
            $query = explode('&', $parsedUri['query']);
            foreach ($query as $item) {
                list($name, $value) = explode('=', $item);
                $_GET[$name] = $value;
            }
        }
        $_SERVER['SCRIPT_NAME'] = '';
    }

    /**
     * @Given /^the request method of "([^"]*)"$/
     */
    public function theRequestMethodOf($method)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
    }

    /**
     * @When /^I create an application object$/
     */
    public function iCreateAnApplicationObject()
    {
        $this->app = new Application($this->options);
    }

    /**
     * @When /^I create a request object$/
     */
    public function iCreateARequestObject()
    {
        if ($this->data) {
            $this->options['data'] = $this->data;
            $this->options['contentType'] = $_SERVER['CONTENT_TYPE'];
        }
        $this->request = new Request($this->options);
    }

    /**
     * @Then /^I should see a request URI of "([^"]*)"$/
     */
    public function iShouldSeeARequestUriOf($uri)
    {
        if ($this->request->uri != $uri) throw new Exception;
    }

    /**
     * @Then /^I should see a request method of "([^"]*)"$/
     */
    public function iShouldSeeARequestMethodOf($method)
    {
        if ($this->request->method != $method) throw new Exception;
    }

    /**
     * @Given /^an? "([^"]*)" header of ['"]([^']*)['"]$/
     */
    public function aHeaderOf($header, $value)
    {
        $headerMapping = array(
            'accept' => 'HTTP_ACCEPT',
            'accept language' => 'HTTP_ACCEPT_LANGUAGE',
            'if-none-match' => 'HTTP_IF_NONE_MATCH',
            'if-match' => 'HTTP_IF_MATCH',
            'content-type' => 'CONTENT_TYPE',
            'auth user' => 'PHP_AUTH_USER',
            'auth password' => 'PHP_AUTH_PW',
            'x-authentication' => 'HTTP_X_AUTHENTICATION'
        );
        $_SERVER[$headerMapping[$header]] = $value;
    }

    /**
     * @Given /^I should see an? "([^"]*)" string of "([^"]*)"$/
     */
    public function iShouldSeeAStringOf($header, $string)
    {
        $propertyMapping = array(
            'accept' => 'accept',
            'accept language' => 'acceptLanguage',
            'if-none-match' => 'ifNoneMatch',
            'if-match' => 'ifMatch',
            'content-type' => 'contentType'
        );
        if (is_array($this->request->$propertyMapping[$header])) {
            $value = join(',', $this->request->$propertyMapping[$header]);
        } else {
            $value = $this->request->$propertyMapping[$header];
        }
        if ($value != $string)
            throw new Exception($value);
    }

    /**
     * @Given /^body data of \'([^\']*)\'$/
     */
    public function bodyDataOf($data)
    {
        $this->data = $data;
    }

    /**
     * @Given /^I should see body data of "([^"]*)"$/
     */
    public function iShouldSeeBodyDataOf($data)
    {
        if ($this->request->data != $data)
            throw new Exception();
    }

    /**
     * @Given /^a resource definition "([^"]*)" with URI "([^"]*)" and priority of (\d+)$/
     */
    public function aResourceDefinitionWithUriAndPriorityOf($className, $uri, $priority)
    {
        $this->aResourceDefinition($className, $uri, $priority);
    }

    /**
     * @Given /^a resource definition "([^"]*)" with URI "([^"]*)" and namespace of "([^"]*)"$/
     */
    public function aResourceDefinitionWithUriAndNamespaceOf($className, $uri, $namespace)
    {
        $this->aResourceDefinition($className, $uri, 1, $namespace);
    }

    /**
     * @Given /^a resource definition "([^"]*)" with URI "([^"]*)" and namespace annotation of "([^"]*)"$/
     */
    public function aResourceDefinitionWithUriAndNamespaceAnnotationOf($className, $uri, $namespace)
    {
        $this->aResourceDefinition($className, $uri, 1, NULL, $namespace);
    }

    /**
     * @Given /^a resource definition "([^"]*)" with URI "([^"]*)" and windows style line endings$/
     */
    public function aResourceDefinitionWithUriAndWindowsStyleLineEndings($className, $uri)
    {
        $this->aResourceDefinition($className, $uri, 1, NULL, NULL, "\r\n");
    }

    private function aResourceDefinition($className, $uri, $priority = 1, $namespace = NULL, $annotationNamespace = NULL, $lineEnding = "\n")
    {
        $classDefinition = '';
        if ($namespace) $classDefinition .= 'namespace '.$namespace.';'.$lineEnding;
        $classDefinition .= '/**'.$lineEnding.
            ' * @uri '.$uri.$lineEnding.
            ' * @priority '.$priority.$lineEnding;
        if ($annotationNamespace) $classDefinition .= ' * @namespace '.$annotationNamespace.$lineEnding;
        $classDefinition .= ' */'.$lineEnding.
            'class '.$className.' extends \Tonic\Resource {'.$lineEnding;
        foreach ($this->createMethod as $methodData) {
            $classDefinition .= '    /**'.$lineEnding;
            $classDefinition .= '     * @method '.(isset($methodData['method']) ? $methodData['method'] : 'GET').$lineEnding;
            if (isset($methodData['lang'])) $classDefinition .= '     * @lang '.$methodData['lang'].$lineEnding;
            if (isset($methodData['accepts'])) $classDefinition .= '     * @accepts '.$methodData['accepts'].$lineEnding;
            if (isset($methodData['provides'])) $classDefinition .= '     * @provides '.$methodData['provides'].$lineEnding;
            $classDefinition .= $lineEnding.'     */'.$lineEnding.
                '    function '.$methodData['name'].'() {'.$lineEnding.
                '        return "'.$methodData['name'].'";'.$lineEnding.
                '    }'.$lineEnding;
        }
        $classDefinition .= '}'.$lineEnding;
        eval($classDefinition);
    }

    /**
     * @Given /^load the resource$/
     */
    public function loadTheResource()
    {
        try {
            $this->resource = $this->app->getResource($this->request);
        } catch (Tonic\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then /^the loaded resource should have a class of "([^"]*)"$/
     */
    public function theLoadedResourceShouldHaveAClassOf($className)
    {
        $loadedClassName = get_class($this->resource);
        if ($loadedClassName != $className) throw new Exception($loadedClassName.' != '.$className);
    }

    /**
     * @Given /^the loaded resource should have a param "([^"]*)" with the value "([^"]*)"$/
     */
    public function theLoadedResourceShouldHaveAParamWithTheValue($name, $value)
    {
        if (!isset($this->resource->params[$name])) throw new Exception('Param '.$name.' not found');
        if ($this->resource->params[$name] != $value) throw new Exception('Param '.$name.' does not equal '.$value);
    }

    /**
     * @Given /^execute the resource$/
     */
    public function executeTheResource()
    {
        set_error_handler(function ($level, $message, $file, $line) {
            throw new ErrorException($message, $level);
        });
        try {
            if ($this->resource) {
                $this->response = $this->resource->exec();
            } else {
                throw new Exception('Resource not loaded');
            }
        } catch (Tonic\Exception $e) {
            $this->exception = $e;
        } catch (ErrorException $e) {
            $this->exception = $e;
            $this->error = $e->getCode();
        }
        restore_error_handler();
    }

    /**
     * @Then /^response should be "([^"]*)"$/
     */
    public function responseShouldBe($responseString)
    {
        if (!$this->response) throw new Exception('Response not loaded due to '.$this->exception);
        if ($this->response->body != $responseString) throw new Exception('The response body is: "'.$this->response->body.'"');
    }

    /**
     * @Given /^a "([^"]*)" resource method "([^"]*)"$/
     */
    public function aResourceMethod($method, $name)
    {
        $this->createMethod[] = array(
            'method' => $method,
            'name' => $name
        );
    }

    /**
     * @Given /^a "([^"]*)" resource method "([^"]*)" that provides "([^"]*)"$/
     */
    public function aResourceMethodThatProvides($method, $name, $provides)
    {
        $this->createMethod[] = array(
            'method' => $method,
            'name' => $name,
            'provides' => $provides
        );
    }

    /**
     * @Given /^a "([^"]*)" resource method "([^"]*)" that accepts "([^"]*)"$/
     */
    public function aResourceMethodThatAccepts($method, $name, $accepts)
    {
        $this->createMethod[] = array(
            'method' => $method,
            'name' => $name,
            'accepts' => $accepts
        );
    }

    /**
     * @Given /^the request content type of "([^"]*)"$/
     */
    public function theRequestContentTypeOf($contentType)
    {
        $_SERVER['CONTENT_TYPE'] = $contentType;
    }

    /**
     * @Given /^the request data of "([^"]*)"$/
     */
    public function theRequestDataOf($data)
    {
        $this->data = $data;
    }

    /**
     * @Then /^a "([^"]*)" should be thrown$/
     */
    public function aShouldBeThrown($exception)
    {
        if ($exception != get_class($this->exception)) throw new Exception($this->exception->getMessage());
    }

    /**
     * @Then /^a PHP warning should occur$/
     */
    public function aPhpWarningShouldOccur()
    {
        if ($this->error != E_WARNING) throw new Exception('No PHP warning');
    }

    /**
     * @Then /^a PHP notice should occur$/
     */
    public function aPhpNoticeShouldOccur()
    {
        if ($this->error != E_NOTICE) throw new Exception('No PHP notice');
    }

    /**
     * @Given /^I mount "([^"]*)" at the URI "([^"]*)"$/
     */
    public function iMountAtTheUri($className, $uriSpace)
    {
        $this->app->mount($className, $uriSpace);
    }

    /**
     * @Given /^a class definition:$/
     */
    public function aClassDefinition(PyStringNode $string)
    {
        eval($string);
    }

    /**
     * @Given /^I set the request option "([^"]*)" to:$/
     */
    public function iSetTheRequestOptionTo($option, PyStringNode $json)
    {
        $this->options[$option] = json_decode($json, TRUE);
    }

    /**
     * @Given /^I supply an empty cache object$/
     */
    public function iSupplyAnEmptyCacheObject()
    {
        $this->options['cache'] = new MockMetadataCache();
    }

    /**
     * @Given /^a resource file "([^"]*)" to load$/
     */
    public function aResourceFileToLoad($filename)
    {
        $this->options['load'][] = $filename;
    }

    /**
     * @Given /^the cache object should contain "([^"]*)" "([^"]*)"$/
     */
    public function theCacheObjectShouldContain($className, $methodName)
    {
        if (!$this->options['cache']->contains($className, $methodName)) throw new Exception;
    }

    /**
     * @Given /^a cache object containing a class "([^"]*)" with a URL of "([^"]*)" and a method "([^"]*)" responding to HTTP "([^"]*)"$/
     */
    public function aCacheObjectContainingAClassWithAUrlOfAndAMethodRespondingToHttp($className, $uri, $methodName, $method)
    {
        $this->iSupplyAnEmptyCacheObject();
        $this->options['cache']->save(array(
            $className => array(
                'class' => $className,
                'uri' => $uri,
                'methods' => array(
                    $methodName => array(
                        'method' => array(
                            $method
                        )
                    )
                )
            )
        ));
    }

    /**
     * @Then /^the loaded resource "([^"]*)" should respond with the method "([^"]*)"$/
     */
    public function theLoadedResourceShouldRespondToWithTheMethod($className, $methodName)
    {
        $metadata = $this->app->getResourceMetadata($className);
        if (!isset($metadata['methods'][$methodName])) throw new Exception;
    }

    /**
     * @Then /^fetching the URI for the resource "([^"]*)" with the parameter "([^"]*)" should get "([^"]*)"$/
     */
    public function fetchingTheUriForTheResourceShouldGet($className, $params, $url)
    {
        $params = explode(':', $params);
        if ($this->app->uri($className, $params) != $url)
            throw new Exception($this->app->uri($className, $params).' != '.$url);
    }

    /**
     * @Given /^a "([^"]*)" resource method "([^"]*)" with lang "([^"]*)"$/
     */
    public function aResourceMethodWithLang($method, $name, $language)
    {
        $this->createMethod[] = array(
            'method' => $method,
            'name' => $name,
            'lang' => $language
        );
    }

    /**
     * @Then /^the resource "([^"]*)" should have the URI "([^"]*)"$/
     */
    public function theResourceShouldHaveTheURI($resourceName, $url)
    {
        $found = FALSE;
        $metadata = $this->app->getResourceMetadata($resourceName);
        foreach ($metadata['uri'] as $uri) {
            if ($uri[0] == $url) {
                $found = TRUE;
                break;
            }
        }
        if (!$found) throw new Exception;
    }

    /**
     * @Then /^the resource "([^"]*)" should have the condition "([^"]*)" with the parameters "([^"]*)"$/
     */
    public function theResourceShouldHaveTheConditionWithTheParameters($className, $conditionName, $parameters)
    {
        $metadata = $this->app->getResourceMetadata($className);
        if ($parameters) {
            if ($parameters != join(',', $metadata['methods']['test'][$conditionName][0])) throw new Exception('Condition method not found');
            
            $resource = new $className($this->app, new Request, array());
            $condition = call_user_func_array(array($resource, $conditionName), explode(',', $parameters));
            if ($condition != explode(',', $parameters)) throw new Exception('Condition parameters not returned');
        } else {
            if (!isset($metadata['methods']['test'][$conditionName])) throw new Exception('Condition method not found');
        }
    }

    /**
     * @Given /^an issue "([^"]*)"$/
     */
    public function anIssue($issue)
    {
        require_once dirname(__FILE__).'/../../issues/'.$issue.'.php';
    }

    /**
     * @Given /^the method priority for "([^"]*)" should be "([^"]*)"$/
     */
    public function theMethodPriorityForShouldBe($methodName, $value)
    {
        preg_match('/[\[ ]([0-9-]+)\] '.$methodName.' /', (string)$this->resource, $matches);
        if (!$matches)
            throw new Exception('"'.$methodName.'" not found');
        if ($matches[1] != $value)
            throw new Exception('"'.$methodName.'" has the priortiy of '.$matches[1]);
    }

    /**
     * @Then /^the response should have the header "([^"]*)" with the value "([^"]*)"$/
     */
    public function theResponseShouldHaveTheHeaderWithTheValue($name, $value)
    {
        if ($this->response->headers[$name] != $value) throw new Exception('Response header '.$name.' does not equal '.$value);
    }

    /**
     * @Then /^output the "([^"]*)"$/
     */
    public function outputThe($thing)
    {
        echo $this->$thing;
    }


}