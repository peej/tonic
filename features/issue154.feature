Feature: Issue 154
  In order to make sure issue #154 (https://github.com/peej/tonic/issues/154) is fixed
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: Resource without a URI annotation
    Given a resource file "issues/Issue154/*.php" to load
    And the request URI of "/issue154"
    When I create an application object
    And load the resource
    Then the application has a resource for class "Issue154\WithAnnotation", URI "/issue154", and priority 1
    And the application does not have a resource for class "Issue154\WithoutAnnotation"
