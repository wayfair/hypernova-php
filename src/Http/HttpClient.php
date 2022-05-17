<?php 

namespace WF\Hypernova\Http;

function getOrNull($array, $key){
    return isset($array[$key]) ? $array[$key] : null;
}

class Client {
    private $globalConf;

    public function __construct($conf) {
        $this->globalConf = $conf;
    }

    public function request($method, $url, $body = null, $queryParams = null, $headers = []) {
        $curl = curl_init();

        if (!is_null($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $conf = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method
        );

        foreach ($conf as $option => $value) {
           curl_setopt($curl, $option, $value);
        }

        if (!is_null($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $tmp = [];
        foreach ($headers as $key => $record) {
            $tmp[] = $record;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $tmp);

        try {
            $rawResponse = curl_exec($curl);
        } catch (\ErrorException $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }

        $response = new Response($curl, $rawResponse);
        curl_close($curl); // close cURL handler

        return $response;
    }

    public function get($url, $conf = []){
        $queryParams = getOrNull($conf, 'params');
        $headers = getOrNull($conf, 'headers');
        return $this->request('GET', $url, null, $queryParams, $headers);
    }

    public function post($url, $conf = []) {
        $body = getOrNull($conf, 'body');
        $json = getOrNull($conf, 'json');
        $queryParams = getOrNull($conf, 'params');
        $headers = getOrNull($conf, 'headers');

        if(!is_null($json)){
            $body = json_encode($json);
            if(is_null($headers)) {
                $headers = [];
            }
            $headers[] = "Content-Type: application/json";
        }
        return $this->request('POST', $url, $body, $queryParams, $headers);
    }
}

?>