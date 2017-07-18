<?php
/**
  * The standalone PHP class that validates the request by calling the API
  */
/**
  * The standalone PHP class that validates the request by calling the API
  */
class DataDome_ValidateRequest 
{
    /**
     * @internal
     * 
     * The API method for sending the request parameters
     */
    const API_METHOD_VALIDATE = "validate-request";
    
    /**
     * @internal
     * 
     * @property array $includeHeaders defines the parameters that can be found in the headers
     */
    private static $includeHeaders = array(
        "XForwaredForIP" => "X-Forwarded-For",
    );

    /**
     * @internal
     * 
     * @property array $includeServerHeaders defines the parameters that need to be sent to the API and their handling.
     * <param_name> => <param_value> where <param_value> can be a string or an array. If it's a string, the information is extracted
     * using $_SERVER[<param_value>] but if it's an array, it is resolved to a local function that extract the value
     */
    private static $includeServerHeaders = array(
        "Accept"                => "HTTP_ACCEPT",
        "AcceptCharset"         => "HTTP_ACCEPT_CHARSET",
        "AcceptEncoding"        => "HTTP_ACCEPT_ENCODING",
        "AcceptLanguage"        => "HTTP_ACCEPT_LANGUAGE",
        "AuthorizationLen"      => array('getAuthorizationLen'),
        "CacheControl"          => "HTTP_CACHE_CONTROL",
        "Connection"            => "HTTP_CONNECTION",
        "CookiesLen"            => array('getCookiesLen'),
        "Host"                  => "HTTP_HOST",
        "IP"                    => "REMOTE_ADDR",
        "Origin"                => "HTTP_ORIGIN",
        "Port"                  => "REMOTE_PORT",
        "PostParamLen"          => array('getPostParamLen'),
        "Pragma"                => "HTTP_PRAGMA",
        "Protocol"              => array("getProtocol"),
        "Referer"               => "HTTP_REFERER",
        "Request"               => "REQUEST_URI",
        "ServerHostname"        => "HTTP_HOST",
        "ServerName"            => array("getServer"),
        "TimeRequest"           => array("getRequestTime"),
        "UserAgent"             => "HTTP_USER_AGENT",
    );

    /**
     * @internal
     * 
     * @property array $excludeMapping defines the parameters that are mapped to special values in the exclude parameter settings
     */
    private static $excludeMapping = array(
    );

    /**
     * @internal
     *
     * @property string $excludeRegex exclusion regex to filter URIs to ignore
     */
    private static $uriRegexExclusion = "/\/.*(\.js|\.css|\.jpg|\.jpeg|\.png|\.ico|\.gif|\.tiff|\.woff|\.woff2|\.ttf|\.eot)$/";

