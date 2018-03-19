<?php

namespace EmailChecker;
/* Telnet Class
 *
 * author: Clark Galgo <clark@dev-engine.net>
 *
 */
class Telnet {
    protected $resource = null;
    protected $wait = 3;
    protected $response = null;
    protected $valid = false;
    protected $headers = [];
    
    /* construct
     *
     * @param string $host
     * @param integer $port
     */
    public function __construct ($host, $port = 25) {
        // telnet baby
        $this->resource = fsockopen($host, $port, $errno, $errstr, 30);
        
        // no blocking
        stream_set_blocking($this->resource, 0);
        
    }

    /* say helo to server
     *
     * @param string $hi
     * @return string
     */
    public function sayHelo ($hi = 'hi') {
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        // say hello
        fwrite ($this->resource, "helo " . $hi . "\r\n");
        $res = '';
        for ($i = 0; $i < $this->wait; $i++) {
            sleep(1); // sleep 1 second
            // get response
            $response = fgets($this->resource, 1028);
            // if response is empty,
            // break loop
            if (!trim($response) && $res != '') {
                break;
            }
            
            
            $res .= $response;
        }

        return $res;
    }

    /*
     * set mail from
     *
     * @param string $email
     * @return string
     */
    public function mailFrom ($email) {
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        // set mail from
        fwrite ($this->resource, "MAIL FROM: <" . $email . ">\r\n");
        $res = '';
        for ($i = 0; $i < $this->wait; $i++) {
            sleep(1); // sleep 1 second
            // get response
            $response = fgets ($this->resource, 1028);
            // if response is empty,
            // break loop
            if (!trim($response) && $res != '') {
                break;
            }

            $res .= $response;
        }

        return $res;
    }

    /*
     * set rcpt to
     *
     * @param string $email
     * @return string
     */
    public function rcptTo ($email) {
        
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        // set mail from
        fwrite ($this->resource, "RCPT TO: <" . $email . ">\r\n");
        $res = '';
        for ($i = 0; $i < $this->wait; $i++) {
            sleep(1); // sleep 1 second
            // get response
            $response = fgets ($this->resource, 1028);
            // if response is empty,
            // break loop
            if (!trim($response) && $res != '') {
                break;
            }

            $res .= $response;
        }

        $this->response = $res;
        return $res;
    }

    /*
    * Check email if exist
    * 
    * @return bool
    */
    public function check () {
        // check if rcptResponse is set
        if (!$this->response) {
            throw new \Exception('RCPT TO response is not set yet. Try to set higher value for wait time');
            return;
        }

        // match this response: recipient <email@domain.com> ok.
        if (preg_match('/ok/i', $this->response) && preg_match('/recipient/i', $this->response)) {
            $this->valid = true;
        }

        // match this response: OK [random-character] - gsmtp
        if (preg_match('/OK\s.*\s\-\sgsmtp/', $this->response)) {
            $this->valid = true;
        }

        // match unauthenticated response, this means it's valid but we are not authorized to send an email here
        // seen this on zoho servers
        if (preg_match('/unauthenticated/', $this->response)) {
            $this->valid = true;
        }

        // this is seen on outlook
        if (preg_match('/Recipient\sOK/i', $this->response)) {
            $this->valid = true;
        }

        // yahoo
        if (preg_match('/ok/', $this->response) && preg_match('/dirdel/', $this->response)) {
            $this->valid = true;
        }
        
        return $this->valid;
    }

    public function getResponse() {
        return $this->response;
    }

    /*
     * data request to telnet
     */
    public function data () {
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        // set mail from
        fwrite ($this->resource, "DATA\r\n");
        
        $res = '';
        for ($i = 0; $i < $this->wait; $i++) {
            sleep(1); // sleep 1 second
            // get response
            $response = fgets ($this->resource, 1028);
            // if response is empty,
            // break loop
            if (!trim($response) && $res != '') {
                break;
            }

            $res .= $response;
        }
        
        return $res;
    }

    /*
     * add email header
     *
     * @param string key
     * @param string value
     */
    public function addHeader ($key, $value) {
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        if ($key && $value) {
            $this->headers[] = $key . ':' . $value;
        }

        return $this;
    }
    
    /*
     * set email body
     *
     * @param string body
     */
    public function setBody($body) {
        // check if resource is set
        if (!$this->resource) {
            throw new \Exception ('No socket opened.');
            return;
        }

        // set headers
        foreach ($this->headers as $header) {
            fwrite ($this->resource, $header . "\r\n");
        }

        // now set the body
        fwrite ($this->resource, "\r\n");
        fwrite ($this->resource, $body . "\r\n");
        fwrite ($this->resource, ".\r\n");
        
        $res = '';
        for ($i = 0; $i < 10; $i++) {
            sleep(1); // sleep 1 second
            // get response
            $response = fgets ($this->resource, 1028);
            // if response is empty,
            // break loop
            if (!trim($response) && $res != '') {
                break;
            }

            $res .= $response;
        }
        
        // get response
        $this->response = $res;
        return $this;
    }
    
    
}

