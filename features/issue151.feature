Feature: Issue 151
  In order to make sure issue #151 (https://github.com/peej/tonic/issues/151) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: URL without a trailing slash
    Given an issue "Issue151"
    And the request URI of "/issue151"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\Issue151NoSlash"

  Scenario: URL with a trailing slash
    Given an issue "Issue151"
    And the request URI of "/issue151/"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\Issue151Slash"
