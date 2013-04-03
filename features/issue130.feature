Feature: Issue 130
  In order to make sure issue #130 (https://github.com/peej/tonic/issues/130) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: URI parameters with '.' in them get garbled
    Given an issue "Issue130"
    And the request URI of "/issue130/abc.123"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "abc.123"

  Scenario: Mimetype shortcodes should get removed from URL and turned into accept header
    Given an issue "Issue130"
    And the request URI of "/issue130/abc.en.123.txt"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    And execute the resource
    Then response should be "abc.123"
    And I should see an "accept" string of "text/plain"
    And I should see an "accept language" string of "en"