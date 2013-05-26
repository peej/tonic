Feature: Issue 146
  In order to make sure issue #146 (https://github.com/peej/tonic/issues/146) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Getting x-blah HTTP headers
    Given an issue "Issue146"
    And the request URI of "/issue146"
    And the request method of "GET"
    And a "x-authentication" header of "foo"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "foo"
