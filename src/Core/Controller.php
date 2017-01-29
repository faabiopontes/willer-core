<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\Exception\WException
 * @uses Core\Request
 */
namespace Core {
    use Core\Exception\WException;
    use Core\Request;
    /**
     * Class Controller
     * @package Core
     * @class abstract
     * @property object $request
     */
    abstract class Controller {
        private $request;
        /**
         * Controller constructor.
         * @param null $request_method
         */
        public function __construct(Request $request) {
            $this->setRequest($request);
            $this->requestMethodAccess();
        }
        /**
         * @return mixed
         */
        protected function getRequest() {
            return $this->request;
        }
        /**
         * @param $request
         * @return $this
         */
        protected function setRequest($request) {
            $this->request = $request;

            return $this;
        }
        /**
         * @param null $request_method
         * @throws WException
         */
        private function requestMethodAccess() {
            $request = $this->getRequest();
            $request_method = $request->getRequestMethod();
            $request_server = $request->getHttpServer();

            if (empty(Util::get($request_server,'REQUEST_METHOD',null))) {
                throw new WException('php $_SERVER["REQUEST_METHOD"] is empty');
            }

            if (!empty($request_method)) {
                if (is_array($request_method)) {
                    if (!in_array($request_server['REQUEST_METHOD'],$request_method)) {
                        throw new WException(vsprintf('request method "%s" is different "%s"',[$request_server['REQUEST_METHOD'],print_r($request_method,true)]));
                    }

                } else {
                    if ($request_server['REQUEST_METHOD'] != $request_method) {
                        throw new WException(vsprintf('request method "%s" is different "%s"',[$request_server['REQUEST_METHOD'],$request_method]));
                    }
                }
            }
        }
    }
}
