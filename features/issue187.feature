Feature: Issue 187
  In order to make sure issue #187 (https://github.com/peej/tonic/issues/187) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Doccomment with many stars
    Given an issue "Issue187"
    And the request URI of "/issue187"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\Issue187"
