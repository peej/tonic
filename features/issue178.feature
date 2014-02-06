Feature: Issue 178
  In order to make sure issue #178 (https://github.com/peej/tonic/issues/178) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: 
    Given a response object
    When set the response header of "Content-MD5" to "blah"
    Then the response should have the header "Content-MD5" with the value "blah"
