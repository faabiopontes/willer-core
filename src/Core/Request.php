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
        private $uri_match;
        private $url_route;

        public function __construct($uri_match) {
            $this->setUriMatch($uri_match);
            $this->setUri($uri_match);
        }

        private function setUriMatch($uri_match) {
            if (!empty($uri_match)) {
                array_shift($uri_match);
            }

            $this->uri_match = $uri_match;
        }

        public function getUriMatch() {
            return $this->uri_match;
        }

        private function setUri($uri_match) {
            if (!empty($uri_match)) {
                $uri = $uri_match[0];
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

        public function getHttpSession() {
            return $_SESSION;
        }

        public function getRoute($id) {
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

                foreach ($url_list as $route => $url_config) {
                    if (count($url_config) != 3) {
                        throw new WException(vsprintf('route %s incorrect format. EX: "/^\/home\/?$/" => ["Home\index",[(GET|POST|PUT|DELETE)],"id_route"]',[$route,]));
                    }

                    if ($id == $url_config[2]) {
                        // return $route;
                        $route = str_replace(['/','^','$','?','!'],'',$route);
                        $route = str_replace(['\\'],'/',$route);

                        // $route = preg_quote($route);
                        // $route = str_replace(['\\'],'',$route);
                        return $route;

                        break;
                    }
                }

                throw new WException(vsprintf('route id %s dont exists',[$id,]));
            }
        }
    }
}
