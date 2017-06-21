<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 */
namespace Core {
    use Core\Stream;
    // use Psr\Http\Message\UploadedFileInterface;
    /**
     * Abstract Class UploadedFile
     * see UploadedFileInterface
     * @var array $file_meta
     * @var resource $stream
     * @var string $name
     * @var string $tmp_name
     * @var int $error
     * @var int $size
     */
    // class UploadedFile implements UploadedFileInterface {
    class UploadedFile {
        private $file_meta;
        private $stream;
        private $name;
        private $tmp_name;
        private $error;
        private $size;
        private $uploaded;
        /**
         * UploadedFile constructor
         * @param array $file_meta
         * @throws \Error
         */
        public function __construct(array $file_meta) {
            $this->setFileMeta($stream);

            $this->setName($file_meta['name']);
            $this->setType($file_meta['type']);
            $this->setTmpName($file_meta['tmp_name']);
            $this->setError($file_meta['error']);
            $this->setSize($file_meta['size']);
            $this->setUpLoaded(false);

            try {
                $file_open = fopen($file_meta['tmp_name'],'r');

            } catch (\Error $error) {
                throw new \Error('Upload file don\'t open');
            }

            $stream = new Stream($file_open);

            $this->setStream($stream);
        }
        /**
         * @return array
         */
        public function getFileMeta(): array {
            return $this->file_meta;
        }
        /**
         * @param array $file_meta
         * @return self
         */
        public function setFileMeta(array $file_meta): self {
            $this->file_meta = $file_meta;

            return $this;
        }
        /**
         * @return string
         */
        public function getName(): string {
            return $this->name;
        }
        /**
         * @return string
         */
        public function getClientFilename(): string {
            return $this->name;
        }
        /**
         * @param string $name
         * @return self
         */
        public function setName(string $name): self {
            $this->name = $name;

            return $this;
        }
        /**
         * @return string
         */
        public function getType(): string {
            return $this->type;
        }
        /**
         * @return string
         */
        public function getClientMediaType(): string {
            return $this->type;
        }
        /**
         * @param string $type
         * @return self
         */
        public function setType(string $type): self {
            $this->type = $type;

            return $this;
        }
        /**
         * @return string
         */
        public function getTmpName(): string {
            return $this->tmp_name;
        }
        /**
         * @param string $tmp_name
         * @return self
         */
        public function setTmpName(string $tmp_name): self {
            $this->tmp_name = $tmp_name;

            return $this;
        }
        /**
         * @return string
         */
        public function getError(): string {
            return $this->error;
        }
        /**
         * @param string $error
         * @return self
         */
        public function setError(string $error): self {
            $this->error = $error;

            return $this;
        }
        /**
         * @return string
         */
        public function getSize(): string {
            return $this->size;
        }
        /**
         * @param string $size
         * @return self
         */
        public function setSize(string $size): self {
            $this->size = $size;

            return $this;
        }
        /**
         * @return bool
         */
        public function getUpLoaded(): bool {
            return $this->uploaded;
        }
        /**
         * @param bool $uploaded
         * @return self
         */
        public function setUpLoaded(bool $uploaded): self {
            $this->uploaded = $uploaded;

            return $this;
        }
        /**
         * @return StreamInterface
         */
        public function getStream(): StreamInterface {
            return $this->stream;
        }
        /**
         * @param StreamInterface $stream
         * @return self
         */
        public function setStream(StreamInterface $stream): self {
            $this->stream = $stream;

            return $this;
        }
        /**
         * @param string $target_path
         * @return self
         */
        public function moveTo(string $target_path): self {
            $uploaded = $this->getUpLoaded();

            if (empty($uploaded)) {
                throw new \Error('File uploaded');
            }

            $name = $this->getName();
            $tmp_name = $this->getTmpName();

            $target_stream = strpos($target_path,'://');

            if ($target_stream > 0) {
                if (!copy($tmp_name,$target_path)) {
                    throw new \Error(vsprintf('Error moving uploaded file "%s" to "%s"',[$name,$target_path]));
                }

                if (!unlink($tmp_name)) {
                    throw new \Error(vsprintf('Error removing uploaded file "%s"',[$name]));
                }

                return true;
            }

            if is_writable(dirname($target_path)) {
                throw new \Error(vsprintf('Upload target path "%s" is not writable',[$target_path,]));
            }

            if (php_sapi_name() == 'cli') {
                if (!rename($tmp_name,$target_path)) {
                    throw new \Error(sprintf('Error moving uploaded file "%s" to "%s"', $name,$target_path));
                }

                return true;
            }

            if (!is_uploaded_file($tmp_name)) {
                throw new \Error(sprintf('"%s" is not a valid uploaded file',$name));
            }

            if (!move_uploaded_file($name,$target_path)) {
                throw new \Error(sprintf('Error moving uploaded file "%s" to "%s"',$name,$target_path));
            }

            $this->setUpLoaded(true);

            return true;
        }
    }
}