<?php

namespace Core\Exception {
	use \Exception as Exception;

	class WF_Exception extends Exception {
		public function __construct($name = null,$code = null,$previous = null) {
            parent::__construct($name,$code,$previous);
        }
	}
}