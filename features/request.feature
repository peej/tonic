Feature: HTTP request object
  In order to handle a request
  As a PHP developer
  I want a PHP object that represents the state of the incoming HTTP request
  
  Scenario: Have access to the request URI
    Given the request URI of "/something/otherthing"
    When I create a request object
    Then I should see a request URI of "/something/otherthing"
    
  Scenario: Have access to the HTTP request method
    Given the request method of "GET"
    When I create a request object
    Then I should see a request method of "GET"
    
  Scenario: Receive the request querystring
    Given the request URI of "/something/otherthing"
    And the request method of "GET"
    And the querystring is "?foo=bar"
    When I create a request object
    Then I should see a querystring of "foo=bar"
    
  Scenario: Have access to the HTTP request method
    Given the request method of "post"
    And the request data of "some data"
    When I create a request object
    Then I should see a request method of "POST"
    And I should see the request data "some data"
    
  Scenario: Have access to the HTTP request data
    Given the request method of "PUT"
    And the request data of "some data"
    When I create a request object
    Then I should see a request method of "PUT"
    And I should see the request data "some data"
    
  Scenario: Have bare request URI content negotiated correctly
    Given the request URI of "/requesttest/one/two"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two"
    
  Scenario: Have extension request URI content negotiated correctly
    Given the request URI of "/requesttest/one/two.html"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.html,/requesttest/one/two"
    
  Scenario: Have bare request URI with accept header content negotiated correctly
    Given the request URI of "/requesttest/one/two"
    And the accept header of "image/png;q=0.5,text/html"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two"
    
  Scenario: Have extension request URI with accept header content negotiated correctly
    Given the request URI of "/requesttest/one/two.html"
    And the accept header of "image/png;q=0.5,text/html"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.html,/requesttest/one/two"
    
  Scenario: Have bare request URI with language accept header content negotiated correctly
    Given the request URI of "/requesttest/one/two"
    And the language accept header of "fr;q=0.5,en"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    
  Scenario: Have extension request URI with language accept header content negotiated correctly
    Given the request URI of "/requesttest/one/two.html"
    And the language accept header of "fr;q=0.5,en"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html.en,/requesttest/one/two.html.fr,/requesttest/one/two.html,/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    
  Scenario: Have bare request URI with accept and language header content negotiated correctly
    Given the request URI of "/requesttest/one/two"
    And the accept header of "image/png;q=0.5,text/html"
    And the language accept header of "fr;q=0.5,en"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html.en,/requesttest/one/two.html.fr,/requesttest/one/two.html,/requesttest/one/two.png.en,/requesttest/one/two.png.fr,/requesttest/one/two.png,/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    
  Scenario: Have extension request URI with accept and language header content negotiated correctly
    Given the request URI of "/requesttest/one/two.html"
    And the accept header of "image/png;q=0.5,text/html"
    And the language accept header of "fr;q=0.5,en"
    When I create a request object
    Then I should see a request URI of "/requesttest/one/two"
    And I should see a negotiated URI of "/requesttest/one/two.html.en,/requesttest/one/two.html.fr,/requesttest/one/two.html,/requesttest/one/two.png.html,/requesttest/one/two.png.en,/requesttest/one/two.png.fr,/requesttest/one/two.png,/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    And I should see a format negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.png,/requesttest/one/two"
    And I should see a language negotiated URI of "/requesttest/one/two.html,/requesttest/one/two.en,/requesttest/one/two.fr,/requesttest/one/two"
    
  Scenario: Have if match header
    Given an if match header of '123123'
    When I create a request object
    Then I should see an if match header of "123123"
    And if match should match "123123"
    
  Scenario: Have if match header in quotes
    Given an if match header of '"123123"'
    When I create a request object
    Then I should see an if match header of "123123"
    And if match should match "123123"
    
  Scenario: Have multiple if match headers in quotes
    Given an if match header of '"123123","456456"'
    When I create a request object
    Then I should see an if match header of "123123,456456"
    And if match should match "123123"
    And if match should match "456456"
    
  Scenario: Have star if match header
    Given an if match header of '*'
    When I create a request object
    Then I should see an if match header of "*"
    And if match should match "123123"
    And if match should match "123456"
    
  Scenario: Have if none match header
    Given an if none match header of '123123'
    When I create a request object
    Then I should see an if none match header of "123123"
    And if none match should match "123123"
    
  Scenario: Have multiple if none match headers in quotes
    Given an if none match header of '"123123","456456"'
    When I create a request object
    Then I should see an if none match header of "123123,456456"
    And if none match should match "123123"
    And if none match should match "456456"
  
  Scenario: Have star if none match header
    Given an if none match header of '*'
    When I create a request object
    Then I should see an if none match header of "*"
    And if none match should not match "123123"
    And if none match should not match "123456"
    
  Scenario: Load a non-existent resource
    Given the request URI of "/three"
    When I create a request object
    And I load the resource
    Then I should have a response of type "NoResource"
    
  Scenario: Load a resource
    Given the request URI of "/requesttest/one"
    When I create a request object
    And I load the resource
    Then I should have a response of type "NewResource"
    
  Scenario: Load a child resource
    Given the request URI of "/requesttest/one/two"
    When I create a request object
    And I load the resource
    Then I should have a response of type "ChildResource"
    
  Scenario: Load a resource with a regex match URI
    Given the request URI of "/requesttest/three/something/four"
    When I create a request object
    And I load the resource
    Then I should have a response of type "NewResource"
  
  Scenario: Load a non-existent resource with a new 404 resource class
    Given the request URI of "/three"
    And a 404 resource classname of "NewNoResource"
    When I create a request object
    And I load the resource
    Then I should have a response of type "NewNoResource"
  
  Scenario: Resource data loading
    Given the request URI of "/requesttest/one/two"
    When I create a request object
    Then I should see resource "namespace" metadata of "Tonic/Tests"
    And I should see resource "class" metadata of "ChildResource"
    And I should see resource "priority" metadata of "0"
    
  Scenario: Mounting in a namespace to a URI
    Given the request URI of "/foo/bar/requesttest/one"
    And a mounting of "Tonic/Tests" to "/foo/bar"
    When I create a request object
    And I load the resource
    Then I should have a response of type "NewResource"
