Feature: Issue 136
  In order to make sure issue #136 (https://github.com/peej/tonic/issues/136) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Extending a parent class should inherit method annotations
    Given an issue "Issue136"
    And the request URI of "/issue136"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "get call me maybe?"

  Scenario: Implementing an interface should inherit method annotations
    Given an issue "Issue136"
    And the request URI of "/issue136"
    And the request method of "POST"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "post call me super!"

  Scenario: Extending a parent class should inherit method annotations but override conflicts
    Given an issue "Issue136"
    And the request URI of "/issue136"
    And the request method of "PUT"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "override"
