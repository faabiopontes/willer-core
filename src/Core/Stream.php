<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 */
namespace Core {
    // use Psr\Http\Message\StreamInterface;
    /**
     * Abstract Class Stream
     * see StreamInterface
     * @constant MODE ['read' => ['r', 'r+', 'w+', 'a+', 'x+', 'c+'],'write' => ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']]
     * @var resource $stream
     */
    // class Stream implements StreamInterface {
    class Stream {
        private const MODE = [
            'read' => ['r','r+','w+','a+','x+','c+','w+b'],
            'write' => ['r+','w','w+','a','a+','x','x+','c','c+','w+b'],
        ];

        private $stream;
        /**
         * Response constructor
         * @param resource $stream
         * @throws \Error
         */
        public function __construct($stream) {
            if (!is_resource($stream)) {
                throw new \Error('Stream is not resource');
            }

            $this->setStream($stream);
        }
        /**
         * @return string
         * @throws \Error
         */
        public function __toString(): string {
            try {
                $stream_content = $this->getContents();

            } catch (\Error $error) {
                throw new \Error('Stream ToString error');
            }

            return $stream_content;
        }
        /**
         * @return resource
         */
        public function getStream() {
            return $this->stream;
        }
        /**
         * @param resource $stream null
         * @return self
         */
        public function setStream($stream = null): self {
            if (!is_resource($stream)) {
                throw new \Error('Stream is not resource');
            }

            $this->stream = $stream;

            return $this;
        }
        /**
         * @return self
         * @throws \Error
         */
        public function close(): self {
            $stream = $this->getStream();

            try {
                fclose($stream);

            } catch(\Error $error) {
                throw $error;
            }

            return $this;
        }
        /**
         * @return resource
         */
        public function detach() {
            $stream = $this->getStream();

            $this->setStream(null);

            return $stream;
        }
        /**
         * @return int|null
         */
        public function getSize(): ?int {
            $stream = $this->getStream();

            try {
                $stream_stats = fstat($stream);

            } catch(\Error $error) {
                throw $error;
            }

            if (isset($stream_stats['size'])) {
                return intval($stream_stats['size']);
            }

            return null;
        }
        /**
         * @return int|null
         * @throws \Error
         */
        public function tell(): int {
            $stream = $this->getStream();

            try {
                $stream_tell = ftell($stream);

            } catch(\Error $error) {
                throw $error;
            }

            if (empty($stream_tell)) {
                throw new \Error('Stream position error');
            }

            return intval($stream_tell);
        }
        /**
         * @return bool
         * @throws \Error
         */
        public function eof(): bool {
            $stream = $this->getStream();

            try {
                $stream_eof = feof($stream);

            } catch(\Error $error) {
                throw $error;
            }

            return boolval($stream_eof);
        }
        /**
         * @return bool
         */
        public function isSeekable(): bool {
            $stream = $this->getStream();

            $meta_data = $this->getMetadata();

            if (array_key_exists('seekable',$meta_data)) {
                return boolval($meta_data['seekable']);
            }

            return false;
        }
        /**
         * @param int $offset
         * @param global $whence SEEK_SET
         * @return bool
         * @throws \Error
         */
        public function seek(int $offset,$whence = SEEK_SET): bool {
            $stream = $this->getStream();

            try {
                $stream_seek = fseek($stream,$offset,$whence);

            } catch(\Error $error) {
                throw $error;
            }

            if (intval($stream_seek) === -1) {
                throw new \Error('Stream seek error');
            }

            return true;
        }
        /**
         * @return bool
         * @throws \Error
         */
        public function rewind(): bool {
            $stream = $this->getStream();

            try {
                $stream_seek = rewind($stream);

            } catch(\Error $error) {
                throw $error;
            }

            if (empty(intval($stream_seek))) {
                throw new \Error('Stream rewind error');
            }

            return true;
        }
        /**
         * @return bool
         * @throws \Error
         */
        public function isWritable(): bool {
            $stream = $this->getStream();
            $meta_data = $this->getMetadata();

            if (!array_key_exists('mode',$meta_data)) {
                throw new \Error('Stream metadata "mode" don\'t exist');
            }

            if (!in_array($meta_data['mode'],self::MODE['write'])) {
                return false;
            }

            return true;
        }
        /**


         * @return int
         * @throws \Error
         */
        public function write(string $string): int {
            $stream = $this->getStream();

            if (!$this->isWritable()) {
                throw new \Error('Stream don\'t writable');
            }

            try {
                $stream_write = fwrite($stream,$string);

            } catch(\Error $error) {
                throw $error;
            }

            if ($stream_write === false) {
                throw new \Error('Stream write error');
            }

            // $this->setStream($stream);

            return intval($stream_write);
        }
        /**
         * @return bool
         * @throws \Error
         */
        public function isReadable(): bool {
            $stream = $this->getStream();
            $meta_data = $this->getMetadata();

            if (!array_key_exists('mode',$meta_data)) {
                throw new \Error('Stream metadata "mode" don\'t exist');
            }

            if (!in_array($meta_data['mode'],self::MODE['read'])) {
                return false;
            }

            return true;
        }
        /**
         * @param int $length
         * @return string
         * @throws \Error
         */
        public function read(int $length): string {
            $stream = $this->getStream();

            if (!$this->isReadable()) {
                throw new \Error('Stream don\'t readable');
            }

            try {
                $stream_read = fread($stream,$length);

            } catch(\Error $error) {
                throw $error;
            }

            if ($stream_read === false) {
                throw new \Error('Stream read error');
            }

            return strval($stream_read);
        }
        /**
         * @return string
         * @throws \Error
         */
        public function getContents(): string {
            $stream = $this->getStream();

            if (!$this->isReadable()) {
                throw new \Error('Stream don\'t readable');
            }

            try {
                $stream_content = stream_get_contents($stream);

            } catch(\Error $error) {
                throw $error;
            }

            if ($stream_content === false) {
                throw new \Error('Stream get content error');
            }

            return strval($stream_content);
        }
        /**
         * @param string $name null
         * @return array|null
         */
        public function getMetadata(?string $name = null): ?array {
            $stream = $this->getStream();

            $meta_data = stream_get_meta_data($stream);

            if (is_null($name)) {
                return $meta_data;
            }

            if (array_key_exists($name,$meta_data)) {
                return [$meta_data[$name]];
            }

            return null;
        }
    }
}