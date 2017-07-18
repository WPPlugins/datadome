<?php
/**
  * The unit test for testing validation requests
  */
/**
  * The unit test for testing validation requests
  */
class ValidateRequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get list of servers after testing for benchmark latency and then hit each server with a validation request
     * 
     * @param int $timeout the connect timeout in milliseconds
     * @param string $license the license key
     * @param string $version the version of the module
     * @param string $exclude what all parameters to exclude from the request
     * @dataProvider dataProvider
     */
    public function testValidateRequest($timeout, $license, $version, $exclude)
    {
        $list       = DataDome_GetServers::fetchList($timeout, true, false);
        foreach ($list as $server) {
            $host       = $server["host"];
            $result     = DataDome_ValidateRequest::validate($host, $timeout, $license, $version, $exclude);

            $this->assertNotNull($result, "Result is null for $host");
            $this->assertNotEmpty($result, "Result is empty for $host");
            $this->assertNotCount(0, $result, "Result has zero elements for $host");
            $this->assertArrayHasKey("response", $result, "Result does not have 'response' for $host");
            $this->assertArrayHasKey("error", $result, "Result does not have 'error' for $host");
            $this->assertArrayHasKey("time", $result, "Result does not have 'time' for $host");
            $this->assertArrayHasKey("headers", $result, "Result does not have 'headers' for $host");
            $this->assertNotEmpty($result["headers"], "Headers is empty for $host");
        }
    }

    /**
     * provide timeout in milliseconds, license, version, exclude params
     * 
     * @return array containing the timeout in milliseconds, the license key to use, the module version and the parameters to exclude
     */
    public function dataProvider()
    {
        new Autoloader();
        return array(
            array(1000, "d41d8cd98f00b20", "1", ""),
        );
    }

}