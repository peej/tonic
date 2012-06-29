Feature: Mounting in resources to the global URI space
  In order to create packages of resources I can group and reuse
  As a PHP developer
  I want to be able to mount a package of resources into my applications URI space

  Scenario: Mount in resources from a namespace
    Given a resource definition "mounting1" with URI "/mounting1" and namespace of "mountingNamespace"
    And the request URI of "/mountpoint1/mounting1"
    When I create an application object
    And I create a request object
    And I mount "mountingNamespace" at the URI "/mountpoint1"
    And load the resource
    Then the loaded resource should have a class of "mountingNamespace\mounting1"

  Scenario: Include a PHP file and mount the contents
    Given a resource definition "mounting3" with URI "/mounting3" and namespace annotation of "mountingNamespace"
    And the request URI of "/mountpoint3/mounting3"
    When I create an application object
    And I create a request object
    And I mount "mountingNamespace" at the URI "/mountpoint3"
    And load the resource
    Then the loaded resource should have a class of "mounting3"