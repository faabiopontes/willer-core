<?php
/**
  * @author William Borba
  * @package Core
  * @uses Core\Exception\WException
  * @uses Core\Request
  * @uses Core\Util
  */
namespace Core {
    use Core\{Request,Util};
    use Core\Exception\WException;
    use Workerman\Worker;
    use \DateTime as DateTime;
    use \Exception as Exception;
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
         * @return mixed
         */
        public function ready() {
            $json_config_load = Util::load('Config');

            foreach ($json_config_load['config'] as $key => $value) {
                if (!defined($key)) {
                    define($key,$value);
                }
            }

            if (defined('WORKERMAN') && WORKERMAN == '1' && defined('WORKERMAN_IP') && defined('WORKERMAN_PORT') && defined('WORKERMAN_INSTANCE_MAX') && defined('WORKERMAN_DAEMONIZE') && defined('WORKERMAN_STDOUTFILE')) {
                try {
                    $ready_with_workerman = $this->readyWithWorkerman();

                } catch (WException | Exception $error) {
                    throw $error;
                }

                return $ready_with_workerman;
            }

            $this->readyErrorHandler();

            $request_uri = Util::get($_REQUEST,'REQUEST_URI','/');

            if (!isset($_SESSION['wf'])) {
                $_SESSION['wf'] = [];
            }

            try {
                $ready_route = $this->readyRoute($request_uri);

            } catch (WException | Exception $error) {
                throw $error;
            }

            return $ready_route;
        }
        /**
         * @return mixed
         */
        public function readyWithWorkerman() {
            $workerman_host = vsprintf('%s:%s',[WORKERMAN_IP,WORKERMAN_PORT]);

            Worker::$daemonize = WORKERMAN_DAEMONIZE;
            Worker::$stdoutFile = WORKERMAN_STDOUTFILE;

            $worker_http = new Worker($workerman_host);

            $worker_http->name = Util::get(get_defined_constants(true)['user'],'SYSTEM_NAME',null);
            $worker_http->transport = 'tcp';
            $worker_http->count = WORKERMAN_INSTANCE_MAX;

            $worker_http->onConnect = function($connection) {
                $connection->protocol = 'Workerman\\Protocols\\Http';
                $connection->maxSendBufferSize = 2*1024*1024;
            };

            $worker_http->onMessage = function($connection,$data) {
                $extension_static = ['png','jpg','jpeg','gif','css','js','otf','eot','woff2','woff','ttf','svg','html'];

                $parse_url_path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
                $extension = pathinfo($parse_url_path,PATHINFO_EXTENSION);

                if (in_array($extension,$extension_static)) {
                    $content = file_get_contents('/home/wborba/project/jessi/src'.$_SERVER['REQUEST_URI']);

                    $connection->close($content);

                    return;
                }

                try {
                    $content = $this->readyRoute($_SERVER['REQUEST_URI']);

                } catch (WException | Exception $error) {
                    $date = new DateTime('now');

                    print "---------------------------\n";
                    print vsprintf("Remote: %s:%s\n",[$connection->getRemoteIp(),$connection->getRemotePort(),]);
                    print vsprintf("Date: %s\n",[$date->format('Y-m-d H:i:s u'),]);
                    print vsprintf("Error message: %s\n",[$error->getMessage(),]);
                    print vsprintf("Error trace: %s",[$error->getTraceAsString(),]);
                    print "\n---------------------------\n";

                    $content = 'Entre em contato com o suporte!';
                }

                $connection->send($content);
            };

            Worker::runAll();
        }
        /**
         * @return $this|bool
         * @throws WException
         */
        private function readyErrorHandler() {
            if (empty(defined('DEBUG'))) {
                throw new WException('constant DEBUG not defined');
            }

            if (empty(DEBUG)) {
                return false;
            }

            $whoops_run = new \Whoops\Run();
            $whoops_pretty_page_handler = new \Whoops\Handler\PrettyPageHandler();
            $whoops_json_response_handler = new \Whoops\Handler\JsonResponseHandler();

            $whoops_run->pushHandler($whoops_pretty_page_handler);
            $whoops_run->pushHandler(function ($exception,$inspector,$whoops_run) {
                $inspector->getFrames()->map(function ($frame) {
                    $frame_function = $frame->getFunction();
                    $frame_class = $frame->getClass();
                    $frame_args = $frame->getArgs();

                    if (!empty($frame_function)) {
                        $frame->addComment($frame_function,'Function');
                    }

                    if (!empty($frame_class)) {
                        $frame->addComment($frame_class,'Class');
                    }

                    if (!empty($frame_args)) {
                        $frame->addComment(print_r($frame_args,true),'Args');
                    }

                    return $frame;
                });
            });

            if (\Whoops\Util\Misc::isAjaxRequest()) {
                $whoops_run->pushHandler($whoops_json_response_handler);
            }

            $whoops_pretty_page_handler->addDataTable('Willer Constants',get_defined_constants(true));

            $whoops_run->register();

            return $this;
        }
        /**
         * @param $application_route
         * @param $match
         * @return mixed
         * @throws WException
         */
        private function urlMatch($application_route,$match) {
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

            $json_config_load = Util::load('Config');

            if (empty(defined('ROOT_PATH'))) {
                throw new WException('constant ROOT_PATH not defined');
            }

            if (!array_key_exists('app',$json_config_load)) {
                throw new WException(vsprintf('file app.json not found in directory "%s/Config"',[ROOT_PATH,]));
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
    }
}
