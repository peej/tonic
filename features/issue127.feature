Feature: Issue 127
  In order to make sure issue #127 (https://github.com/peej/tonic/issues/127) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: No accept header
    Given an issue "Issue127"
    And the request URI of "/issue127"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "text"

  Scenario: JSON accept header
    Given an issue "Issue127"
    And the request URI of "/issue127"
    And the request method of "GET"
    And an "accept" header of 'application/json'
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "json"
