Feature: Issue 163
  In order to make sure the feature described in an issue #163 (https://github.com/peej/tonic/issues/132) is implemented
  As a Tonic developer
  I want to test the problems in the issue

  Scenario: load resources from the given directory and its subdirectories by a filename mask
    Given a resource directory "issues/Issue163" with filename mask "*.whatever" to load

    And the request URI of "/dir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

    And the request URI of "/dir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

    And the request URI of "/subdir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

    And the request URI of "/subdir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

  Scenario: load resources from the given directory and its subdirectories by a filename mask
    Given a resource directory "issues/Issue163" with filename mask "*.inc" to load

    And the request URI of "/dir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

    And the request URI of "/dir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\DirInc"

    And the request URI of "/subdir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then a "Tonic\NotFoundException" should be thrown

    And the request URI of "/subdir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\SubdirInc"

  Scenario: load resources from the given directory and its subdirectories
    Given a resource directory "issues/Issue163" to load

    And the request URI of "/dir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\DirPhp"

    And the request URI of "/dir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\DirInc"

    And the request URI of "/subdir-php"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\SubdirPhp"

    And the request URI of "/subdir-inc"
    And the request method of "GET"
    When I create an application object
    And I create a request object
    And load the resource
    Then the loaded resource should have a class of "Tonic\SubdirInc"