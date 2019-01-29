<?php

class Request {
    public $url_elements;
    private $verb;
    private $parameters;
 
    public function __construct($srv_var, $request_param_string) {
        $this->verb = $srv_var['REQUEST_METHOD'];
        $this->url_elements = explode('/', $srv_var['PATH_INFO']);

        $this->parse_incoming_params($srv_var['QUERY_STRING'], $request_param_string, $srv_var['CONTENT_TYPE']);
        // initialise json as default format
        $this->format = 'json';
        if(isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
    }
 
    public function parse_incoming_params($servurl_params, $request__param_string, $content_type_string) {
        $parameters = array();
 
        // first of all, pull the GET vars
        parse_str($servurl_params, $parameters);
 
        // now how about PUT/POST bodies? These override what we got from GET
        $body = $request_param_string;
        $content_type = false;
        if(isset($content_type)) {
            $content_type = $content_type_string;
        }
        switch($content_type) {
            case "application/json":
                $body_params = json_decode($body);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $parameters[$field] = $value;
                }
                $this->format = "html";
                break;
            default:
                // we could parse other supported formats here
                break;
        }
        $this->parameters = $parameters;
    }
    
    public function getVerb() {
        return $this->verb;
    }
    
    public function getParameters() {
        return $this->parameters;
    }
}
?>
