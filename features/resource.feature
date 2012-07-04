Feature: HTTP resource object
  In order to talk HTTP
  As a PHP developer
  I want a PHP object that represents a HTTP resource
  
  Scenario: Execute the correct method for the given HTTP request
    Given a "GET" resource method "method1" that provides "text/html"
    And a "GET" resource method "method2" that provides "application/json"
    And a resource definition "resource1" with URI "/resource1" and priority of 1
    And the request URI of "/resource1.json"
    And an "accept" header of 'text/html'
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "method2"

  Scenario: Execute the correct method for the given HTTP request
    Given a "POST" resource method "method1" that accepts "text/html"
    And a "POST" resource method "method2" that accepts "application/json"
    And a resource definition "resource2" with URI "/resource2" and priority of 1
    And the request URI of "/resource2"
    And the request method of "POST"
    And the request content type of "application/json"
    And the request data of "xyzzy"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "method2"

  Scenario: Execute the correct method for the given HTTP request
    Given a "GET" resource method "method1" with lang "en"
    And a "GET" resource method "method2" with lang "nl"
    And a resource definition "resourceLang" with URI "/resourceLang" and priority of 1
    And the request URI of "/resourceLang.json"
    And an "accept language" header of 'nl-nl, nl'
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "method2"

  Scenario: No resource found
    Given the request URI of "/resourceDoesNotExist"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

  Scenario: No acceptable method is found
    Given a "GET" resource method "method1" that provides "text/plain"
    And a resource definition "resource3" with URI "/resource3" and priority of 1
    And the request URI of "/resource3"
    And the request method of "GET"
    And an "accept" header of 'text/html'
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\NotAcceptableException" should be thrown

  Scenario: Incorrect media provided to method
    Given a "POST" resource method "method1" that accepts "text/plain"
    And a resource definition "resource4" with URI "/resource4" and priority of 1
    And the request URI of "/resource4"
    And the request method of "POST"
    And the request content type of "application/json"
    And the request data of "xyzzy"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\UnsupportedMediaTypeException" should be thrown

  Scenario: Method not allowed
    Given a "GET" resource method "method1"
    And a resource definition "resource5" with URI "/resource5" and priority of 1
    And the request URI of "/resource5"
    And the request method of "PUT"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\MethodNotAllowedException" should be thrown

  Scenario: When extending a resource, parent method annotations should be available to child
    Given a "GET" resource method "method1" that provides "text/html"
    And a resource definition "parent1" with URI "/parent1" and priority of 1
    And a resource definition "child1" with URI "/child1" and priority of 1
    When I create an application object
    And I create a request object
    Then the loaded resource "child1" should respond with the method "method1"