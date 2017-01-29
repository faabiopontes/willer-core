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
    /**
     * Class System
     * @package Core
     */
    class System {
        /**
         * System constructor.
         */
        public function __construct() {
            $this->readyApp();
        }
        /**
         * @return mixed
         */
        private function readyApp() {
            $this->readyErrorHandler();

            if (empty(defined('REQUEST_URI'))) {
                throw new WException('constant REQUEST_URI not defined');
            }

            session_start();

            if (!isset($_SESSION['wf'])) {
                $_SESSION['wf'] = [];
            }

            $ready_url_route = $this->readyUrlRoute(REQUEST_URI);

            return $ready_url_route;
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

            $whoops_pretty_page_handler->addDataTable('Willer Contants',array(
                'URL_PREFIX' => URL_PREFIX,
                'REQUEST_URI' => REQUEST_URI,
                'ROOT_PATH' => ROOT_PATH,
                'DATABASE_PATH' => DATABASE_PATH,
                'DATABASE' => DATABASE,));

            $whoops_run->register();

            return $this;
        }
        /**
         * @param $application_route
         * @param $match
         * @return mixed
         * @throws WException
         */
        private function urlRoute($application_route,$match) {
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

            return $new_application->$controller_action();
        }

        /**
         * @param $request_uri
         * @return mixed
         * @throws WException
         */
        private function readyUrlRoute($request_uri) {
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
                        $route_er = $this->urlRoute($url_config,$match);

                        return $route_er;
                    }
                }
            }

            throw new WException(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
    }
}