    /**
      * The method that actually performs the validation
      *
      * @param string $server the server/url to use
      * @param int $timeout the connect timeout in milliseconds
      * @param string $key the license key configured in the settings
      * @param string $version the version of the module
      * @param string $exclude the headers to exclude configured in the settings
      * @param boolean $https enable HTTPS
      *
      * @return array
      */
    public static function validate($server, $timeout, $key, $version, $exclude, $https)
    {
        // Checks if CURL enabled
        if (function_exists('curl_version') === false) {
            return null;
        }

        // Tests if URI matches the exclusion regex
        if (preg_match(self::$uriRegexExclusion, @$_SERVER['REQUEST_URI']) === 1) {
            return null;
        }

        $time    = round(microtime(true) * 1000);
        $headers = array(
            "ContentType: application/x-www-form-urlencoded", 
            "User-Agent: DataDome"
        );
        $params        = array();
        $params["Key"] = $key;

        $requestHeaders = self::getHeaders();

        foreach (self::$includeServerHeaders as $key => $val) {
            if ($key == 'AuthorizationLen') {
                $method = $val[0];
                $value  = self::$method($requestHeaders);
            } else {
                if (is_array($val)) {
                    $method = $val[0];
                    $value  = self::$method();
                } else {
                    $value = @$_SERVER[$val];
                }
            }

            $params[$key] = $value;
        }

        $params['HeadersList'] = implode(',', array_keys($requestHeaders));

        if (!empty(self::$includeHeaders)) {
            DataDome_Util::writeDebug("headers = " . print_r($requestHeaders, true));

            foreach (self::$includeHeaders as $key => $val) {
                if (!isset($requestHeaders[$val])) {
                    continue;
                }

                $params[$key] = $requestHeaders[$val];
            }
        }

        $params["RequestModuleName"]  = "WordPress";
        $params["APIConnectionState"] = "New";
        $params["ModuleVersion"]      = $version;
        if (isset($_COOKIE["datadome"])) {
            $params["ClientID"] = $_COOKIE["datadome"];
        }

        if (!empty($exclude)) {
            $exclude = explode(" ", trim($exclude));
            if (is_array($exclude)) {
                foreach ($exclude as $key) {
                    if (isset(self::$excludeMapping[$key])) {
                        $key = self::$excludeMapping[$key];
                    }
                    unset($params[$key]);
                }
            }
        }

        // remove parameters when their value is empty
        $params = array_filter($params);

        $protocol = $https ? "https" : "http";

        $result = DataDome_Util::callAPI($protocol . "://" . $server . self::API_METHOD_VALIDATE, array("method" => "post", "headers" => true), $timeout, $params, $headers);
        DataDome_Util::writePerformanceLog("DataDome_ValidateRequest::validate", (round(microtime(true) * 1000) - $time));

        return $result;
    }

    /**
      * The method that determines the request time
      *
      * @return float
      */
    private static function getRequestTime()
    {
        $curTime = str_replace(".", "", microtime(true));

        return str_pad($curTime, 16, "0", STR_PAD_RIGHT);
    }

    /**
      * The method that determines the host based on SERVER_ADMIN or HTTP_HOST
      *
      * @return string
      */
    private static function getHost()
    {
        if (!isset($_SERVER["SERVER_ADMIN"])) {
            return "";
        }

        $host = $_SERVER["SERVER_ADMIN"];
        if (empty($host) || $host == "[no address given]") {
            $host   = $_SERVER["HTTP_HOST"];
        }

        return $host;
    }

    /**
      * The method that determines the server name based on SERVER_NAME or gethostname()
      *
      * @return string
      */
    private static function getServer()
    {
        if (!isset($_SERVER["SERVER_NAME"])) {
            return "";
        }

        $server = $_SERVER["SERVER_NAME"];
        if (function_exists("gethostname")) {
            $server = gethostname();
        }

        return $server;
    }

    /**
      * The method that determines the protocol name based on SERVER_PROTOCOL
      *
      * @return string
      */
    private static function getProtocol()
    {
        if (!isset($_SERVER["SERVER_PROTOCOL"])) {
            return "";
        }

        $protocol = $_SERVER["SERVER_PROTOCOL"];
        $protocol = explode("/", $protocol);

        return strtolower($protocol[0]);
    }

    /**
     * The method that determines the size of AUTHORIZATION header.
     *
     * @property array $requestHeaders list of request headers
     *
     * @return integer
     */
    private static function getAuthorizationLen($requestHeaders)
    {
        if (!isset($requestHeaders["Authorization"])) {
            return "";
        }

        return mb_strlen($requestHeaders['Authorization'], 'UTF-8');
    }

    /**
     * The method that determines the size of COOKIES header.
     *
     * @return integer
     */
    private static function getCookiesLen()
    {
        if (!isset($_SERVER["HTTP_COOKIE"])) {
            return "";
        }

        return mb_strlen($_SERVER['HTTP_COOKIE'], 'UTF-8');
    }

    /**
     * The method that determines the size of POST params.
     *
     * @return integer
     */
    private static function getPostParamLen()
    {
        $postParam = http_build_query($_POST, "", "%26");

        return mb_strlen($postParam, 'UTF-8');
    }

    /**
      * The method that determines the headers in the request
      *
      * @return array
      */
    private static function getHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}
