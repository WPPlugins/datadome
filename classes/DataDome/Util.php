<?php
/**
  * The standalone PHP class that provides utility methods to other classes
  */
/**
  * The standalone PHP class that provides utility methods to other classes
  */
class DataDome_Util
{
    /**
     * @internal
     * 
     * @property boolean $debug switches on/off debugging
     */
    static $debug           = false;

    /**
     * @internal
     * 
     * @property string $debugFile complete path of the debug log file
     */
    static $debugFile       = null;

    /**
     * @internal
     * 
     * @property string $performanceLog complete path of the performance file
     */
    static $performanceLog  = null;

    /**
      * The method that writes to the debug file if debug is on and a valid debug file has been provided
      *
      * @param string $mgs the message to print
      *
      * @return none
      */
    public static function writeDebug($msg)
    {
        if (self::$debug && self::$debugFile) file_put_contents(self::$debugFile, date("F j, Y H:i:s") . " - " . $msg."\n", FILE_APPEND);
    }

    /**
      * The method that writes to the performance log file if a valid file has been provided
      *
      * @param string $method the name of the method
      * @param int $time the time taken in milliseconds
      *
      * @return none
      */
    public static function writePerformanceLog($method, $time)
    {
        $msg        = "$method took $time ms";
        if (self::$performanceLog) file_put_contents(self::$performanceLog, date("F j, Y H:i:s") . " - " . $msg."\n", FILE_APPEND);
    }

    /**
      * The method that actually calls the API
      *
      * @param string $url the url to use
      * @param array $props the properties that determine the behavior of how the result will be processed, such as whether to get header or not ("headers"), whether to use POST or * GET ("method") and whether to process the response as JSON or string ("json")
      * @param int $timeout the connect timeout in milliseconds
      * @param array $params the params to send to the API but only if the method is POST
      * @param array $headers the headers to send to the API
      *
      * @return array
      */
    public static function callAPI($url, $props=array(), $timeout, $params=array(), $headers=array())
    {
        $response   = null;
        $error      = null;
        $conn       = curl_init($url);
        $getHeaders = $props && isset($props["headers"]) && $props["headers"];

        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout*1000); 
        curl_setopt($conn, CURLOPT_TIMEOUT, $timeout*1000);
        curl_setopt($conn, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($conn, CURLOPT_HEADER, 0);
        curl_setopt($conn, CURLOPT_NOSIGNAL, 1);
        if ($getHeaders) {
            curl_setopt($conn, CURLOPT_HEADER, 1);
        }

        if ($headers) {
            $header = array();
            foreach ($headers as $key=>$val) {
                $header[] = "$key: $val";
            }
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
        }

        if ($props && isset($props["method"]) && $props["method"] === "post") {
            curl_setopt($conn, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        }

        $time = round(microtime(true) * 1000);

        $body            = null;
        $responseHeaders = null;
        $response        = null;
        try {
            $response = curl_exec($conn);
            $httpCode = curl_getinfo($conn, CURLINFO_HTTP_CODE);
            if (!curl_errno($conn)) {
                if ($getHeaders) {
                    list($responseHeaders, $body) = explode("\r\n\r\n", $response, 2);
                    $responseHeaders = self::getResponseHeadersArray($responseHeaders);

                    if ((int) $responseHeaders['X-DataDomeResponse'] !== (int) $httpCode) {
                        $error = 500;
                    } else {
                        $error = $httpCode;
                    }
                } else {
                    $body  = $response;
                    $error = $httpCode;
                }
            } else {
                $error = $httpCode;
            }
        } catch (Exception $e) {
            self::writeDebug("Exception " . $e->getMessage());
        }

        $time = (round(microtime(true) * 1000) - $time);

        if (curl_errno($conn) && self::$debug) {
            self::writeDebug("curl_errno ".curl_error($conn));
        }

        curl_close($conn);

        if ($props && isset($props["json"]) && $props["json"]) {
            $body = json_decode($body, true);
        }

        $array = array(
            "response"  => $body,
            "error"     => $error,
            "time"      => $time,
            "headers"   => $responseHeaders
        );

        self::writeDebug("Calling ". $url. " with $timeout and fields = " . print_r($params, true) . " returning raw response " . $response . " and finally returning " . print_r($array,true));

        return $array;
    }

    /**
      * The method that extracts the response from the response of the API
      *
      * @param string $headers the headers from the response as a string
      *
      * @return array
      */
    private static function getResponseHeadersArray($headers)
    {
        $final  = array();
        $array  = explode("\r\n", $headers);
        foreach ($array as $element) {
            if (strpos($element, ":") === FALSE) continue;
            list($key, $value)  = explode(":", $element, 2);
            $final[$key]        = trim($value);
        }
        return $final;
    }
}
