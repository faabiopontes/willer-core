<?php
/**
  * @author William Borba
  * @package Core
  * @uses Core\Exception\WException
  * @uses Core\Request
  * @uses Core\WUtil
  */
namespace Core {
    use Core\{Request,WUtil};
    use Core\Exception\WException;
    use \DateTime as DateTime;
    /**
     * Class System
     * @package Core
     */
    class System {
        /**
         * System constructor.
         */
        public function __construct() {
            session_start();
        }
        /**
         * @return void
         * @throws WException
         */
        public function ready(): void {
            $wutil = new WUtil;

            $json_config_load = $wutil->load('config');

            foreach ($json_config_load['config'] as $key => $value) {
                if (!defined($key)) {
                    define($key,$value);
                }
            }

            if (!isset($_SESSION['wf'])) {
                $_SESSION['wf'] = [];
            }

            if (defined('SWOOLE') && SWOOLE == '1' && defined('SWOOLE_IP') && !empty(SWOOLE_IP) && defined('SWOOLE_PORT') && !empty(SWOOLE_PORT)) {
                try {
                    $this->readyWithSwoole();

                } catch (WException $error) {
                    throw $error;
                }

                return;
            }

            $request_uri = $wutil->arrayContains($_REQUEST,'REQUEST_URI','/')->getString();

            try {
                $ready_route = $this->readyRoute($request_uri);

            } catch (WException $error) {
                throw $error;
            }

            return;
        }
        /**
         * @return mixed
         */
        public function readyWithSwoole(): void {
            $wutil = new WUtil;

            $http_server = new \swoole_http_server(SWOOLE_IP,SWOOLE_PORT);

            $log_level = wutil::arrayContains(get_defined_constants(),'SWOOLE_LOG_LEVEL',false)->getString();
            $log_path = wutil::arrayContains(get_defined_constants(),'SWOOLE_LOG_PATH',false)->getString();

            if (!empty($log_level) && !empty($log_path)) {
                $log_path = vsprintf('%s%s',[UPKEEP_PATH,$log_path,]);

            } else {
                $log_path = null;
            }

            $http_server->set([
                'worker_num' => 1,
                'reactor_num' => 1,
                'daemonize' => $wutil->arrayContains(get_defined_constants(),'SWOOLE_DAEMONIZE','1')->getString(),
                'backlog' => '',
                'max_connection' => 1024,
                'max_request' => 10,
                'log_file' => $log_path,
                'ssl_cert_file' => false,
                'ssl_key_file' => false,
                'ssl_method' => false,
            ]);

            $http_server->on('connect',function(\swoole_http_server $http_server_client) {
                $log_level = $wutil->arrayContains(get_defined_constants(),'SWOOLE_LOG_LEVEL',false)->getString();

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

            $http_server->on('Request',function(\swoole_http_request $http_request,\swoole_http_response $http_response) {
                $_GET = $http_request->get ?? [];
                $_POST = $http_request->post ?? [];
                $_COOKIE = $http_request->cookie ?? [];
                $_FILES = $http_request->files ?? [];
                $_SERVER = $http_request->server ? array_change_key_case($http_request->server,CASE_UPPER) : [];

                $extension_static = ['png','jpg','jpeg','gif','css','js','otf','eot','woff2','woff','ttf','svg','html','map'];

                $parse_url_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
                $extension = pathinfo($parse_url_path,PATHINFO_EXTENSION);

                if (in_array($extension,$extension_static)) {
                    $content = file_get_contents(vsprintf('%s%s',[ROOT_PATH,$_SERVER['REQUEST_URI'],]));

                    if ($extension == 'css') {
                        $http_response->header('Content-Type','text/css;charset=utf-8');

                    } else if ($extension == 'js') {
                        $http_response->header('Content-Type','application/javascript;charset=utf-8');
                    }

                    $http_response->end($content);

                    return;
                }

                try {
                    $content = $this->readyRoute($_SERVER['REQUEST_URI']);

                } catch (WException | Exception $error) {
                    $date = new DateTime('now');

                    print "\n------------------------------------------------------\n";
                    print "Client connect...\n";
                    print "Throw Exception...\n";
                    print vsprintf("Date: [%s]\n",[$date->format('Y-m-d H:i:s u'),]);
                    print vsprintf("HTTP GET...\n%s",[print_r($_GET,true),]);
                    print vsprintf("HTTP POST...\n%s",[print_r($_POST,true),]);
                    print vsprintf("HTTP COOKIE...\n%s",[print_r($_COOKIE,true),]);
                    print vsprintf("HTTP FILES...\n%s",[print_r($_FILES,true),]);
                    print vsprintf("HTTP SERVER...\n%s",[print_r($_SERVER,true),]);
                    print vsprintf("HTTP HEADER...\n%s",[print_r($http_request->header,true),]);
                    print vsprintf("Error message...\n%s\n",[$error->getMessage(),]);
                    print vsprintf("Error trace...\n%s",[$error->getTraceAsString(),]);
                    print "\n------------------------------------------------------\n";

                    $page_error_path = $wutil->arrayContains(get_defined_constants(),'SWOOLE_PAGE_ERROR_PATH',false)->getString();

                    $content = 'No output response!';

                    if (file_exists(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]))) {
                        $content = file_get_contents(vsprintf('%s%s',[UPKEEP_PATH,$page_error_path]));
                    }
                }

                $gzip = $wutil->arrayContains(get_defined_constants(),'SWOOLE_GZIP',false)->getString();

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
         * @param $request_uri
         * @return mixed
         * @throws WException
         */
        private function readyRoute($request_uri) {
            if (!empty(defined('URL_PREFIX'))) {
                $request_uri = str_replace(URL_PREFIX,'',$request_uri);
            }

            $request_uri_strstr = strstr($request_uri,'?',true);

            if (!empty($request_uri_strstr)) {
                $request_uri = $request_uri_strstr;
            }

            $json_config_load = Util::load('config');

            if (empty(defined('ROOT_PATH'))) {
                throw new WException('constant ROOT_PATH not defined');
            }

            if (!array_key_exists('app',$json_config_load)) {
                throw new WException(vsprintf('file app.json not found in directory "%s/config"',[ROOT_PATH,]));
            }

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

                    $url_config[0] = vsprintf('%s\%s',[$app,$url_config[0]]);

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
                            $url_match = $this->urlMatch($url_config,$match);

                        } catch (WException | Exception $error) {
                            throw $error;
                        }

                        return $url_match;
                    }
                }
            }

            throw new WException(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
        /**
         * @param array $application_route
         * @param array $match
         * @return object
         * @throws WException|Exception
         */
        private function urlMatch(array $application_route,array $match): string {
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

            $request = new Request($match,$application_route[1],$application_route[2]);
            $request->setUri($uri);

            $new_application = new $application($request);

            if (empty(method_exists($new_application,$controller_action))) {
                throw new WException(vsprintf('method "%s" not found in class "%s"',[$controller_action,$application]));
            }

            try {
                $context = $new_application->$controller_action();

            } catch (WException | Exception $error) {
                throw $error;
            }

            return $context;
        }
    }
}
