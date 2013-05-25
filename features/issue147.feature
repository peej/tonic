Feature: Issue 147
  In order to make sure issue #147 (https://github.com/peej/tonic/issues/147) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Multiple calls to conditions methods
    Given an issue "Issue147"
    And the request URI of "/issue147"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "1bar"
