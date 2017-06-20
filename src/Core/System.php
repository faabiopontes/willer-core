<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 * @uses Core\Request
 * @uses Core\Util
 */
namespace Core {
    use Core\{Request,Util};
    /**
     * Class System
     * @constant EXTENSION_STATIC ['png','jpg','jpeg','gif','css','js','otf','eot','woff2','woff','ttf','svg','html','map']
     * @constant CONTENT_ERROR_DEFAULT 'No output response!'
     * @constant CONFIG_FILE 'config'
     * @constant SWOOLE_SWOOLE_WORKER_NUM_DEFAULT 8
     * @constant SWOOLE_SWOOLE_REACTOR_NUM_DEFAULT 8
     * @constant SWOOLE_SWOOLE_DAEMONIZE_DEFAULT 1
     * @constant SWOOLE_SWOOLE_MAX_CONNECTION_DEFAULT 1024
     * @constant SWOOLE_SWOOLE_MAX_REQUEST_DEFAULT 9999999
     * @var $load_var
     */
    class System {
        private const EXTENSION_STATIC = ['png','jpg','jpeg','gif','css','js','otf','eot','woff2','woff','ttf','svg','html','map'];
        private const CONTENT_ERROR_DEFAULT = 'No output response!';
        private const CONFIG_PATH = 'config';
        private const CONFIG_FILE = 'config';
        public const APP_PATH = 'App';
        private const SWOOLE_SWOOLE_WORKER_NUM_DEFAULT = 8;
        private const SWOOLE_SWOOLE_REACTOR_NUM_DEFAULT = 8;
        private const SWOOLE_SWOOLE_DAEMONIZE_DEFAULT = 1;
        private const SWOOLE_SWOOLE_MAX_CONNECTION_DEFAULT = 1024;
        private const SWOOLE_SWOOLE_MAX_REQUEST_DEFAULT = 9999999;

