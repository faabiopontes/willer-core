<?php
/**
  *
  * @author William Borba
  * @package Core/System
  * @uses Core\Exception\WF_Exception
  * @uses Core\Util
  * 
  */
namespace Core {
    use Core\Exception\WF_Exception;
    use Core\Util;

    class System {
        public function __construct() {
            $this->readyApp();
        }

        private function readyApp() {
            $this->readyErrorHandler();
            $this->readyUrlRoute(REQUEST_URI);
        }

        private function readyErrorHandler() {
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
        }

        private function urlRoute($application_route,$matche) {
            if (count($application_route) != 2) {
                throw new WF_Exception(vsprintf('error in list [%s], max of two indices. Ex: ["Application/Controller/method","(GET|POST|PUT|DELETE)"]',[print_r($application_route,true)]));
            }

            $request_method = $application_route[1];

            if (empty($request_method)) {
                throw new WF_Exception(vsprintf('error in url "%s", index two is empty. Ex: "(GET|POST|PUT|DELETE)"',[$application_route[0],]));
            }

            $application_route = $application_route[0];
            $application_route_list = explode('/',$application_route);

            if (count($application_route_list) < 3) {
                throw new WF_Exception(vsprintf('error in application route "%s". Ex: "Application/Controller/method"',[$application_route,]));
            }

            if (empty($application_route_list[0])) {
                throw new WF_Exception(vsprintf('application indefined in route "%s". Ex: "Application/Controller/method"',[$application_route,]));
            }

            if (empty($application_route_list[1])) {
                throw new WF_Exception(vsprintf('application controller indefined in route "%s". Ex: "Application/Controller/method"',[$application_route,]));
            }

            if (empty($application_route_list[2])) {
                throw new WF_Exception(vsprintf('controller method indefined in route "%s". Ex: "Application/Controller/method"',[$application_route,]));
            }

            $application = $application_route_list[0];
            $controller = $application_route_list[1];
            $controller_action = $application_route_list[2];

            $application = vsprintf('Application\\%s\\Controller\\%s',[$application,$controller]);
            $application_file = vsprintf('%s/%s.php',[ROOT_PATH,str_replace('\\','/',$application)]);

            $new_application = new $application($request_method);

            if (empty(method_exists($new_application,$controller_action))) {
                throw new WF_Exception(vsprintf('method "%s" not found in class "%s"',[$controller_action,$application]));
            }

            if (!empty($matche)) {
                array_shift($matche);
            }

            return $new_application->$controller_action(...$matche);
        }

        private function readyUrlRoute($request_uri) {
            $request_uri = str_replace(URL_PREFIX,'',$request_uri);

            $request_uri_strstr = strstr($request_uri,'?',true);

            if (!empty($request_uri_strstr)) {
                $request_uri = $request_uri_strstr;
            }

            $json_config_load = Util::load('Config');

            if (!array_key_exists('app',$json_config_load)) {
                throw new WF_Exception(vsprintf('file app.json not found in directory "%s/Config"',[ROOT_PATH,]));
            }

            $url = [];

            foreach ($json_config_load['app'] as $app) {
                $app_url_class = vsprintf('\Application\%s\Url',[$app]);

                if (!class_exists($app_url_class,true)) {
                    throw new WF_Exception(vsprintf('class "%s" not found',[$app_url_class,]));
                }

                $url += $app_url_class::url();
            }

            foreach ($url as $url_er => $application_route) {
                if (preg_match($url_er,$request_uri,$matche)) {
                    return $this->urlRoute($application_route,$matche);
                }
            }

            throw new WF_Exception(vsprintf('request "%s" not found in Url.php',[$request_uri,]));
        }
    }
}
