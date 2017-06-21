<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 * @uses Core\Util
 */
namespace Core {
    use Core\{Util,System,Message,UploadedFile,Uri};
    // use Psr\Http\Message\ServerRequestInterface;
    // use Psr\Http\Message\UriInterface;
    /**
     * Class Request
     * @see Message
     * see ServerRequestInterface
     * @constant SESSION_KEY_DEFAULT 'wf'
     * @var array $http_get
     * @var array $http_post
     * @var array $http_server
     * @var array $http_cookie
     * @var array $http_file
     * @var string $uri
     * @var array $uri_attribute
     * @var array $request_method
     * @var string $route_id
     * @var array $app_url_list
     */
    // class Request extends Message implements ServerRequestInterface {
    class Request extends Message {
        private const SESSION_KEY_DEFAULT = 'wf';

        private $http_get = [];
        private $http_post = [];
        private $http_server = [];
        private $http_cookie = [];
        private $http_file = [];
        private $uri;
        private $uri_attribute;
        private $request_method;
        private $method;
        private $route_id;
        private $app_url_list;
        private $request_target;
        /**
         * Request constructor
         */
        public function __construct() {
            $this->http_get = $this->setNullEmptyData($_GET);
            $this->http_post = $this->setNullEmptyData($_POST);
            $this->http_server = $this->setNullEmptyData($_SERVER);
            $this->http_cookie = $this->setNullEmptyData($_COOKIE);

            $this->uploadFilePack($this->setNullEmptyData($_FILES));

            if (!array_key_exists('REQUEST_METHOD',$this->http_server) || empty($this->http_server['REQUEST_METHOD'])) {
                throw new \Error('Global $_SERVER["REQUEST_METHOD"] is empty');
            }

            $request_method = $this->http_server['REQUEST_METHOD'];

            $this->setMethod($request_method);

            $uri = new Uri($this);
            $this->setUri($uri);
        }
        /**
         * @return array
         */
        public function getHttpGet(): array {
            return $this->http_get;
        }
        /**
         * @return array
         */
        public function getQueryParams(): array {
            return $this->getHttpGet();
        }
        /**
         * @param string $name
         * @param string $value
         * @return self
         */
        public function setHttpGet(string $name,string $value): self {
            $this->http_get[$name] = $value;

            return $this;
        }
        /**
         * @param string $name
         * @param array $list
         * @return self
         */
        public function setHttpGetArray(string $name,array $list): self {
            $this->http_get[$name] = $list;

            return $this;
        }
        /**
         * @param array $http_get
         * return ServerRequestInterface
         * @return Request
         */
        // public function withQueryParams(array $http_get): ServerRequestInterface {
        public function withQueryParams(array $http_get): Request {
            $clone = clone $this;
            $clone->setHttpGet($http_get);

            return $clone;
        }
        /**
         * @return array
         */
        public function getHttpPost(): array {
            return $this->http_post;
        }
        /**
         * @return array
         */
        public function getParsedBody(): array {
            return $this->getHttpPost();
        }
        /**
         * @param string $name
         * @param string $value
         * @return self
         */
        public function setHttpPost(string $name,string $value): self {
            $this->http_post[$name] = $value;

            return $this;
        }
        /**
         * @param string $name
         * @param array $list
         * @return self
         */
        public function setHttpPostArray(string $name,array $list): self {
            $this->http_post[$name] = $list;

            return $this;
        }
        /**
         * @param array $http_post
         * return ServerRequestInterface
         * @return Request
         */
        // public function withParsedBody(array $http_post): ServerRequestInterface {
        public function withParsedBody(array $http_post): Request {
            $clone = clone $this;
            $clone->setHttpPost($http_post);

            return $clone;
        }
        /**
         * @return array
         */
        public function getHttpServer(): array {
            return $this->http_server;
        }
        /**
         * @return array
         */
        public function getServerParams(): array {
            return $this->getHttpServer();
        }
        /**
         * @param string $name
         * @param string $value
         * @return self
         */
        public function setHttpServer(string $name,string $value): self {
            $this->http_server[$name] = $value;

            return $this;
        }
        /**
         * @param string $name
         * @param array $list
         * @return self
         */
        public function setHttpServerArray(string $name,array $list): self {
            $this->http_server[$name] = $list;

            return $this;
        }
        /**
         * @return array
         */
        public function getHttpCookie(): array {
            return $this->http_cookie;
        }
        /**
         * @param string $name
         * @param string $value
         * @return self
         */
        public function setHttpCookie(string $name,string $value): self {
            $this->http_cookie[$name] = $value;

            return $this;
        }
        /**
         * @param string $name
         * @param array $list
         * @return self
         */
        public function setHttpCookieArray(string $name,array $list): self {
            $this->http_cookie[$name] = $list;

            return $this;
        }
        /**
         * return ServerRequestInterface
         * @return Request
         */
        // public function withCookieParams(array $cookie): ServerRequestInterface {
        public function withCookieParams(array $cookie): Request {
            $clone = clone $this;
            $clone->cookie = $cookie;

            return $clone;
        }
        /**
         * @return array
         */
        public function getHttpFile(): array {
            return $this->http_file;
        }
        /**
         * @param array $file
         * @return self
         */
        public function setHttpFile(array $file): self {
            $this->http_file = $file;

            return $this;
        }
        /**
         * @param array $file
         * @return self
         */
        private function uploadFilePack(array $file): self {
            if (empty($file)) {
                return $this;
            }

            $upload_file = [];

            foreach ($file as $name => $value) {
                $upload_file[$name] = [];

                $file_count = count($value['name']);
                $file_key = array_keys($value);

                for ($i = 0;$i < $file_count;$i++) {
                    foreach ($file_key as $key) {
                        $upload_file[$name][$i][$key] = $value[$key][$i];
                    }

                    $upload_file[$name][$i] = new UploadedFile($upload_file[$name][$i]);
                }
            }

            $this->setHttpFile($upload_file);

            return $this;
        }
        /**
         * @param array $file
         * return ServerRequestInterface
         * @return Request
         */
        // public function withUploadedFiles(array $file): ServerRequestInterface {
        public function withUploadedFiles(array $file): Request {
            $clone = clone $this;
            $clone->uploadFilePack($file);

            return $clone;
        }
        /**
         * @return array
         */
        public function getHttpSession(): array {
            $_SESSION[self::SESSION_KEY_DEFAULT] = $this->setNullEmptyData($_SESSION[self::SESSION_KEY_DEFAULT]);

            return $_SESSION[self::SESSION_KEY_DEFAULT];
        }
        /**
         * @param array $session_key_value
         * @return self
         */
        public function setHttpSession(array $session_key_value): self {
            $_SESSION[self::SESSION_KEY_DEFAULT] = array_merge($_SESSION[self::SESSION_KEY_DEFAULT],$session_key_value);

            return $this;
        }
        /**
         * @param string $session_key
         * @return self
         */
        public function cleanHttpSession(?string $name = null): self {
            if (!empty($name)) {
                if (isset($_SESSION[self::SESSION_KEY_DEFAULT][$name])) {
                    unset($_SESSION[self::SESSION_KEY_DEFAULT][$name]);
                }

            } else {
                unset($_SESSION[self::SESSION_KEY_DEFAULT]);

                $_SESSION[self::SESSION_KEY_DEFAULT] = [];
            }

            return $this;
        }
        /**
         * @return array
         */
        private function setNullEmptyData($data): array {
            array_walk_recursive($data,function(&$item,$name) {
                if ($item === '') {
                    $item = null;
                }
            });

            return $data;
        }
        /**
         * @param string $name
         * @param string $default null
         * @return string
         * @throws \Error
         */
        public function getAttribute(string $name,?string $default = null): ?string {
            $uri_attribute = $this->getAllAttribute();

            if (empty($uri_attribute)) {
                throw new \Error('URI attributes is empty');
            }

            if (!array_key_exists($name,$uri_attribute)) {
                return $default;
            }

            return $uri_attribute[$name];
        }
        /**
         * @return array
         */
        public function getAllAttribute(): array {
            return $this->uri_attribute;
        }
        /**
         * @return array
         */
        public function getAttributes(): array {
            return $this->getAllAttribute();
        }
        /**
         * @param array $uri_attribute
         * @return self
         */
        public function setAttribute(array $uri_attribute): self {
            $this->uri_attribute = $uri_attribute;

            return $this;
        }
        /**
         * @param string $name
         * @param string $value
         * return ServerRequestInterface
         * @return Request
         */
        // public function withAttribute(string $name,string $value): ServerRequestInterface {
        public function withAttribute(string $name,string $value): Request {
            $clone = clone $this;
            $clone->uri_attribute[$name] = $value;

            return $clone;
        }
        /**
         * @param string $name
         * return ServerRequestInterface
         * @return Request
         */
        // public function withoutAttribute(string $name): ServerRequestInterface {
        public function withoutAttribute(string $name): Request {
            $clone = clone $this;

            unset($clone->uri_attribute[$name]);

            return $clone;
        }
        /**
         * return UriInterface
         * @return Uri
         */
        // public function getUri(): UriInterface {
        public function getUri(): Uri {
            return $this->uri;
        }
        /**
         * param UriInterface $uri
         * @param Uri $uri
         * @return self
         */
        // public function setUri(UriInterface $uri): self {
        public function setUri(Uri $uri): self {
            $this->uri = $uri;

            return $this;
        }
        /**
         * @return array
         */
        public function getRequestMethod(): array {
            return $this->request_method;
        }
        /**
         * @param array $request_method
         * @return $this
         */
        public function setRequestMethod(array $request_method): self {
            $this->request_method = $request_method;

            return $this;
        }
        /**
         * @return string
         */
        public function getMethod(): string {
            return $this->method;
        }
        /**
         * @param string $method
         * @return $this
         */
        public function setMethod(string $method): self {
            $this->method = $method;

            return $this;
        }
        /**
         * @param string $method
         * return ServerRequestInterface
         * @return Request
         */
        // public function withMethod(string $method): ServerRequestInterface {
        public function withMethod(string $method): Request {
            $clone = clone $this;
            $clone->setMethod($method);

            return $clone;
        }
        /**
         * @return string
         */
        public function getRouteId(): string {
            return $this->route_id;
        }
        /**
         * @param string $route_id
         * @return self
         */
        public function setRouteId($route_id): self {
            $this->route_id = $route_id;

            return $this;
        }
        /**
         * @return array|null
         */
        public function getAppUrlList(): ?array {
            return $this->app_url_list;
        }
        /**
         * @param array $app_url_list null
         * @return self
         */
        public function setAppUrlList(?array $app_url_list = null): self {
            $this->app_url_list = $app_url_list;

            return $this;
        }
        /**
         * @return string
         */
        public function getRequestTarget(): string {
            // TODO
            // $http_get = rawurlencode($http_get);
            // $request_target = vsprintf('%s?%s',[$uri,$http_get,]);
            return $this->request_target;
        }
        /**
         * @param string $request_target
         * @return self
         */
        public function setRequestTarget(string $request_target): self {
            $this->request_target = $request_target;

            return $this;
        }
        /**
         * @param string $request_target
         * return ServerRequestInterface
         * @return Request
         */
        // public function withRequestTarget(string $request_target): ServerRequestInterface {
        public function withRequestTarget(string $request_target): Request {
            $clone = clone $this;
            $clone->setRequestTarget($request_target);

            return $clone;
        }
        /**
         * @return self
         * @throws \Error
         */
        private function routeLoad(): self {
            $util = new Util;

            $system = new System();
            $system->readyLoadVar();

            $load_var_app = $system->getLoadVar(System::APP_FILE);

            $app_url_list = [];

            foreach ($load_var_app as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new \Error(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $app_url_list = array_merge($app_url_list,$app_url_class::url());
            }

            $this->setAppUrlList($app_url_list);

            return $this;
        }
        /**
         * @return array null
         */
        public function getRouteAll(): ?array {
            $this->routeLoad();

            $app_url_list = $this->getAppUrlList();

            return $app_url_list;
        }
        /**
         * @param string $id
         * @param array $url_match
         * @return string
         * @throws \Error
         */
        public function getRoute(string $id,?array $url_match = null): string {
            $this->routeLoad();

            $app_url_list = $this->getAppUrlList();

            $flag_id = false;

            foreach ($app_url_list as $route => $url_config) {

                if (count($url_config) != 3) {
                    throw new \Error(vsprintf('route "%s" incorrect format. EX: "/^\/home\/?$/" => ["Home\index",[(GET|POST|PUT|DELETE)],["id_route" => "Label route id"]]',[$route,]));
                }

                $route = str_replace(' ','',$route);

                if ($id == $url_config[2]) {
                    $flag_id = true;

                    break;
                }
            }

            if (empty($flag_id)) {
                throw new \Error(vsprintf('route id %s dont exists',[$id,]));
            }

            $route = str_replace(' ','',$route);
            $match = null;

            preg_match_all('/{([a-z0-9.\-_]+):{1}?([\w^\-|\[\]\\+\(\)\/]+)?}/',$route,$match);

            if (!empty($match)) {
                if (empty($match[0])) {
                    $route = URL_PREFIX.$route;

                    return $route;

                } else {
                    if (empty($url_match) || count($url_match) != count($match[0])) {
                        throw new \Error(vsprintf('route id %s of format %s, contains vars missing',[$id,$route,]));
                    }

                    $route_split_list = explode('/',$route);

                    foreach ($route_split_list as $key => $route_split) {
                        $match = null;

                        preg_match('/{([a-z0-9.\-_]+):{1}?([\w^\-|\[\]\\+\(\)\/]+)?}/',$route_split,$match);

                        if (!empty($match)) {
                            $match[0] = str_replace(['{','}'],'',$match[0]);
                            $match = explode(':',$match[0]);

                            if (!array_key_exists($match[0],$url_match)) {
                                throw new \Error(vsprintf('var %s missing in route %s(%s)',[$match[0],$route,$id]));
                            }

                            $route_split_list[$key] = $url_match[$match[0]];
                        }
                    }

                    $route = URL_PREFIX.implode('/',$route_split_list);

                    return $route;
                }
            }

            throw new \Error(vsprintf('route id %s dont exists',[$id,]));
        }
    }
}
