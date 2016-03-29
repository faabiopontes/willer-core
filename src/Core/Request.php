<?php
/**
  *
  * @author William Borba
  * @package Core/Request
  * @uses Core\Exception\WException
  * @uses Core\Util
  * 
  */
namespace Core {
    use Core\Exception\WException;
    use Core\Util;

    class Request {
        private $uri;
        private $uri_argument;

        public function __construct($uri_argument) {
            $this->setArgument($uri_argument);
            $this->setUri($uri_argument);
        }

        private function setArgument($uri_argument) {
            if (!empty($uri_argument)) {
                array_shift($uri_argument);
            }

            $this->uri_argument = $uri_argument;
        }

        public function getArgument($key = null) {
            if (!empty($key) && !empty($this->uri_argument)) {
                if (array_key_exists($key,$this->uri_argument)) {
                    return $this->uri_argument[$key];
                }
            }

            return $this->uri_argument;
        }

        private function setUri($uri_argument) {
            if (!empty($uri_argument)) {
                $uri = $uri_argument[0];
            }

            $this->uri = $uri;
        }

        public function getUri() {
            return $this->uri;
        }

        public function getHttpGet() {
            return $_GET;
        }

        public function getHttpPost() {
            return $_POST;
        }

        public function getHttpServer() {
            return $_SERVER;
        }

        public function getHttpSession() {
            return $_SESSION;
        }

        public function getHttpCookie() {
            return $_COOKIE;
        }

        public function getRoute($id,$url_match = []) {
            $json_config_load = Util::load('Config');

            if (!array_key_exists('app',$json_config_load)) {
                throw new WException(vsprintf('file app.json not found in directory "%s/Config"',[ROOT_PATH,]));
            }

            $url = [];

            foreach ($json_config_load['app'] as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new WException(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $url_list = $app_url_class::url();

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

                if (!empty($match) && !empty($match[0])) {
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

                    $route = implode('/',$route_split_list);
                }

                return $route;

                throw new WException(vsprintf('route id %s dont exists',[$id,]));
            }
        }
    }
}