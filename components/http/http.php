<?php
/**
 * A HTTP client that makes it easy to send HTTP requests and trivial to
 * integrate with web services.
 * Available Methods:
 *  - GET
 *  - POST
 *  - PUT
 *  - DELETE
 *
 * @package Advandz
 * @subpackage Advandz.components.http
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
namespace Advandz\Component;

class Http {
    /**
     * @var string The server to send te request
     */
    private $server = '';
    /**
     * @var string The URI request
     */
    private $uri = '';
    /**
     * @var integer Server port
     */
    private $port = null;
    /**
     * @var boolean Use SSL in the request
     */
    private $ssl = false;
    /**
     * @var array The request headers
     */
    private $headers = [];
    /**
     * @var array Basic authentification
     */
    private $auth = [];
    /**
     * @var string Request method
     */
    private $method = 'GET';
    
    /**
     * Set the server to send the request
     *
     * @param string $server The server to send the request
     * @return Http Reference to this class
     */
    public function server($server) {
        $this->server = $server;
        
        return $this;
    }
    
    /**
     * Sets the request URI
     *
     * @param string $uri The request URI
     * @return Http Reference to this class
     */
    public function uri($uri) {
        $this->uri = $uri;
        
        return $this;
    }
    
    /**
     * Sets the port to be used in the request
     *
     * @param integer $port The request port
     * @return Http Reference to this class
     */
    public function port($port) {
        if (is_numeric($port)) {
            $this->port = $port;
        }
        
        return $this;
    }
    
    /**
     * Use SSL in the request
     */
    public function useSsl() {
        $this->ssl = true;
        
        return $this;
    }
    
    /**
     * Sets the headers to be sent in the request
     *
     * @param array $headers The request headers
     * @return Http Reference to this class
     */
    public function headers($headers = []) {
        if (is_array($headers)) {
            $this->headers = $headers;
        }
        
        return $this;
    }
    
    /**
     * Sets the basic authentication to the request
     *
     * @param array $auth The authentication details
     * @return Http Reference to this class
     */
    public function auth($auth = []) {
        if (is_array($auth)) {
            $this->auth = $auth;
        }
        
        return $this;
    }
    
    /**
     * Sets the method to send the request
     *
     * @param string $method Request method
     * @return Http Reference to this class
     * @throws Exception When a invalid HTTP method is given
     */
    public function method($method) {
        if ($method == 'GET' || $method == 'POST' || $method == 'DELETE' || $method == 'PUT') {
            $this->method = $method;
        } else {
            throw new Exception('Invalid method');
        }
        
        return $this;
    }
    
    /**
     * Execute the request
     *
     * @param array $data The data to send
     * @return mixed The result of the request
     */
    public function execute($data = []) {
        $curl = curl_init();
        
        // Add basic authentication header
        if (isset($this->auth[0]) && isset($this->auth[1])) {
            $this->headers[] = 'Authorization: Basic ' . base64_encode($this->auth[0] . ':' . $this->auth[1]);
            curl_setopt($curl, CURLOPT_USERPWD, base64_encode($this->auth[0] . ':' . $this->auth[1]));
        }
        
        // Set headers
        if (isset($this->headers) && is_array($this->headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        // Build GET request
        if ($this->method == 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $get = '?' . http_build_query($data);
            }
        }
        
        // Build POST request
        if ($this->method == 'POST') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        // Build PUT request
        if ($this->method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        // Build DELETE request
        if ($this->method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            if (!empty($data)) {
                $get = '?' . http_build_query($data);
            }
        }
        
        // Build URL
        $url = ($this->ssl ? 'https' : 'http') . '://' . $this->server . (isset($this->port) ? ':' . $this->port : '') . '/' . $this->uri . (isset($get) ? $get : '');
        
        // Make request
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // Get result
        $result = curl_exec($curl);
        
        // Close request
        curl_close($curl);
        
        return $result;
    }
}