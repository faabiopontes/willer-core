<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\WUtil
 * @uses Core\Exception\WException
 */
namespace Core {
    use Core\WUtil;
    use Core\Exception\WException;
    /**
     * Class Request
     * @constant SESSION_KEY_DEFAULT 'wf'
     * @var string $uri
     * @var array $uri_argument
     * @var array $request_method
     * @var string $route_id
     * @var array $app_url_list
     */
    class Request {
        private const SESSION_KEY_DEFAULT = 'wf';

        private $uri;
        private $uri_argument;
        private $request_method;
        private $route_id;
        private $app_url_list;
        /**
         * Request constructor.
         * @param array $uri_argument []
         * @param array $request_method []
         * @param string $route_id null
         */
        public function __construct(array $uri_argument = [],array $request_method = [],?string $route_id): void {
            $this->setArgument($uri_argument);
            $this->setRequestMethod($request_method);
            $this->setRouteId($route_id);
        }
        /**
         * @param string $key
         * @return string
         * @throws WException
         */
        public function getArgument(string $key): string {
            if (empty($this->uri_argument)) {
                throw new WException('URI arguments is empty');
            }

            if (!array_key_exists($key,$this->uri_argument)) {
                throw new WException(vsprintf('URI arguments key "%s" dont find',[$key,]));
            }

            return $this->uri_argument[$key];
        }
        /**
         * @return array
         */
        public function getAllArgument(): array {
            return $this->uri_argument;
        }
        /**
         * @param array $uri_argument
         * @return self
         */
        public function setArgument(array $uri_argument): self {
            $this->uri_argument = $uri_argument;

            return $this;
        }
        /**
         * @return string
         */
        public function getUri(): string {
            return $this->uri;
        }
        /**
         * @param string $uri
         * @return self
         */
        public function setUri($uri): self {
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
        public function setAppUrlList(?array $app_url_list): self {
            $this->app_url_list = $app_url_list;

            return $this;
        }
        /**
         * @return array
         */
        public function getHttpGet(): array {
            return $_GET;
        }
        /**
         * @return array
         */
        public function getHttpPost(): array {
            return $_POST;
        }
        /**
         * @return array
         */
        public function getHttpServer(): array {
            return $_SERVER;
        }
        /**
         * @return array
         */
        public function getHttpSession(): array {
            return $_SESSION[self::SESSION_KEY_DEFAULT];
        }
        /**
         * @param string $session_key
         * @param string $session_value
         * @return self
         */
        public function setHttpSession(string $session_key,string $session_value): self {
            $_SESSION[self::SESSION_KEY_DEFAULT][$session_key] = $session_value;

            return $this;
        }
        /**
         * @param string $session_key
         * @return self
         */
        public function cleanHttpSession(?string $session_key): self {
            if (!empty($session_key)) {
                if (isset($_SESSION[self::SESSION_KEY_DEFAULT][$session_key])) {
                    unset($_SESSION[self::SESSION_KEY_DEFAULT][$session_key]);
                }

            }

            unset($_SESSION[self::SESSION_KEY_DEFAULT]);

            $_SESSION[self::SESSION_KEY_DEFAULT] = [];

            return $this;
        }
        /**
         * @return array
         */
        public function getHttpCookie(): array {
            return $_COOKIE;
        }
        /**
         * @return array
         */
        public function getHttpFiles(): array {
            return $_FILES;
        }
        /**
         * @param string $id
         * @param array $url_match []
         * @return string
         * @throws WException
         */
        public function getRoute(string $id,array $url_match = []): string {
            $app_url_list = $this->getAppUrlList();

            if (empty($app_url_list)) {
                $wutil = new WUtil;

                $load_var = $wutil::load('config');

                $app_url_list = [];

                foreach ($load_var['app'] as $app) {
                    $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                    if (!class_exists($app_url_class,true)) {
                        throw new WException(vsprintf('class "%s" not found',[$app_url_class,]));
                    }

                    $app_url_list = array_merge($app_url_list,$app_url_class::url());
                }

                $this->setAppUrlList($app_url_list);
            }

            $flag_id = false;

            foreach ($app_url_list as $route => $url_config) {
                if (count($url_config) != 3) {
                    throw new WException(vsprintf('route %s incorrect format. EX: "/^\/home\/?$/" => ["Home\index",[(GET|POST|PUT|DELETE)],"id_route"]',[$route,]));
                }

                $route = str_replace(' ','',$route);

                if ($id == $url_config[2]) {
                    $flag_id = true;

                    break;
                }
            }

            if (empty($flag_id)) {
                throw new WException(vsprintf('route id %s dont exists',[$id,]));
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
                        throw new WException(vsprintf('route id %s of format %s, contains vars missing',[$id,$route,]));
                    }

                    $route_split_list = explode('/',$route);

                    foreach ($route_split_list as $key => $route_split) {
                        $match = null;

                        preg_match('/{([a-z0-9.\-_]+):{1}?([\w^\-|\[\]\\+\(\)\/]+)?}/',$route_split,$match);

                        if (!empty($match)) {
                            $match[0] = str_replace(['{','}'],'',$match[0]);
                            $match = explode(':',$match[0]);

                            if (!array_key_exists($match[0],$url_match)) {
                                throw new WException(vsprintf('var %s missing in route %s(%s)',[$match[0],$route,$id]));
                            }

                            $route_split_list[$key] = $url_match[$match[0]];
                        }
                    }

                    $route = URL_PREFIX.implode('/',$route_split_list);

                    return $route;
                }
            }

            throw new WException(vsprintf('route id %s dont exists',[$id,]));
        }
    }
}
