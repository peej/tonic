Feature: Resource object
  In order to execute code
  As a PHP developer
  I want a PHP object that represents a HTTP resource
  
  Scenario: Load a non existant resource
    Given the request URI of "/resourcetest"
    When I create a request object
    Then I should fail to load the resource with response code "404" and body 'A resource matching URI "/resourcetest" was not found'
  
  Scenario: Load a resource that exists
    Given the request URI of "/resourcetest/one"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "200"
    And the response body should be 'test'

