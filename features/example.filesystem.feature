Feature: Filesystem example
  In order to demonstrate who you'd use Tonic to read/write to the filesystem
  As a PHP developer
  I want an filesystem resource that exposes files on the filesystem
  
  Scenario: Read a file
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTest"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "200"
    And the response body should be 'test'

  Scenario: Read default document
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "200"
    And the response body should be 'test'

  Scenario: Try to read a non existant resource
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTestDoesntExist"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "404"

  Scenario: Try to create a new resource with no content data
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTest"
    And the request method of "PUT"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "411"

  Scenario: Create a new resource
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTest"
    And the request method of "PUT"
    And the request data of "test"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "204"
    And the written file "tonicFilesystemTest" should contain 'test'

  Scenario: Delete a resource
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTest"
    And the request method of "DELETE"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "204"
    And the written file "tonicFilesystemTest" should not exist

  Scenario: Try to delete a non existant resource
    Given the filesystem test data is setup
    And the request URI of "/filesystemtest/one/tonicFilesystemTestDoesntExist"
    And the request method of "DELETE"
    When I create a request object
    And I load the resource
    And execute the request
    Then the response code should be "404"
