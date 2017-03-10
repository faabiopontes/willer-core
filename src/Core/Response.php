<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\Exception\WException
 */
namespace Core {
    use Core\Exception\WException;
    /**
     * Class Response
     * @constant string CODE_STATUS_OK '200'
     * @var string $body
     * @var integer $code
     */
    class Response {
        private const CODE_STATUS_OK = '200'

        private $body;
        private $code;
        /**
         * Response constructor.
         * @param string $body null
         * @param string $code
         * @return void
         */
        public function __construct(?string $body,?string $code): void {
            if (empty($code)) {
                $code = self::CODE_STATUS_OK;
            }

            $this->setBody($body);
            $this->setCode($code);
        }
        /**
         * @return string
         */
        public function getBody(): string {
            return $this->body;
        }
        /**
         * @param string $body null
         * @return self
         */
        public function setBody(?string $body): self {
            $this->body = $body;

            return $this;
        }
        /**
         * @return string
         */
        public function getCode(): string {
            return $this->code;
        }
        /**
         * @param string $code
         * @return self
         */
        public function setCode(string $code): self {
            $this->code = $code;

            http_response_code($code);

            return $this;
        }
        /**
         * @param string $header_key
         * @return string
         * @throws WException
         */
        public function getHeader(string $header_key): string {
            $header = getallheaders();

            if (!array_key_exists($header_key,$header)) {
                throw new WException(vsprintf('Header key "%s" dont find in header list',[$header_key,]));
            }

            return $header[$header_key];
        }
        /**
         * @return array
         */
        public function getAllHeader(): array {
            $header = getallheaders();

            return $header;
        }
        /**
         * @param string $header_key
         * @param string $header_value
         * @return self
         */
        public function setHeader(string $header_key,string $header_value): self {
            $header = vsprintf('%s: %s',[$header_key,$header_value]);

            header($header);

            return $this;
        }
        /**
         * @param string $body
         * @return string
         */
        public function render(string $body): string {
            $this->setBody($body);

            return $body;
        }
        /**
         * @param object $body
         * @return string
         */
        public function renderObjectToJson(object $body): string {
            $body = json_encode($body,JSON_UNESCAPED_UNICODE);

            $this->setHeader('Content-Type','application/json');

            return $body;
        }
        /**
         * @param string $url
         * @return void
         */
        public function httpRedirect(string $url): void {
            $this->setHeader('Location',$url);
        }
        /**
         * @return array
         */
        public function getFlashMessage(): array {
            $flash_message = null;

            if (isset($_SESSION['wf']['flash_message']) && !empty($_SESSION['wf']['flash_message'])) {
                $flash_message = $_SESSION['wf']['flash_message'];

                unset($_SESSION['wf']['flash_message']);
            }

            return $flash_message;
        }
        /**
         * @param string $message
         * @param string $type 'info'
         * @return self
         */
        public function setFlashMessage($message,$type = 'info'): self {
            if (!isset($_SESSION['wf']['flash_message'])) {
                $_SESSION['wf']['flash_message'] = [
                    [
                    'message' => $message,
                    'type' => $type]];

            } else {
                $_SESSION['wf']['flash_message'][] = [
                    'message' => $message,
                    'type' => $type];
            }

            return $this;
        }
    }
}
