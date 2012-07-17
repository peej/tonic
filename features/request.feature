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
    And a "content-type" header of 'text/plain'
    And body data of 'woo yay'
    When I create a request object
    Then I should see a request URI of "/request1"
    And I should see a request method of "POST"
    And I should see an "accept" string of "image/jpeg,text/html,application/xhtml+xml,application/xml,*/*"
    And I should see an "accept language" string of "en-gb,en"
    And I should see an "if-none-match" string of "xyzzy,quux"
    And I should see an "if-match" string of "quux"
    And I should see a "content-type" string of "text/plain"
    And I should see body data of "woo yay"

  Scenario: Querystrings should be ignored from request URIs
    Given the request URI of "/request3?foo=bar"
    When I create a request object
    Then I should see a request URI of "/request3"

  Scenario: Request content type contains encoding data
    Given the request URI of "/request4"
    And a "content-type" header of 'application/json; charset=UTF-8'
    When I create a request object
    Then I should see a "content-type" string of "application/json"