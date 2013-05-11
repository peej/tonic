Feature: Issue 142
  In order to make sure issue #142 (https://github.com/peej/tonic/issues/142) is fixed
  As a Tonic developer
  I want to test the problems in the issue
  
  Scenario: Accept header that contains spaces
    Given the request URI of "/issue142"
    And an "accept" header of 'text/html, image/gif;q=0.5, image/jpeg ; q=.8, */*; q=.2'
    When I create a request object
    Then I should see an "accept" string of "text/html,image/jpeg,image/gif,*/*"
