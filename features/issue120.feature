Feature: Issue 120
  In order to make sure issue #120 (https://github.com/peej/tonic/issues/120) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Go to child resource URL
    Given an issue "Issue120"
    And the request URI of "/issue120/123"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "123"

  Scenario: Go to base resource URL
    Given an issue "Issue120"
    And the request URI of "/issue120"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then a PHP warning should occur
