<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\Request
 * @uses Core\WUtil
 * @uses Core\Exception\WException
 */
namespace Core {
    use Core\{Request,WUtil};
    use Core\Exception\WException;
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
        public function __construct(Request $request): void {
            $this->setRequest($request);
            $this->requestMethodAccess();
        }
        /**
         * @return object
         */
        protected function getRequest(): object {
            return $this->request;
        }
        /**
         * @param object $request
         * @return self
         */
        protected function setRequest(object $request): self {
            $this->request = $request;

            return $this;
        }
        /**
         * @return self
         * @throws WException
         */
        private function requestMethodAccess(): self {
            $request = $this->getRequest();
            $request_method = $request->getRequestMethod();
            $request_server = $request->getHttpServer();

            $wutil = new WUtil;

            if (empty($wutil->contains($request_server,'REQUEST_METHOD')->getString())) {
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

            return $this;
        }
    }
}
