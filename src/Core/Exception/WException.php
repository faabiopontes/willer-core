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
	 */
	class WException extends Exception {
		/**
		 * WException constructor.
		 * @param null $name
		 * @param null $code
		 * @param null $previous
         */
		public function __construct(?string $name = null,?string $code = null,?string $previous = null) {
            parent::__construct($name,$code,$previous);
        }
	}
}
