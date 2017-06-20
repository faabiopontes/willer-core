<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 */
namespace Core {
    use Core\{Message,Stream};
    use Psr\Http\Message\ResponseInterface;
    /**
     * Class Response
     * @see Message
     * @see ResponseInterface
     * @constant MESSAGE_CODE_VALID [100,101,102,200,201,202,203,204,205,206,207,208,226,300,301,302,303,304,305,306,307,308,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,421,422,423,424,426,428,429,431,444,451,499,500,501,502,503,504,505,506,507,508,510,511,599]
     * @var int $code
     * @var string $reason_phrase
     */
    class Response extends Message implements ResponseInterface {
        protected const MESSAGE_CODE_VALID = [
            //Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            //Successful 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            //Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            //Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            444 => 'Connection Closed Without Response',
            451 => 'Unavailable For Legal Reasons',
            499 => 'Client Closed Request',
            //Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            599 => 'Network Connect Timeout Error',
        ];

        private $code;
        private $reason_phrase;
        /**
         * Response constructor
         * @param int $status 200
         * @param StreamInterface|null $stream null
         */
        public function __construct(int $code = 200,?StreamInterface $stream = null) {
            if (empty($stream)) {
                $stream = new Stream(fopen('php://tmp','r+'));

            }

            $this->setCode($code);
            $this->setStream($stream);
        }
        /**
         * @return int
         */
        public function getCode(): int {
            return $this->code;
        }
        /**
         * @param int $code
         * @return self
         * @throws \Error
         */
        public function setCode(int $code): self {
            if (!array_key_exists($code,self::MESSAGE_CODE_VALID)) {
                throw new \Error(vsprintf('Http code "%s" incorrect',[$code,]));
            }

            $this->code = $code;

            http_response_code($code);

            return $this;
        }
        /**
         * @return int
         */
        public function getStatusCode(): int {
            return $this->getCode();
        }
        /**
         * @return string
         */
        public function getReasonPhrase(): string {
            return $this->reason_phrase;
        }
        /**
         * @param string $reason_phrase
         * @return self
         */
        public function setReasonPhrase(string $reason_phrase): self {
            $this->reason_phrase = $reason_phrase;

            return $this;
        }
        /**
         * @param int $code
         * @param string $reasonPhrase null
         * @return Response
         * @throws \Error
         */
        public function withStatus(int $code,?string $reason_phrase = null): Response {
            if (!array_key_exists($code,self::MESSAGE_CODE_VALID)) {
                throw new \Error(vsprintf('Http code "%s" incorrect',[$code,]));
            }

            $clone = clone $this;
            $clone->setCode($code);
            $clone->setReasonPhrase($reason_phrase);

            return $clone;
        }
        /**
         * @return string
         */
        public function render(): string {
            $stream_content = $this
                ->getStream()
                ->getContents();

            return $stream_content;
        }
        /**
         * @param object $stream
         * @return string
         */
        public function renderToJson(): string {
            $stream_content = $this->render();

            $stream_json_encode = json_encode($stream_content,JSON_UNESCAPED_UNICODE);

            $this->setHeader('Content-Type','application/json');

            return $stream_json_encode;
        }
        /**
         * @param string $url
         * @return void
         */
        public function httpRedirect(string $url): void {
            $this->setHeader('Location',$url);
        }
        /**
         * @return array|null
         */
        public function getFlashMessage(): ?array {
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
