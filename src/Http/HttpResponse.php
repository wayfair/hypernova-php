<?php 

namespace WF\Hypernova\Http;

class Response {

    private $rawBody;
    private $headers;
    private $response;

    public function __construct($curl, $response) {
        $this->rawBody = $response;
        if (empty($response)) {
            throw new Exception("Empty Response", 0);        
            return;
        }

        $info = curl_getinfo($curl);

        if (empty($info['http_code'])) {
            throw new Exception("No HTTP code was returned", 0);        
            return;
        }

        $this->response = $info;
        $this->headers = $info['request_header'];


        if($info['http_code'] > 399) {
            throw new Exception($response, $info['http_code'] );
        }
    }

    public function getBody() {
        return $this->rawBody;
    }

    public function getHeaders() {
        return $this->headers;
    }
}

?>