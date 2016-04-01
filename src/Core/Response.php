<?php
/**
  *
  * @author William Borba
  * @package Core/Response
  * @uses Core\Exception\WException
  * @uses Core\Util
  * 
  */
namespace Core {
    use Core\Exception\WException;
    use Core\Util;

    class Response {
        private $code;
        private $header;

        public function __construct($code = 200) {
            $this->setCode($code);

            http_response_code($code);
        }

        public function setCode($code) {
            $this->code = $code;

            http_response_code($code);

            return $this;
        }

        public function getCode() {
            return $this->code;
        }

        public function setHeader($header) {
            $this->header = $header;

            return $this;
        }

        public function getHeader() {
            return $this->header;
        }
    }
}
