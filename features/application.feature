Feature: A Tonic application
  In order to configure and initialise a Tonic application
  As a PHP developer
  I want a PHP object that represents an application

  Scenario: Given two resources with the same matching URI, the one with the highest priority should be loaded
    Given the request URI of "/request2"
    And a resource definition "pri1" with URI "/request2" and priority of 1
    And a resource definition "pri2" with URI "/request2" and priority of 2
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "pri2"

  Scenario: Regular expression @uri annotations should match
    Given a resource definition "regex" with URI "/regex/([0-9])/([a-z])" and priority of 1
    And the request URI of "/regex/1/a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "regex"
    And the loaded resource should have a param "0" with the value "1"
    And the loaded resource should have a param "1" with the value "a"

  Scenario: URL template expression @uri annotations should match
    Given a resource definition "urlTemplate" with URI "/urlTemplate/{number}/{letter}" and priority of 1
    And the request URI of "/urlTemplate/1/a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "urlTemplate"
    And the loaded resource should have a param "number" with the value "1"
    And the loaded resource should have a param "letter" with the value "a"

  Scenario: Rails route style expression @uri annotations should match
    Given a resource definition "rails" with URI "/rails/:number/:letter" and priority of 1
    And the request URI of "/rails/1/a"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "rails"
    And the loaded resource should have a param "number" with the value "1"
    And the loaded resource should have a param "letter" with the value "a"

  Scenario: To link resources together, I need to know programatically the URL(s) a resource will be available on
    Given a resource definition "request4" with URI "/request4/:test" and priority of 1
    When I create an application object
    Then fetching the URI for the resource "request4" with the parameter "woot" should get "/request4/woot"

  Scenario: There should be some flexibility to the whitespace around annotations
    Given a class definition:
      """
      /**
       * @uri /annotation
       *   @uri /annotation2
       *** @uri /annotation3
       * @uri      /annotation4   
       */
      class AnnotationTest extends Tonic\Resource {}
      """
    When I create an application object
    Then the resource "AnnotationTest" should have the URI "/annotation"
    And the resource "AnnotationTest" should have the URI "/annotation2"
    And the resource "AnnotationTest" should have the URI "/annotation3"
    And the resource "AnnotationTest" should have the URI "/annotation4"

  Scenario: Windows style line endings shouldn't break annotations
    Given a resource definition "windows" with URI "/windows" and windows style line endings
    And the request URI of "/windows"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "windows"
