<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\Exception\WException
 */
namespace Core {
    use Core\Exception\WException;
    /**
     * Class Controller
     * @package Core
     * @class abstract
     */
    abstract class Controller {
        /**
         * Controller constructor.
         * @param null $request_method
         */
        public function __construct($request_method = null) {
            $this->requestMethodAccess($request_method);
        }
        /**
         * @param null $request_method
         * @throws WException
         */
        private function requestMethodAccess($request_method = null) {
            if (empty(Util::get($_SERVER,'REQUEST_METHOD',null))) {
                throw new WException('php $_SERVER["REQUEST_METHOD"] is empty');
            }

            if (!empty($request_method)) {
                if (is_array($request_method)) {
                    if (!in_array($_SERVER['REQUEST_METHOD'],$request_method)) {
                        throw new WException(vsprintf('request method "%s" is different "%s"',[$_SERVER['REQUEST_METHOD'],print_r($request_method,true)]));
                    }

                } else {
                    if ($_SERVER['REQUEST_METHOD'] != $request_method) {
                        throw new WException(vsprintf('request method "%s" is different "%s"',[$_SERVER['REQUEST_METHOD'],$request_method]));
                    }
                }
            }
        }
    }
}