        private $load_var;
        /**
         * System constructor.
         */
        public function __construct() {}
        /**
         * @return self
         */
        public function readyLoadVar(): self {
            $util = new Util;

            try {
                $load_var = $util->load(vsprintf('%s/%s',[ROOT_PATH,self::CONFIG_PATH]));

            } catch (\Error $error) {
                throw $error;
            }

            $this->setLoadVar($load_var);

            return $this;
        }
        /**
         * @return array
         */
        public function getLoadVar(?string $filename = null): array {
            if (!empty($filename) && is_array($this->load_var) && array_key_exists($filename,$this->load_var)) {
                return $this->load_var[$filename];
            }

            return $this->load_var;
        }
        /**
         * @return self
         */
        private function setLoadVar(array $load_var): self {
            $this->load_var = $load_var;

            return $this;
        }
        /**
         * @return void
         * @throws \Error
         */
        public function ready(): void {
            session_start();

            $request = new Request();
            $request->cleanHttpSession();

            $this->readyLoadVar();

            $load_var = $this->getLoadVar();

            if (!empty($load_var)) {
                foreach ($load_var[self::CONFIG_FILE] as $var_key => $var_value) {
                    if (!defined($var_key)) {
                        define($var_key,$var_value);
                    }
                }
            }

            if (defined('SWOOLE') && SWOOLE == '1') {
                try {
                    $this->readyWithSwoole();

                } catch (\Error $error) {
                    throw $error;
                }

                return;
            }

            $request = new Request;

            try {
                $request_uri = $util->contains($request->getHttpServer(),'REQUEST_URI','/')->getString();

                $object_route = $this->readyRoute($request_uri);

                $controller = $object_route->controller;
                $action = $object_route->action;

                $content = $controller->$action();

            } catch (\Error $error) {
                throw $error;
            }

            return;
        }
        /**
         * @return void
         */
        public function readyWithSwoole(): void {
            $util = new Util;

            $get_defined_constants = get_defined_constants(true);
            $get_defined_constants_user = $get_defined_constants['user'];

            try {
                $ip = $util->contains($get_defined_constants_user,'SWOOLE_IP')->getString();
                $port = $util->contains($get_defined_constants_user,'SWOOLE_PORT')->getInteger();
                $log = $util->contains($get_defined_constants_user,'SWOOLE_LOG')->getInteger();
                $log_path = $util->contains($get_defined_constants_user,'SWOOLE_LOG_PATH')->getString();
                $page_error_path = $util->contains($get_defined_constants_user,'SWOOLE_PAGE_ERROR_PATH')->getString();
                $gzip = $util->contains($get_defined_constants_user,'SWOOLE_GZIP')->getInteger();
                $worker_num = $util->contains($get_defined_constants_user,'SWOOLE_WORKER_NUM')->getInteger(self::SWOOLE_SWOOLE_WORKER_NUM_DEFAULT);
                $reactor_num = $util->contains($get_defined_constants_user,'SWOOLE_REACTOR_NUM')->getInteger(self::SWOOLE_SWOOLE_REACTOR_NUM_DEFAULT);
                $daemonize = $util->contains($get_defined_constants_user,'SWOOLE_DAEMONIZE')->getInteger(self::SWOOLE_SWOOLE_DAEMONIZE_DEFAULT);
                $max_connection = $util->contains($get_defined_constants_user,'SWOOLE_MAX_CONNECTION')->getInteger(self::SWOOLE_SWOOLE_MAX_CONNECTION_DEFAULT);
                $max_request = $util->contains($get_defined_constants_user,'SWOOLE_MAX_REQUEST')->getInteger(self::SWOOLE_SWOOLE_MAX_REQUEST_DEFAULT);
                $ssl_cert_file = $util->contains($get_defined_constants_user,'SWOOLE_SSL_CERT_FILE')->getString();
                $ssl_key_file = $util->contains($get_defined_constants_user,'SWOOLE_SSL_KEY_FILE')->getString();
                $ssl_method = $util->contains($get_defined_constants_user,'SWOOLE_SSL_METHOD')->getString();

            } catch (\Error $error) {
                throw new \Error(vsprintf('Constants swoole incomplete(%s)',[$error->getMessage(),]));
            }

            $http_server = new \swoole_http_server($ip,$port);

            if (!empty($log) && !empty($log_path)) {
                $log_path = vsprintf('%s%s',[$log_path,]);

            } else {
                $log_path = null;
            }

            $page_error_content = '';

            if (file_exists('%s',$page_error_path)) {
                $page_error_content = file_get_contents($page_error_path);
            }

            $http_server->set([
                'worker_num' => $worker_num,
                'reactor_num' => $reactor_num,
                'daemonize' => $daemonize,
                'backlog' => '',
                'max_connection' => $max_connection,
                'max_request' => $max_request,
                'log_file' => $log_path,
                'ssl_cert_file' => $ssl_cert_file,
                'ssl_key_file' => $ssl_key_file,
                'ssl_method' => $ssl_method,
            ]);

            $http_server->on('connect',function(\swoole_http_server $http_server_client) use ($log) {
                if (empty($log)) {
                    return;
                }

                $date = new \DateTime('now');

                print "\n------------------------------------------------------\n";
                print "Client connect...\n";
                print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                print "Client stats...\n";
                print_r($http_server_client->stats());
                print "\n------------------------------------------------------\n";
            });

            $http_server->on('Request',function(\swoole_http_request $http_request,\swoole_http_response $http_response) use ($util,$gzip,$log) {
                $_GET = $http_request->get ?? [];
                $_POST = $http_request->post ?? [];
                $_COOKIE = $http_request->cookie ?? [];
                $_FILES = $http_request->files ?? [];
                $_SERVER = $http_request->server ? array_change_key_case($http_request->server,CASE_UPPER) : [];

                $extension_static = self::EXTENSION_STATIC;

                $request = new Request;

                $request_uri = $util->contains($request->getHttpServer(),'REQUEST_URI','/')->getString();

                $parse_url_path = parse_url($request_uri,PHP_URL_PATH);
                $extension = pathinfo($parse_url_path,PATHINFO_EXTENSION);

                if (in_array($extension,$extension_static)) {
                    $content = file_get_contents(vsprintf('%s%s',[ROOT_PATH,$request_uri,]));

                    if ($extension == 'css') {
                        $http_response->header('Content-Type','text/css;charset=utf-8');

                    } else if ($extension == 'js') {
                        $http_response->header('Content-Type','application/javascript;charset=utf-8');
                    }

                    $http_response->end($content);

                    return;
                }

                if (!empty($log)) {
                    $date = new \DateTime('now');

                    print "\n------------------------------------------------------\n";
                    print "Client connect...\n";
                    print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                    print vsprintf("HTTP GET...\n%s",[print_r($request->getHttpGet(),true),]);
                    print vsprintf("HTTP POST...\n%s",[print_r($request->getHttpPost(),true),]);
                    print vsprintf("HTTP SESSION...\n%s",[print_r($request->getHttpSession(),true),]);
                    print vsprintf("HTTP COOKIE...\n%s",[print_r($request->getHttpCookie(),true),]);
                    print vsprintf("HTTP FILES...\n%s",[print_r($request->getHttpFiles(),true),]);
                    print vsprintf("HTTP SERVER...\n%s",[print_r($request->getHttpServer(),true),]);
                    print vsprintf("HTTP HEADER...\n%s",[print_r($http_request->header,true),]);
                    print "\n------------------------------------------------------\n";
                }

                try {
                    $object_route = $this->readyRoute($request_uri);

                    $controller = $object_route->controller;
                    $action = $object_route->action;

                    $response = $controller->$action();

                    $content = $response->getBody();

                } catch (\Error $error) {
                    $date = new \DateTime('now');

                    print "\n------------------------------------------------------\n";
                    print "Client connect...\n";
                    print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                    print "Throw Exception...\n";
                    print vsprintf("HTTP GET...\n%s",[print_r($request->getHttpGet(),true),]);
                    print vsprintf("HTTP POST...\n%s",[print_r($request->getHttpPost(),true),]);
                    print vsprintf("HTTP SESSION...\n%s",[print_r($request->getHttpSession(),true),]);
                    print vsprintf("HTTP COOKIE...\n%s",[print_r($request->getHttpCookie(),true),]);
                    print vsprintf("HTTP FILES...\n%s",[print_r($request->getHttpFiles(),true),]);
                    print vsprintf("HTTP SERVER...\n%s",[print_r($request->getHttpServer(),true),]);
                    print vsprintf("HTTP HEADER...\n%s",[print_r($http_request->header,true),]);
                    print vsprintf("Error message...\n%s\n",[$error->getMessage(),]);
                    print vsprintf("Error trace...\n%s",[$error->getTraceAsString(),]);
                    print "\n------------------------------------------------------";

                    $content = self::CONTENT_ERROR_DEFAULT;

                    if (!empty($page_error_content)) {
                        $content = $page_error_content;
                    }
                }

                if (!is_null($gzip) && $gzip == 1) {
                    $http_response->gzip(1);
                }

                $http_response->end($content);

                return;
            });

            $http_server->start();

            return;
        }
        /**
         * @param string $request_uri
         * @return object
         * @throws \Error
         */
        private function readyRoute(string $request_uri): \stdClass {
            if (!empty(defined('URL_PREFIX'))) {
                $request_uri = str_replace(URL_PREFIX,'',$request_uri);
            }

            $request_uri_strstr = strstr($request_uri,'?',true);

            if (!empty($request_uri_strstr)) {
                $request_uri = $request_uri_strstr;
            }

            $load_var = $this->getLoadVar();

            if (empty(defined('ROOT_PATH'))) {
                throw new \Error('constant ROOT_PATH not defined');
            }

            $app_url_class = vsprintf('\%s\Url',[self::APP_PATH,]);

            if (!class_exists($app_url_class,true)) {
                throw new \Error(vsprintf('class "%s" not found',[$app_url_class,]));
            }

            $url_list = $app_url_class::url();

            foreach ($url_list as $route => $app_route) {
                if (count($app_route) != 3) {
                    throw new \Error(vsprintf('route %s incorrect format. EX: "/home/page/test/" => ["Home\index",[(GET|POST|PUT|DELETE)],"id_route"]',[$route,]));
                }

                $route = str_replace(' ','',$route);
                $route_split_list = explode('/',$route);

                foreach ($route_split_list as $key => $route_split) {
                    $match = null;

                    preg_match('/{([a-z0-9.\-_]+):{1}?([\w^\-|\[\]\\+\(\)\/]+)?}/',$route_split,$match);

                    if (!empty($match)) {
                        $match[0] = str_replace(['{','}'],'',$match[0]);
                        $match = explode(':',$match[0]);

                        if (!empty($match[1])) {
                            $route_split_list[$key] = vsprintf('(?<%s>%s)',[$match[0],$match[1],]);

                        } else {
                            $route_split_list[$key] = vsprintf('(?<%s>%s)',[$match[0],'[a-z0-9]+',]);
                        }
                    }
                }

                $route_er = vsprintf('/^%s$/',[implode('\/',$route_split_list),]);

                if (preg_match($route_er,$request_uri,$match)) {
                    try {
                        $object_route = $this->urlMatch($app_route,$match);

                    } catch (\Error $error) {
                        throw $error;
                    }

                    return $object_route;
                }
            }

            throw new \Error(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
        /**
         * @param array $app_route
         * @param array $match
         * @return object
         * @throws \Error
         */
        private function urlMatch(array $app_route,array $match): \stdClass {
            $app_route_explode = explode('@',$app_route[0]);

            if (count($app_route_explode) != 2) {
                throw new \Error('Route incorrect format');
            }

            $app_route_list = explode('\\',$app_route_explode[0]);
            $controller_action = $app_route_explode[1];

            $app_path = implode('\\',$app_route_list);

            $app = vsprintf('%s\\%s',[self::APP_PATH,$app_path]);

            $route_id = $app_route[2];

            $request = new Request();
            $request->setAttribute($match);
            $request->setRouteId($route_id);

            $new_app = new $app($request);

            if (empty(method_exists($new_app,$controller_action))) {
                throw new \Error(vsprintf('method "%s" not found in class "%s"',[$controller_action,$app]));
            }

            $object_route = new \stdClass;

            try {
                $object_route->controller = $new_app;
                $object_route->action = $controller_action;

            } catch (\Error $error) {
                throw $error;
            }

            return $object_route;
        }
    }
}
