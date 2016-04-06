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
        private $body;
        private $code;
        private $header;

        public function __construct($body = null,$code = 200) {
            $this->setbody($body);
            $this->setCode($code);
        }

        public function setBody($body) {
            $this->body = $body;

            return $this;
        }

        public function getBody() {
            return $this->body;
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

        public function render($body) {
            if (!empty($body)) {
                $this->setBody($body);

            } else {
                $body = $this->getBody();
            }

            print $body;
        }

        public function renderToJson($body) {
            if (!empty($body)) {
                $this->setBody($body);

            } else {
                $body = $this->getBody();
            }

            $body = json_encode($body,JSON_UNESCAPED_UNICODE);

            header('Content-Type: application/json');

            print $body;
        }
    }
}
