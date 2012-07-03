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

    private $app, $request, $resource, $response, $exception;

    private $createMethod = array();
    private $data;

    private $options = array();

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
     * @Given /^an "([^"]*)" header of '([^']*)'$/
     */
    public function anHeaderOf($header, $value)
    {
        $headerMapping = array(
            'accept' => 'HTTP_ACCEPT',
            'accept language' => 'HTTP_ACCEPT_LANGUAGE',
            'if-none-match' => 'HTTP_IF_NONE_MATCH',
            'if-match' => 'HTTP_IF_MATCH',
        );
        $_SERVER[$headerMapping[$header]] = $value;
    }

    /**
     * @Given /^I should see an "([^"]*)" string of "([^"]*)"$/
     */
    public function iShouldSeeAnAcceptStringOf($header, $string)
    {
        $propertyMapping = array(
            'accept' => 'accept',
            'accept language' => 'acceptLang',
            'if-none-match' => 'ifNoneMatch',
            'if-match' => 'ifMatch',
        );
        if (join(',', $this->request->$propertyMapping[$header]) != $string)
            throw new Exception(join(',', $this->request->$propertyMapping[$header]));
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

    private function aResourceDefinition($className, $uri, $priority = 1, $namespace = NULL, $annotationNamespace = NULL)
    {
        $classDefinition = '';
        if ($namespace) $classDefinition .= 'namespace '.$namespace.";\n";
        $classDefinition .= '
/**
 * @uri '.$uri.'
 * @priority '.$priority.'
';
        if ($annotationNamespace) $classDefinition .= ' * @namespace '.$annotationNamespace."\n";
        $classDefinition .= ' */
class '.$className.' extends \Tonic\Resource {
';
        foreach ($this->createMethod as $methodData) {
            $classDefinition .= '
    /**'."\n";
            $classDefinition .= '     * @method '.(isset($methodData['method']) ? $methodData['method'] : 'GET')."\n";
            if (isset($methodData['accepts'])) $classDefinition .= '     * @accepts '.$methodData['accepts']."\n";
            if (isset($methodData['provides'])) $classDefinition .= '     * @provides '.$methodData['provides']."\n";
            $classDefinition .= '
     */
    function '.$methodData['name'].'() {
        return "'.$methodData['name'].'";
    }
';
        }
        $classDefinition .= '
}
';
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
            $this->exception = get_class($e);
        }
    }

    /**
     * @Then /^the loaded resource should have a class of "([^"]*)"$/
     */
    public function theLoadedResourceShouldHaveAClassOf($className)
    {
        $loadedClassName = get_class($this->resource);
        if ($loadedClassName != $className) throw new Exception($loadedClassName);
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
        try {
            if ($this->resource) {
                $this->response = $this->resource->exec();
            } else {
                throw new Exception('Resource not loaded');
            }
        } catch (Tonic\Exception $e) {
            $this->exception = get_class($e);
        }
    }

    /**
     * @Then /^response should be "([^"]*)"$/
     */
    public function responseShouldBe($responseString)
    {
        if (!$this->response) throw new Exception('Response not loaded due to '.$this->exception);
        if ($this->response->body != $responseString) throw new Exception($this->response->body);
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
        if ($exception != $this->exception) throw new Exception($this->exception);
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
                'uri' => '|^'.$uri.'$|',
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
        if ($this->app->uri($className, $params) != $url) throw new Exception;
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
            if ($uri[0] == '|^'.$url.'$|') {
                $found = TRUE;
                break;
            }
        }
        if (!$found) throw new Exception;
    }

}