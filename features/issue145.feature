Feature: Issue 145
  In order to make sure issue #145 (https://github.com/peej/tonic/issues/145) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: GET request should throw authentication exception
    Given an issue "Issue145"
    And the request URI of "/issue145"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\UnauthorizedException" should be thrown

  Scenario: PUT request should throw authentication exception
    Given an issue "Issue145"
    And the request URI of "/issue145"
    And the request method of "PUT"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a "Tonic\UnauthorizedException" should be thrown
