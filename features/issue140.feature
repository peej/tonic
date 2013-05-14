Feature: Issue 140
  In order to make sure issue #140 (https://github.com/peej/tonic/issues/140) is fixed
  As a Tonic developer
  I want to test the problems in the issue
  
  Scenario: Resource startup method should get called
    Given an issue "Issue140"
    And the request URI of "/issue140"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "setup"
