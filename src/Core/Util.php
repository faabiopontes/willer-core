<?php
/**
 * @author William Borba
 * @package Core
 * @uses \SplFileInfo
 */
namespace Core {
    use \SplFileInfo as SplFileInfo;
    /**
     * Trait Util
     * @package Core
     * @var $return
     */
    class WUtil {
        private $return;
        /**
         * @param object $input
         * @param string $key
         * @return string
         */
        public function objectContains(object $input,string $key): void {
            $this->return = isset($input->$key) ? !empty($input->$key) || $input->$key === '0' ? $input->$key : null : null;
        }
        /**
         * @param array $input
         * @param string $key
         * @param string $default null
         * @return string
         */
        public function arrayContains(array $input,string $key): void {
            $this->return = isset($input[$key]) ? !empty($input[$key]) || $input[$key] === '0' ? $input[$key] : null : null;
        }
        /**
         * @return string
         */
        public function getString(?string $string_default = null): ?string {
            if (empty($this->return)) {
                return $string_default;
            }

            return $this->return;
        }
        /**
         * @return object
         */
        public function getObject(?object $object_default = null): ?object {
            if (empty($this->return)) {
                return $object_default;
            }

            return $this->return;
        }
        /**
         * @return array
         */
        public function getArray(?array $array_default = null): ?array {
            if (empty($this->return)) {
                return $array_default;
            }

            return $this->return;
        }
        /**
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
