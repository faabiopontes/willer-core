<?php
/**
 * @author William Borba
 * @package Core/Exception
 * @uses \Exception
 */
namespace Core\Exception {
	use \Exception as Exception;
	/**
	 * Class WException
	 * @package Core\Exception
	 * @extends \Exception
	 */
	class WException extends Exception {
		/**
		 * WException constructor.
		 * @param null $name
		 * @param null $code
		 * @param null $previous
         */
		public function __construct($name = null, $code = null, $previous = null) {
            parent::__construct($name,$code,$previous);
        }
	}
}
