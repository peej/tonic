Feature: Issue 134
  In order to make sure issue #134 (https://github.com/peej/tonic/issues/134) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Adding a no-cache condition should set the correct cache header
    Given an issue "Issue134"
    And the request URI of "/issue134/nocache"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then the response should have the header "cache-control" with the value "no-cache"

  Scenario: Adding a cache life condition should set the correct cache header
    Given an issue "Issue134"
    And the request URI of "/issue134/cache"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then the response should have the header "cache-control" with the value "max-age=10, must-revalidate"