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
     * @var $load_var
     */
    class System {
        private const EXTENSION_STATIC = ['png','jpg','jpeg','gif','css','js','otf','eot','woff2','woff','ttf','svg','html','map'];
        private const CONTENT_ERROR_DEFAULT = 'No output response!';

        private $load_var;
        /**
         * System constructor.
         */
        public function __construct() {
            session_start();

            $request = new Request();
            $request->cleanHttpSession();

            $util = new Util;

            try {
                $load_var = $util->load('config');

            } catch (\Error $error) {
                throw $error;
            }

            $this->setLoadVar($load_var);
        }
        /**
         * @return array
         */
        private function getLoadVar(): array {
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
            $load_var = $this->getLoadVar();

            if (!empty($load_var)) {
                foreach ($load_var['config'] as $var_key => $var_value) {
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
            $request = new Request;
            $util = new Util;

            $get_defined_constants = get_defined_constants(true);
            $get_defined_constants_user = $get_defined_constants['user'];

            try {
                $ip = $util->contains($get_defined_constants_user,'SWOOLE_IP')->getString();
                $port = $util->contains($get_defined_constants_user,'SWOOLE_PORT')->getInteger();
                $log_level = $util->contains($get_defined_constants_user,'SWOOLE_LOG_LEVEL')->getInteger(1);
                $log_path = $util->contains($get_defined_constants_user,'SWOOLE_LOG_PATH')->getString();
                $page_error_path = $util->contains($get_defined_constants_user,'SWOOLE_PAGE_ERROR_PATH')->getString();
                $gzip = $util->contains($get_defined_constants_user,'SWOOLE_GZIP')->getInteger();
                $worker_num = $util->contains($get_defined_constants_user,'SWOOLE_WORKER_NUM')->getInteger(1);
                $reactor_num = $util->contains($get_defined_constants_user,'SWOOLE_REACTOR_NUM')->getInteger(1);
                $daemonize = $util->contains($get_defined_constants_user,'SWOOLE_DAEMONIZE')->getInteger(1);
                $max_connection = $util->contains($get_defined_constants_user,'SWOOLE_MAX_CONNECTION')->getInteger(1024);
                $max_request = $util->contains($get_defined_constants_user,'SWOOLE_MAX_REQUEST')->getInteger(10);
                $ssl_cert_file = $util->contains($get_defined_constants_user,'SWOOLE_SSL_CERT_FILE')->getString();
                $ssl_key_file = $util->contains($get_defined_constants_user,'SWOOLE_SSL_KEY_FILE')->getString();
                $ssl_method = $util->contains($get_defined_constants_user,'SWOOLE_SSL_METHOD')->getString();

            } catch (\Error $error) {
                throw new \Error(vsprintf('Constants swoole incomplete',[$error->getMessage(),]));
            }

            $http_server = new \swoole_http_server($ip,$port);

            if (!empty($log_level) && !empty($log_path)) {
                $log_path = vsprintf('%s%s',[UPKEEP_PATH,$log_path,]);

            } else {
                $log_path = null;
            }

            $page_error_content = '';

            if (file_exists(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]))) {
                $page_error_content = file_get_contents(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]));
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

            $http_server->on('connect',function(\swoole_http_server $http_server_client) use ($log_level) {
                if (!empty($log_level) || $log_level < 2) {
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

            $http_server->on('Request',function(\swoole_http_request $http_request,\swoole_http_response $http_response) use ($request,$util,$gzip) {
                $_GET = $http_request->get ?? [];
                $_POST = $http_request->post ?? [];
                $_COOKIE = $http_request->cookie ?? [];
                $_FILES = $http_request->files ?? [];
                $_SERVER = $http_request->server ? array_change_key_case($http_request->server,CASE_UPPER) : [];

                $extension_static = self::EXTENSION_STATIC;

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
                    print "\n------------------------------------------------------\n";

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

            if (!array_key_exists('app',$load_var)) {
                throw new \Error(vsprintf('file app.json not found in directory "%s/config"',[ROOT_PATH,]));
            }

            foreach ($load_var['app'] as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new \Error(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $url_list = $app_url_class::url();

                foreach ($url_list as $route => $application_route) {
                    if (count($application_route) != 3) {
                        throw new \Error(vsprintf('route %s incorrect format. EX: "/home/page/test/" => ["Home\index",[(GET|POST|PUT|DELETE)],"id_route"]',[$route,]));
                    }

                    $application_route[0] = vsprintf('%s\%s',[$app,$application_route[0]]);

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
                            $object_route = $this->urlMatch($application_route,$match);

                        } catch (\Error $error) {
                            throw $error;
                        }

                        return $object_route;
                    }
                }
            }

            throw new \Error(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
        /**
         * @param array $application_route
         * @param array $match
         * @return object
         * @throws \Error
         */
        private function urlMatch(array $application_route,array $match): \stdClass {
            $application_route_list = explode('\\',$application_route[0]);

            $bundle = array_shift($application_route_list);
            $controller_action = array_pop($application_route_list);
            $application_path = implode('\\',$application_route_list);

            $application = vsprintf('Application\\%s\\Controller\\%s',[$bundle,$application_path]);

            $uri = null;

            if (!empty($match)) {
                $uri = $match[0];
                array_shift($match);
            }

            $request_method = $application_route[1];
            $route_id = $application_route[2];

            $request = new Request();
            $request->setArgument($match);
            $request->setRequestMethod($request_method);
            $request->setRouteId($route_id);
            $request->setUri($uri);

            $new_application = new $application($request);

            if (empty(method_exists($new_application,$controller_action))) {
                throw new \Error(vsprintf('method "%s" not found in class "%s"',[$controller_action,$application]));
            }

            $object_route = new \stdClass;

            try {
                $object_route->controller = $new_application;
                $object_route->action = $controller_action;

            } catch (\Error $error) {
                throw $error;
            }

            return $object_route;
        }
    }
}
