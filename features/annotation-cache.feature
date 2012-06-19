Feature: Caching of annotation information
  In order to increase the performance of the system and allow opcode caching
  As a systems administrator
  I want to be able cache the resource class metadata on my production server

  Scenario: Load a resource and execute a method via preloaded resource metadata
    Given the request URI of "/cache"
    And I set the request option "resources" to:
      """
      {
        "Cache": {
          "uri": "|^/cache$|",
          "methods": {
            "method1": {
              "method": "GET"
            }
          }
        }
      }
      """
    When I create a request object
    And a class definition:
      """
      class Cache extends Tonic\Resource {
        function method1() {
          return 'cache';
        }
      }
      """
    And load the resource
    And execute the resource
    Then the loaded resource should have a class of "Cache"
    And response should be "cache"

  Scenario: Store resource metadata into a cache
    Given a "GET" resource method "method2" that provides "text/html"
    And a resource definition "Cache2" with URI "/cache2" and priority of 1
    And the request URI of "/cache2.html"
    And I supply an empty cache object
    When I create a request object
    And load the resource
    And execute the resource
    Then response should be "method2"
    And the cache object should contain "Cache2" "method2"
