Feature: Issue 122
  In order to make sure issue #122 (https://github.com/peej/tonic/issues/122) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Non standard @method annotation
    Given an issue "Issue122"
    And the request URI of "/issue122"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "get"
