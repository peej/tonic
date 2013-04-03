Feature: Issue 133
  In order to make sure issue #133 (https://github.com/peej/tonic/issues/133) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Wildcard in load statement no longer works
    Given a resource file "issues/b?d[ch?rs]/*.php" to load
    And the request URI of "/issue132"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "loaded"