Feature: Issue 51
  In order to make sure issue #51 (https://github.com/peej/tonic/issues/51) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: URL template variable should only match a single URL segment
    Given a resource definition "issue51" with URI "/issue51/(?P<child>.+)" and priority of 1
    And the request URI of "/issue51/1/a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "issue51"
    And the loaded resource should have a param "child" with the value "1/a"

  Scenario: URL template variable should only match a single URL segment
    Given a resource definition "issue51c" with URI "/issue51c/(.+)" and priority of 1
    And the request URI of "/issue51c/1/a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "issue51c"
    And the loaded resource should have a param "0" with the value "1/a"

  Scenario: URL template variable should only match a single URL segment
    Given a resource definition "issue51b" with URI "/issue51b/{child}" and priority of 1
    And the request URI of "/issue51b/1a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "issue51b"
    And the loaded resource should have a param "child" with the value "1a"
