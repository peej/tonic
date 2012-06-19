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
    And a the class definition:
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
