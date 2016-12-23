<?php

namespace PP\SampleBundle\Utils;

class ClipperHttpClient
{
  const TYPE_FORM = "application/x-www-form-urlencoded";
  const TYPE_JSON = "application/json";
  
  /**
   * post data to url in a non-blocking way
   **/
	public function postNonBlocking($url, $params = null, $content_type = self::TYPE_FORM)
  {
    if (!is_null($params) && !is_string($params)) {
      $params = http_build_query($params);
    }

    $parts = parse_url($url);
    $port = isset($parts['port']) ? $parts['port'] : 80;
    $host = isset($parts['host']) ? $parts['host'] : null;
    $path = isset($parts['path']) ? $parts['path'] : "/";
    
    try {
      $fp = fsockopen($host, $port, $errno, $errstr);
      stream_set_blocking($fp, false);
    }
    catch(\Exception $e) {
      throw new \Exception("fsockopen ERROR: {$errno} - {$errstr}");
    }

    $out  = "POST " . $path . " HTTP/1.1\r\n";
    $out .= "Host: " . $host . "\r\n";
    $out .= "Content-Type: " . $content_type . "\r\n";
    $out .= "Content-Length: " . strlen($params) . "\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $out .= $params;

    if (!$fp) {
      throw new \Exception("fsockopen ERROR: {$errno} - {$errstr}");
    } 
    else {
      stream_set_timeout($fp, 3); // seconds timeout
      fwrite($fp, $out);
      fclose($fp);      
    }
  }
  
  /**
   * JSOM-RPC non-blocking
   */
  public function jsonRpcNonBlocking($url, $method, $params)
  {
    $request = array(
      'method' => $method,
      'params' => $params,
      'id' => null,
    );
    $json = json_encode($request);
    
    $this->postNonBlocking($url, $json, self::TYPE_JSON); 
  }
}