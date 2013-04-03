Feature: Issue 107
  In order to make sure issue #107 (https://github.com/peej/tonic/issues/107) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Go to base URL
    Given an issue "Issue107"
    And the request URI of "/issue107"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "get default"

  Scenario: Go to ID URL
    Given an issue "Issue107"
    And the request URI of "/issue107/id"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "get id"

  Scenario: Go to self URL
    Given an issue "Issue107"
    And the request URI of "/issue107/self"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "get SELF"