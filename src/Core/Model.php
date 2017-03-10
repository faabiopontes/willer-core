<?php
/**
 * @author William Borba
 * @package Core
 * @uses Core\DAO\DataManipulationLanguage
 * @uses Core\Exception\WException
 */
namespace Core {
    use Core\DAO\DataManipulationLanguage;
    use Core\Exception\WException;
    /**
     * Class Model
     * @constant RULE ['null','length','table','label','option','multiple','hidden','filter','reference','password','disabled']
     */
    abstract class Model extends DataManipulationLanguage {
        private const RULE = ['null','length','table','label','option','multiple','hidden','filter','reference','password','disabled'];
        /**
         * Model constructor.
         * @param Transaction $transaction
         */
        public function __construct(?Transaction $transaction) {
            $this->definePrimaryKey();

            if (!empty($transaction)) {
                parent::__construct($transaction);
            }
        }
        /**
         * @return array
         */
        public function __debugInfo(): array {
            return $this->column();
        }
        /**
         * @return string
         */
        protected function className(): string {
            return get_class($this);
        }
        /**
         * @return array
         */
        protected function column(): array {
            return get_object_vars($this);
        }
        /**
         * @param array $rule []
         * @param integer $value null
         * @param bool $flag false
         * @return integer
         */
        protected static function primaryKey(array $rule = [],?integer $value,boolean $flag = false): integer {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (empty($value)) {
                throw new WException('"primaryKey" field can not be null');
            }

            return $value;
        }
        /**
         * @param array $rule
         * @param object $value null
         * @param bool $flag false
         * @return integer|null
         * @throws WException
         */
        protected static function foreignKey(array $rule = [],?object $value,boolean $flag = false) ?integer {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (empty($rule)) {
                throw new WException('"foreignKey" field require one object');
            }

            if (!array_key_exists('table',$rule)) {
                throw new WException('"foreignKey" field require one object');
            }

            if (empty($value)) {
                if (!array_key_exists('null',$rule) || empty($rule['null'])) {
                    throw new WException('"foreignKey" field value can not be null');
                }

            } else {
                if (!is_object($rule['table'])) {
                    throw new WException('"foreignKey" field value must be an object');
                }

                if (!$value instanceof $rule['table']) {
                    throw new WException('"foreignKey" field value must be an instance of the reference object');
                }
            }

            $primary_key = $value->getPrimaryKey();

            if (empty($primary_key)) {
                throw new WException('"foreignKey" field error, dont find primaryKey field');
            }

            return $value->$primary_key;
        }
        /**
         * @param array $rule []
         * @param string $value null
         * @param bool $flag false
         * @return string|null
         * @throws WException
         */
        protected static function char(array $rule = [],?string $value,?boolean $flag = false) ?string {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"char" field value can not be null');
                }
            }

            if (!is_null($value)) {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new WException('rule key length must be an numeric, to field "char"');
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new WException(vsprintf('"char" field length is greater than "%s"',[$rule_length,]));
                    }
                }
            }

            return $value;
        }
        /**
         * @param array $rule []
         * @param string $value null
         * @param bool $flag false
         * @return string|null
         * @throws WException
         */
        protected static function text(array $rule = [],?string $value,boolean $flag = false): ?string {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"text" field value can not be null');
                }
            }

            if (!is_null($value)) {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new WException('rule key length must be an numeric, to field "text"');
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new WException(vsprintf('"text" field length is greater than "%s"',[$rule_length,]));
                    }
                }
            }
            
            return $value;
        }
        /**
         * @param array $rule []
         * @param integer $value null
         * @param bool $flag false
         * @return integer|null
         * @throws WException
         */
        protected static function integer(array $rule = [],?integer $value,boolean $flag = false): ?integer {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"integer" field value can not be null');
                }
            }

            if (!is_null($value)) {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new WException('rule key length must be an numeric, to field "integer"');
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new WException(vsprintf('"integer" field length is greater than "%s"',[$rule_length,]));
                    }
                }
            }
            
            return $value;
        }
        /**
         * @param array $rule []
         * @param boolean $value null
         * @param bool $flag false
         * @return boolean|object
         * @throws WException
         */
        protected static function boolean(array $rule = [],?boolean $value,boolean $flag = false): ?boolean {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"boolean" field value can not be null');
                }
            }
            
            return $value;
        }
        /**
         * @param array $rule []
         * @param string $value null
         * @param bool $flag false
         * @return string|null
         * @throws WException
         */
        protected static function datetime(array $rule = [],?string $value,boolean $flag = false): ?string {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"datetime" field value can not be null');
                }
            }

            if (!is_null($value)) {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$/'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                    throw new WException(vsprintf('"datetime" field value "%s" incorrect',[$value,]));
                }
            }

            return $value;
        }
        /**
         * @param array $rule []
         * @param string $value null
         * @param bool $flag false
         * @return string|null
         * @throws WException
         */
        protected static function date(array $rule = [],?string $value,boolean $flag = false): ?string {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"datetime" field value can not be null');
                }
            }

            if (!is_null($value)) {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})$/'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                    throw new WException(vsprintf('date field value "%s" incorrect',[$value,]));
                }
            }

            return $value;
        }
        /**
         * @param array $rule []
         * @param string $value null
         * @param bool $flag false
         * @return false|object
         * @throws WException
         */
        protected static function time(array $rule = [],?string $value,boolean $flag = false): string {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"time" field value can not be null');
                }
            }

            if (!is_null($value)) {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'decimal' => '.'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_FLOAT,$filter_var_option) === false) {
                    throw new WException(vsprintf('float field value "%s" incorrect',[$value,]));
                }
            }

            return $value;
        }
        /**
         * @param array $rule []
         * @param float $value null
         * @param bool $flag false
         * @return float|object
         * @throws WException
         */
        protected static function float(array $rule = [],?float $value,boolean $flag = false): ?float {
            if (empty($flag)) {
                $object = new stdClass;

                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (!empty($rule)) {
                return $value;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (empty($rule_null)) {
                if (is_null($value)) {
                    throw new WException('"float" field value can not be null');
                }
            }

            if (!is_null($value)) {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'decimal' => '.'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_FLOAT,$filter_var_option) === false) {
                    throw new WException(vsprintf('float field value "%s" incorrect',[$value,]));
                }
            }

            return $value;
        }
    }
}
