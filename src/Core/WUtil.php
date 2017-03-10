<?php
/**
 * @author William Borba
 * @package Core
 * @uses \SplFileInfo
 */
namespace Core {
    use \SplFileInfo as SplFileInfo;
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
                $this->return = isset($input[$key]) ? !empty($input[$key]) || $input[$key] === '0' ? $input[$key] : null : null;

            } else if (is_object()) {
                $this->return = isset($input->$key) ? !empty($input->$key) || $input->$key === '0' ? $input->$key : null : null;
            }

            return $this;
        }
        /**
         * @param string $string_default null
         * @return string|null
         */
        public function getString(?string $string_default = null): ?string {
            if (empty($this->return)) {
                return $string_default;
            }

            return $this->return;
        }
        /**
         * @param object $object_default null
         * @return object|null
         */
        public function getObject(?object $object_default = null): ?object {
            if (empty($this->return)) {
                return $object_default;
            }

            return $this->return;
        }
        /**
         * @param array $array_default null
         * @return array|null
         */
        public function getArray(?array $array_default = null): ?array {
            if (empty($this->return)) {
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
                $spl_file_info = new SplFileInfo($file);

                if ($spl_file_info->getExtension() == 'json') {
                    $key = $spl_file_info->getBasename('.json');

                    $load_var[$key] = json_decode(file_get_contents(vsprintf('%s/%s',[ROOT_PATH,$file])),true);
                }
            }

            if (!empty($scandir_application)) {
                foreach ($scandir_application as $file) {
                    $spl_file_info = new SplFileInfo($file);

                    if ($spl_file_info->getExtension() == 'json') {
                        $key = $spl_file_info->getBasename('.json');

                        $load_var[$key] = json_decode(file_get_contents(vsprintf('%s/%s/%s',[ROOT_PATH,$application_path,$file])),true);
                    }
                }
            }

            return $load_var;
        }
    }
}
