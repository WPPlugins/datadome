<?php
/**
  * The standalone PHP class that fetches the list of servers and does the latency benchmark tests
  *
  */
/**
  * The standalone PHP class that fetches the list of servers and does the latency benchmark tests
  *
  */
class DataDome_GetServers
{

    /**
     * @internal
     * 
     * The endpoint for getting the list of servers
     */
    const API_ENDPOINT_LIST     = "#://package.datadome.co/api-servers.json";

    /**
     * @internal
     * 
     * The API method for getting the response of a benchmark test
     */
    const API_METHOD_STATUS     = "status";

    /**
     * @internal
     * 
     * The endpoint for sending benchmark statistics
     */
    const API_STATS_ENDPOINT    = "#://package.datadome.co/bstats.php";

    /**
     * @internal
     * 
     * The number of hits to calculate average latency
     */
    const NUM_OF_HITS           = 10;

    /**
      * The method that fetches the list of servers and, optionally, does the latency benchmark tests
      *
      * @param int $timeout the connect timeout in milliseconds
      * @param boolean $getMostEfficient perform the latency benchmark (defaults to true) and sort the results in ascending order of performance
      * @param boolean $sendStats send statistics to data-dome?
      * @param boolean $https enable HTTPS
      *
      * @return array
      */
    public static function fetchList($timeout, $getMostEfficient=true, $sendStats=false, $https=false)
    {
        if (self::NUM_OF_HITS > 1) {
            @set_time_limit(0);
        }

        $protocol       = $https ? "https" : "http";
        $url            = str_replace("#", $protocol, self::API_ENDPOINT_LIST);

        $time           = round(microtime(true) * 1000);
        $response       = DataDome_Util::callAPI($url, array("method" => "get", "json" => true), $timeout, null);
        $list           = $response["response"];
        if ($list) {
            if ($getMostEfficient) {
                foreach ($list as &$array) {
                    $array["time"]  = self::calculateSpeed($array["host"], $timeout, $https);
                }

                usort($list, function($x, $y){
                    if($x["time"] == $y["time"]) return 0;
                    return $x["time"] < $y["time"] ? -1 : 1;
                });
            }
        }

        if ($sendStats) {
            self::sendStatistics($timeout, $list, $https);
        }

        DataDome_Util::writePerformanceLog("DataDome_GetServers::fetchList", (round(microtime(true) * 1000) - $time));
        return $list;
    }

    /**
      * The method that actually performs the latency benchmark tests
      *
      * @param string $server the server/url to use
      * @param int $timeout the connect timeout in milliseconds
      * @param boolean $https enable HTTPS
      *
      * @return int
      */
    private static function calculateSpeed($server, $timeout, $https)
    {
        $protocol       = $https ? "https" : "http";

        $time   = 0;
        for ($x = 0; $x < self::NUM_OF_HITS; $x++) {
            $response   = DataDome_Util::callAPI($protocol . "://" . $server . "/" . self::API_METHOD_STATUS, array("method" => "get"), $timeout, null);
            if ($response) {
                $time   += $response["time"];
            }
        }
        DataDome_Util::writeDebug("DataDome_GetServers::calculateSpeed, total time $time");
        return $time/self::NUM_OF_HITS;
    }

    /**
      * The method that sends the benchmark latency statistics to the datadome server
      *
      * @param int $timeout the connect timeout in milliseconds
      * @param int $list the final array containing the hosts and their response times
      *
      * @return void
      */
    private static function sendStatistics($timeout, $list, $https)
    {
        if (empty($list)) return;

        $stats      = "";
        foreach ($list as $array) {
            if (strlen($stats) > 0) {
                $stats  .= ",";
            }
            $stats  .= $array["host"] . "=" . $array["time"];
        }

        $protocol       = $https ? "https" : "http";
        $url            = str_replace("#", $protocol, self::API_STATS_ENDPOINT);

        DataDome_Util::callAPI($url, array("method" => "post"), $timeout, array("stats" => $stats));
    }
}
