class Request {
    public $url_elements;
    public $verb;
    public $parameters;
 
    public function __construct() {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $this->url_elements = explode('/', $_SERVER['PATH_INFO']);
    }
}