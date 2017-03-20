<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 */
namespace Core {
    /**
     * Class WUtil
     * @var $return
     */
    class WUtil {
        private $return;
        /**
         * @param iterable $input
         * @param string $key
         * @return self
         */
        public function contains(iterable $input,string $key): self {
            if (is_array($input)) {
                $this->return = array_key_exists($key,$input) ? $input[$key] : null;

            } else if (is_object()) {
                $this->return = property_exists($key,$input) ? $input->$key : null;
            }

            return $this;
        }
        /**
         * @param string $string_default null
         * @return string|null
         */
        public function getString(?string $string_default = null): ?string {
            if (is_null($this->return)) {
                return $string_default;
            }

            return $this->return;
        }
        /**
         * @param string $string_default null
         * @return string|null
         */
        public function getStringForce(?string $string_default = null): ?string {
            if (is_null($this->return)) {
                return $string_default;
            }

            return strval($this->return);
        }
        /**
         * @param int $integer_default null
         * @return int|null
         */
        public function getInteger(?int $integer_default = null): ?int {
            if (is_null($this->return)) {
                return $integer_default;
            }

            return $this->return;
        }
        /**
         * @param int $integer_default null
         * @return int|null
         */
        public function getIntegerForce(?int $integer_default = null): ?int {
            if (is_null($this->return)) {
                return $integer_default;
            }

            if (!filter_var($this->return,FILTER_VALIDATE_INT)) {
                throw new \Error(vsprintf('Value "%s" not int',[$this->return,]));
            }

            return intval($this->return);
        }
        /**
         * @param bool $boolean_default null
         * @return bool|null
         */
        public function getBoolean(?bool $boolean_default = null): ?bool {
            if (is_null($this->return)) {
                return $boolean_default;
            }

            return $this->return;
        }
        /**
         * @param bool $boolean_default null
         * @return bool|null
         */
        public function getBooleanForce(?bool $boolean_default = null): ?bool {
            if (is_null($this->return)) {
                return $boolean_default;
            }

            return boolval($this->return);
        }
        /**
         * @param float $float_default null
         * @return float|null
         */
        public function getFloat(?float $float_default = null): ?float {
            if (is_null($this->return)) {
                return $float_default;
            }

            return $this->return;
        }
        /**
         * @param float $float_default null
         * @return float|null
         */
        public function getFloatForce(?float $float_default = null): ?float {
            if (is_null($this->return)) {
                return $float_default;
            }

            return floatval($this->return);
        }
        /**
         * @param array $array_default null
         * @return array|null
         */
        public function getArray(?array $array_default = null): ?array {
            if (is_null($this->return)) {
                return $array_default;
            }

            return $this->return;
        }
        /**
         * Load vars from .json extension files, and return associative array.
         * @param string $application_path null
         * @param array $exclude_list []
         * @return array
         */
        public static function load(?string $application_path,array $exclude_list = []): ?array {
            $exclude_list = array_merge($exclude_list,['..','.']);

            $scandir_root = array_diff(scandir(ROOT_PATH),$exclude_list);

            $scandir_application = null;

            if (!empty($application_path)) {
                $scandir_application = array_diff(scandir(vsprintf('%s/%s',[ROOT_PATH,$application_path])),$exclude_list);
            }

            $load_var = [];

            foreach ($scandir_root as $file) {
                $spl_file_info = new \SplFileInfo($file);

                if ($spl_file_info->getExtension() == 'json') {
                    $key = $spl_file_info->getBasename('.json');

                    try {
                        $load_var[$key] = json_decode(file_get_contents(vsprintf('%s/%s',[ROOT_PATH,$file])),true);

                    } catch (\Error $error) {
                        throw $error;
                    }
                }
            }

            if (!empty($scandir_application)) {
                foreach ($scandir_application as $file) {
                    $spl_file_info = new \SplFileInfo($file);

                    if ($spl_file_info->getExtension() == 'json') {
                        $key = $spl_file_info->getBasename('.json');

                        try {
                            $load_var[$key] = json_decode(file_get_contents(vsprintf('%s/%s/%s',[ROOT_PATH,$application_path,$file])),true);

                        } catch (\Error $error) {
                            throw $error;
                        }
                    }
                }
            }

            return $load_var;
        }
    }
}
