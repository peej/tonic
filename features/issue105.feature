Feature: Issue 105
  In order to make sure issue #105 (https://github.com/peej/tonic/issues/105) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Go to base URL without auth
    Given an issue "Issue105"
    And the request URI of "/issue105"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\UnauthorizedException" should be thrown

  Scenario: Go to user URL without auth
    Given an issue "Issue105"
    And the request URI of "/issue105/username"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\UnauthorizedException" should be thrown

  Scenario: Go to base URL with auth
    Given an issue "Issue105"
    And the request URI of "/issue105"
    And the request method of "GET"
    And a "auth user" header of "username"
    And a "auth password" header of "password"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "Hello Papa"

  Scenario: Go to user URL with auth
    Given an issue "Issue105"
    And the request URI of "/issue105/username"
    And the request method of "GET"
    And a "auth user" header of "username"
    And a "auth password" header of "password"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "Hello username"

  Scenario: Go to self URL
    Given an issue "Issue105"
    And the request URI of "/issue105/self"
    And the request method of "GET"
    And a "auth user" header of "username"
    And a "auth password" header of "password"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "Hello username"
