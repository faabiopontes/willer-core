<?php
/**
 * @author William Borba
 * @package Core
 * @uses \SplFileInfo
 */
namespace Core {
	use \SplFileInfo as SplFileInfo;
	use Core\Exception\WException;
	/**
	 * Trait Util
	 * @package Core
	 */
	trait Util {
		/**
		 * @param mixed $input
		 * @param string $key
		 * @param mixed $default null
		 * @return mixed|null
		 * @throws WException
         */
		public static function get(mixed $input,string $key,mixed $default = null): mixed {
			if (!is_array($input) && !(is_object($input))) {
				return $default;
			}

			if (is_array($input)) {
				return isset($input[$key]) ? !empty($input[$key]) || $input[$key] === '0' ? $input[$key] : $default : $default;

			} else if (is_object($input)) {
				return isset($input->$key) ? !empty($input->$key) || $input->$key === '0' ? $input->$key : $default : $default;
			}

			throw WException('input type is incorrect');
		}
		/**
		 * @param string $application_path null
		 * @param array $exclude_list []
		 * @return array
         */
		public static function load(string $application_path = null,array $exclude_list = []): array {
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
