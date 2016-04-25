<?php
/**
  * @author William Borba
  * @package Core
  * @uses Core\Exception\WException
  * @uses Core\Util
  */
namespace Core {
    use Core\Exception\WException;
    use Core\Util;
    /**
     * Class Request
     * @package Core
     * @property string $uri
     * @property array $uri_argument
     */
    class Request {
        private $uri;
        private $uri_argument;
        /**
         * Request constructor.
         * @param $uri_argument
         */
        public function __construct($uri_argument = null) {
            $this->setArgument($uri_argument);
            $this->setUri($uri_argument);
        }
        /**
         * @param $uri_argument
         * @return $this
         */
        private function setArgument($uri_argument) {
            if (!empty($uri_argument)) {
                array_shift($uri_argument);
            }

            $this->uri_argument = $uri_argument;

            return $this;
        }
        /**
         * @param null $key
         * @return mixed
         */
        public function getArgument($key = null) {
            if (!empty($key) && !empty($this->uri_argument)) {
                if (array_key_exists($key,$this->uri_argument)) {
                    return $this->uri_argument[$key];
                }
            }

            return $this->uri_argument;
        }
        /**
         * @param $uri_argument
         * @return $this
         */
        private function setUri($uri_argument) {
            if (!empty($uri_argument)) {
                $this->uri = $uri_argument[0];
            }

            return $this;
        }
        /**
         * @return mixed
         */
        public function getUri() {
            return $this->uri;
        }
        /**
         * @return mixed
         */
        public function getHttpGet() {
            return $_GET;
        }
        /**
         * @return mixed
         */
        public function getHttpPost() {
            return $_POST;
        }
        /**
         * @return mixed
         */
        public function getHttpServer() {
            return $_SERVER;
        }
        /**
         * @return mixed
         */
        public function getHttpSession() {
            return $_SESSION;
        }
        /**
         * @return mixed
         */
        public function getHttpCookie() {
            return $_COOKIE;
        }
        /**
         * @param $id
         * @param array $url_match
         * @return int|string
         * @throws WException
         */
        public function getRoute($id, $url_match = []) {
            $json_config_load = Util::load('Config');

            if (empty(defined('ROOT_PATH'))) {
                throw new WException('constant ROOT_PATH not defined');
            }

            if (!array_key_exists('app',$json_config_load)) {
                throw new WException(vsprintf('file app.json not found in directory "%s/Config"',[ROOT_PATH,]));
            }

            $url_list = [];

            foreach ($json_config_load['app'] as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new WException(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $url_list = array_merge($url_list,$app_url_class::url());
            }

            $flag_id = false;

            foreach ($url_list as $route => $url_config) {
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
                    return URL_PREFIX.$route;

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
