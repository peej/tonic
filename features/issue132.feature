Feature: Issue 132
  In order to make sure issue #132 (https://github.com/peej/tonic/issues/132) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: loadResourceFiles uses glob() which return emtpy array
    Given a resource file "issues/bad[chars]/Issue[132].php" to load
    And the request URI of "/issue132"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "loaded"