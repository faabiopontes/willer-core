<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 */
namespace Core {
    // use Psr\Http\Message\MessageInterface;
    // use Psr\Http\Message\StreamInterface;
    /**
     * Abstract Class Message
     * @see MessageInterface
     * @constant PROTOCOL_VERSION_VALID ['1.0','1.1','2.0']
     * @var string $protocol_version '1.1'
     * @var string $stream
     */
    // abstract class Message implements MessageInterface {
    abstract class Message {
        private const PROTOCOL_VERSION_VALID = ['1.0','1.1','2.0'];
        private $protocol_version = '1.1';
        private $stream;
        /**
         * @return string
         */
        public function getProtocolVersion(): string {
            return $this->$protocol_version;
        }
        /**
         * @param string $protocol_version
         * @return self
         * @throws \Error
         */
        public function setProtocolVersion(string $protocol_version): self {
            if (!in_array($protocol_version,self::PROTOCOL_VERSION_VALID)) {
                throw new \Error(vsprintf('Http protocol version "%s" incorrect',[$protocol_version,]));
            }

            $this->protocol_version = $protocol_version;

            return $this;
        }
        /**
         * @param string $protocol_version
         * @return Response
         * @throws \Error
         */
        public function withProtocolVersion(string $protocol_version): Response {
            if (!array_key_exists($protocol_version,self::MESSAGE_CODE_VALID)) {
                throw new \Error(vsprintf('Http protocol version "%s" incorrect',[$protocol_version,]));
            }

            $clone = clone $this;
            $clone->setProtocolVersion($protocol_version);

            return $clone;
        }
        /**
         * @param string $header
         * @return string
         * @throws \Error
         */
        public function getHeader(string $header): string {
            $header = getallheaders();

            if (!array_key_exists($header,$header)) {
                throw new \Error(vsprintf('Header key "%s" dont find in header list',[$header,]));
            }

            return $header[$header];
        }
        /**
         * @param string $header
         * @return string
         */
        public function getHeaderLine(string $header): string {
            return $this->getHeader($header);
        }
        /**
         * @param string $header
         * @return bool
         */
        public function hasHeader(string $header): bool {
            try {
                $this->getHeader($header);

            } catch (\Error $error) {
                return false;
            }

            return true;
        }
        /**
         * @return array
         */
        public function getAllHeader(): array {
            $header = getallheaders();

            return $header;
        }
        /**
         * @return array
         */
        public function getHeaders(): array {
            return $this->getAllHeader();
        }
        /**
         * @param string $header
         * @param string $value
         * @return self
         */
        public function setHeader(string $header,string $value,?bool $replace = null): self {
            $header = vsprintf('%s: %s',[$header,$value]);

            if (is_null($replace)) {
                $replace = false;
            }

            header($header,$replace);

            return $this;
        }
        /**
         * @param string $header
         * @return self
         * @throws \Error
         */
        public function deleteHeader(string $header): self {
            $header_exist = $this->hasHeader($header);

            if (empty($header_exist)) {
                throw new \Error(vsprintf('Header key "%s" not exist',[$header,]));                
            }

            header_remove($header);

            return $this;
        }
        /**
         * @param string $header
         * @param string $value
         * @return Response
         */
        public function withHeader(string $header,string $value): Response {
            $clone = clone $this;
            $clone->setHeader($header,$value);

            return $clone;
        }
        /**
         * @param string $header
         * @param string $value
         * @return Response
         */
        public function withAddedHeader(string $header,string $value): Response {
            $clone = clone $this;
            $clone->setHeader($header,$value,true);

            return $clone;
        }
        /**
         * @param string $header
         * @return Response
         */
        public function withoutHeader(string $header): Response {
            $clone = clone $this;
            $clone->deleteHeader($header);

            return $clone;
        }
        /**
         * return StreamInterface
         * @return Stream
         */
        // public function getStream(): StreamInterface {
        public function getStream(): Stream {
            return $this->stream;
        }
        /**
         * param StreamInterface $stream null
         * @param Stream $stream null
         * @return self
         */
        // public function setStream(?StreamInterface $stream): self {
        public function setStream(?Stream $stream): self {
            $this->stream = $stream;

            return $this;
        }
        /**
         * param StreamInterface $stream
         * @param Stream $stream
         * @return Response
         */
        // public function withStream(StreamInterface $stream): Response {
        public function withStream(Stream $stream): Response {
            $clone = clone $this;
            $clone->setStream($stream);

            return $clone;
        }
    }
}