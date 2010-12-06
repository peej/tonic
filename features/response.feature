Feature: Response object
  In order to output a response representation
  As a PHP developer
  I want a PHP object that represents a HTTP response
  
  Scenario: Output should be gzipped if the client supports it
    Given the request URI of "/resourcetest/one"
    And an accept encoding of "gzip"
    When I create a request object
    And I load the resource
    And execute the request
    And I process content encoding
    Then the response header "Content-Encoding" should contain 'gzip'
    And the response body should be gzipped and be "test"

  Scenario: Output should be deflated if the client supports it
    Given the request URI of "/resourcetest/one"
    And an accept encoding of "deflate"
    When I create a request object
    And I load the resource
    And execute the request
    And I process content encoding
    Then the response header "Content-Encoding" should contain 'deflate'
    And the response body should be deflated and be "test"

  Scenario: Output should be compress if the client supports it
    Given the request URI of "/resourcetest/one"
    And an accept encoding of "compress"
    When I create a request object
    And I load the resource
    And execute the request
    And I process content encoding
    Then the response header "Content-Encoding" should contain 'compress'
    And the response body should be compressed and be "test"

  Scenario: Responses should have a default cache header
    Given the request URI of "/resourcetest/one"
    When I create a request object
    And I load the resource
    And execute the request
    And I add a cache header of ""
    Then the response header "Cache-Control" should contain 'max-age=86400, must-revalidate'

  Scenario: Responses told not to cache should send a zero cache header
    Given the request URI of "/resourcetest/one"
    When I create a request object
    And I load the resource
    And execute the request
    And I add a cache header of "0"
    Then the response header "Cache-Control" should contain 'no-cache'

  Scenario: Responses given an etag should send an etag response header
    Given the request URI of "/resourcetest/one"
    When I create a request object
    And I load the resource
    And execute the request
    And I add an etag header of "123123"
    Then the response header "Etag" should contain '"123123"'
