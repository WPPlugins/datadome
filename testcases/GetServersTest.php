<?php
/**
  * The unit test for latency benchmark testing
  */
/**
  * The unit test for latency benchmark testing
  */
class GetServersTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get list of servers after testing for benchmark latency
     * 
     * @param int $timeout in milliseconds
     * @param boolean $https enable HTTPS
     * @dataProvider dataProvider
     */
    public function testFetchServersListWithEfficiency($timeout, $https)
    {
        $list   = DataDome_GetServers::fetchList($timeout, true, false, $https);

        $this->assertNotNull($list, "List is null");
        $this->assertNotEmpty($list, "List is empty");
        $this->assertNotCount(0, $list, "List has zero elements");
        $this->assertArrayHasKey("host", $list[0], "List does not contain 'host'");
        $this->assertArrayHasKey("name", $list[0], "List does not contain 'name'");
        $this->assertArrayHasKey("time", $list[0], "List does not contain 'time'");

        return $list;
    }

    /**
     * Get list of servers without testing for benchmark latency
     * 
     * @param int $timeout in milliseconds
     * @param boolean $https enable HTTPS
     * @dataProvider dataProvider
     */
    public function testFetchServersListWithoutEfficiency($timeout, $https)
    {
        $list   = DataDome_GetServers::fetchList($timeout, false, false, $https);

        $this->assertNotNull($list, "List is null");
        $this->assertNotEmpty($list, "List is empty");
        $this->assertNotCount(0, $list, "List has zero elements");
        $this->assertArrayHasKey("host", $list[0], "List does not contain 'host'");
        $this->assertArrayHasKey("name", $list[0], "List does not contain 'name'");

        return $list;
    }

    /**
     * provide timeout in milliseconds, and whether to use HTTPS
     * 
     * @return array containing the timeout in milliseconds, boolean to use HTTPS
     */
    public function dataProvider()
    {
        new Autoloader();
        return array(
            array(1000, false),
        );
    }
}