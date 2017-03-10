<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\Request
 * @uses Core\WUtil
 * @uses Core\Exception\WException
 * @uses \DateTime
 */
namespace Core {
    use Core\{Request,WUtil};
    use Core\Exception\WException;
    use \DateTime as DateTime;
    use \stdClass as stdClass;
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

            $wutil = new WUtil;

            $load_var = $wutil->load('config');

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
         * @throws WException
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

            if (defined('SWOOLE') && SWOOLE == '1' && defined('SWOOLE_IP') && !empty(SWOOLE_IP) && defined('SWOOLE_PORT') && !empty(SWOOLE_PORT)) {
                try {
                    $this->readyWithSwoole();

                } catch (WException $error) {
                    throw $error;
                }

                return;
            }

            $request = new Request;

            $request_uri = $wutil->contains($request->getHttpServer(),'REQUEST_URI','/')->getString();

            try {
                $object_route = $this->readyRoute($request_uri);

                $controller = $object_route->controller;
                $action = $object_route->action;

                $content = $controller->$action();

            } catch (WException $error) {
                throw $error;
            }

            print $content;

            return;
        }
        /**
         * @return void
         */
        public function readyWithSwoole(): void {
            $request = new Request;
            $wutil = new WUtil;

            $http_server = new \swoole_http_server(SWOOLE_IP,SWOOLE_PORT);

            $get_defined_constants = get_defined_constants();

            $log_level = $wutil->contains($get_defined_constants,'SWOOLE_LOG_LEVEL',false)->getString();
            $log_path = $wutil->contains($get_defined_constants,'SWOOLE_LOG_PATH',false)->getString();

            if (!empty($log_level) && !empty($log_path)) {
                $log_path = vsprintf('%s%s',[UPKEEP_PATH,$log_path,]);

            } else {
                $log_path = null;
            }

            $page_error_path = $wutil->contains($get_defined_constants,'SWOOLE_PAGE_ERROR_PATH',false)->getString();
            $page_error_content = '';

            if (file_exists(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]))) {
                $page_error_content = file_get_contents(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]));
            }

            $gzip = $wutil->contains($get_defined_constants,'SWOOLE_GZIP',false)->getString();
            $log_level = $wutil->contains($get_defined_constants,'SWOOLE_LOG_LEVEL',false)->getString();

            $http_server->set([
                'worker_num' => $wutil->contains($get_defined_constants,'SWOOLE_WORKER_NUM','1')->getString(),
                'reactor_num' => $wutil->contains($get_defined_constants,'SWOOLE_REACTOR_NUM','1')->getString(),
                'daemonize' => $wutil->contains($get_defined_constants,'SWOOLE_DAEMONIZE','1')->getString(),
                'backlog' => '',
                'max_connection' => $wutil->contains($get_defined_constants,'SWOOLE_MAX_CONNECTION','1024')->getString(),
                'max_request' => $wutil->contains($get_defined_constants,'SWOOLE_MAX_REQUEST','10')->getString(),
                'log_file' => $log_path,
                'ssl_cert_file' => $wutil->contains($get_defined_constants,'SWOOLE_SSL_CERT_FILE',false)->getString(),
                'ssl_key_file' => $wutil->contains($get_defined_constants,'SWOOLE_SSL_KEY_FILE',false)->getString(),
                'ssl_method' => $wutil->contains($get_defined_constants,'SWOOLE_SSL_METHOD',false)->getString(),
            ]);

            $http_server->on('connect',function(\swoole_http_server $http_server_client) {
                if (!empty($log_level) && $log_level < '2') {
                    return;
                }

                $date = new DateTime('now');

                print "\n------------------------------------------------------\n";
                print "Client connect...\n";
                print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                print "Client stats...\n";
                print_r($http_server_client->stats());
                print "\n------------------------------------------------------\n";
            });

            $http_server->on('Request',function(\swoole_http_request $http_request,\swoole_http_response $http_response) use ($request,$wutil) {
                $_GET = $http_request->get ?? [];
                $_POST = $http_request->post ?? [];
                $_COOKIE = $http_request->cookie ?? [];
                $_FILES = $http_request->files ?? [];
                $_SERVER = $http_request->server ? array_change_key_case($http_request->server,CASE_UPPER) : [];

                $extension_static = self::EXTENSION_STATIC;

                $request_uri = $wutil->contains($request->getHttpServer(),'REQUEST_URI','/')->getString();

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

                    $content = $controller->$action();

                } catch (WException $error) {
                    $date = new DateTime('now');

                    print "\n------------------------------------------------------\n";
                    print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                    print "Client connect...\n";
                    print "Throw Exception...\n";
                    print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
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

                if (!empty($gzip)) {
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
         * @throws WException
         */
        private function readyRoute(string $request_uri): stdClass {
            if (!empty(defined('URL_PREFIX'))) {
                $request_uri = str_replace(URL_PREFIX,'',$request_uri);
            }

            $request_uri_strstr = strstr($request_uri,'?',true);

            if (!empty($request_uri_strstr)) {
                $request_uri = $request_uri_strstr;
            }

            $load_var = $this->getLoadVar();

            if (empty(defined('ROOT_PATH'))) {
                throw new WException('constant ROOT_PATH not defined');
            }

            if (!array_key_exists('app',$load_var)) {
                throw new WException(vsprintf('file app.json not found in directory "%s/config"',[ROOT_PATH,]));
            }

            foreach ($load_var['app'] as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new WException(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $url_list = $app_url_class::url();

                foreach ($url_list as $route => $application_route) {
                    if (count($application_route) != 3) {
                        throw new WException(vsprintf('route %s incorrect format. EX: "/home/page/test/" => ["Home\index",[(GET|POST|PUT|DELETE)],"id_route"]',[$route,]));
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

                        } catch (WException $error) {
                            throw $error;
                        }

                        return $object_route;
                    }
                }
            }

            throw new WException(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
        /**
         * @param array $application_route
         * @param array $match
         * @return object
         * @throws WException
         */
        private function urlMatch(array $application_route,array $match): stdClass {
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
                throw new WException(vsprintf('method "%s" not found in class "%s"',[$controller_action,$application]));
            }

            $object_route = new stdClass;

            try {
                $object_route->controller = $new_application;
                $object_route->action = $controller_action;

            } catch (WException $error) {
                throw $error;
            }

            return $object_route;
        }
    }
}
