Feature: HTTP request object
  In order to handle a request
  As a PHP developer
  I want a PHP object that represents the state of the incoming HTTP request
  
  Scenario: Have access to the request data
    Given the request URI of "/request1.jpg"
    And the request method of "POST"
    And an "accept" header of 'text/html,application/xml;q=0.9,application/xhtml+xml,*/*;q=0.8'
    And an "accept language" header of 'en;q=0.8,en-GB'
    And an "if-none-match" header of '"xyzzy", "quux"'
    And an "if-match" header of '"quux"'
    When I create a request object
    Then I should see a request URI of "/request1"
    And I should see a request method of "POST"
    And I should see an "accept" string of "image/jpeg,text/html,application/xhtml+xml,application/xml,*/*"
    And I should see an "accept language" string of "en-gb,en"
    And I should see an "if-none-match" string of "xyzzy,quux"
    And I should see an "if-match" string of "quux"

  Scenario: Given two resources with the same matching URI, the one with the highest priority should be loaded
    Given the request URI of "/request2"
    And a resource definition "pri1" with URI "/request2" and priority of 1
    And a resource definition "pri2" with URI "/request2" and priority of 2
    When I create a request object
    And load the resource
    Then the loaded resource should have a class of "pri2"

  Scenario: Regular expression @uri annotations should match
    Given a resource definition "regex" with URI "/regex/([0-9])/([a-z])" and priority of 1
    And the request URI of "/regex/1/a"
    When I create a request object
    And load the resource
    Then the loaded resource should have a class of "regex"
    And the loaded resource should have a param "0" with the value "1"
    And the loaded resource should have a param "1" with the value "a"

  Scenario: URL template expression @uri annotations should match
    Given a resource definition "urlTemplate" with URI "/urlTemplate/{number}/{letter}" and priority of 1
    And the request URI of "/urlTemplate/1/a"
    When I create a request object
    And load the resource
    Then the loaded resource should have a class of "urlTemplate"
    And the loaded resource should have a param "number" with the value "1"
    And the loaded resource should have a param "letter" with the value "a"

  Scenario: Rails route style expression @uri annotations should match
    Given a resource definition "rails" with URI "/rails/:number/:letter" and priority of 1
    And the request URI of "/rails/1/a"
    When I create a request object
    And load the resource
    Then the loaded resource should have a class of "rails"
    And the loaded resource should have a param "number" with the value "1"
    And the loaded resource should have a param "letter" with the value "a"

  Scenario: Querystrings should be ignored from request URIs
    Given the request URI of "/request3?foo=bar"
    When I create a request object
    Then I should see a request URI of "/request3"
