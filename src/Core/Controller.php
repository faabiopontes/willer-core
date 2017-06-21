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
     * Class Controller
     * @var object $request
     */
    abstract class Controller {
        private $request;
        /**
         * Controller constructor.
         * @param object $request Request
         */
        public function __construct(Request $request) {
            $this->setRequest($request);
            $this->requestMethodAccess();
        }
        /**
         * @return object
         */
        protected function getRequest(): Request {
            return $this->request;
        }
        /**
         * @param object $request
         * @return self
         */
        protected function setRequest(Request $request): self {
            $this->request = $request;

            return $this;
        }
        /**
         * @return self
         * @throws \Error
         */
        private function requestMethodAccess(): self {
            $request = $this->getRequest();
            $request_method = $request->getRequestMethod();
            $request_server = $request->getHttpServer();

            $util = new Util;

            if (is_array($request_method)) {
                if (!in_array($request_server['REQUEST_METHOD'],$request_method)) {
                    throw new \Error(vsprintf('request method "%s" is different "%s"',[$request_server['REQUEST_METHOD'],print_r($request_method,true)]));
                }

            } else {
                if ($request_server['REQUEST_METHOD'] != $request_method) {
                    throw new \Error(vsprintf('request method "%s" is different "%s"',[$request_server['REQUEST_METHOD'],$request_method]));
                }
            }

            return $this;
        }
    }
}
