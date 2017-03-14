<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 * @uses Core\DAO\DataManipulationLanguage
 */
namespace Core {
    use Core\DAO\DataManipulationLanguage;
    /**
     * Class Model
     * @constant RULE ['null','length','table','label','option','multiple','hidden','filter','reference','password','disabled']
     */
    abstract class Model extends DataManipulationLanguage {
        private const RULE = ['null','length','table','label','option','multiple','hidden','filter','reference','password','disabled'];
        /**
         * Model constructor.
         * @param object $transaction \Core\DAO\Transaction|null
         */
        public function __construct(?\Core\DAO\Transaction $transaction = null) {
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
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         */
        protected static function primaryKey(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (is_null($value)) {
                throw new \Error(vsprintf('"%s primaryKey" field can not be null',[$column,]));
            }

            if (!is_int($value)) {
                throw new \Error(vsprintf('"%s primaryKey" field must be integer',[$column,]));
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function foreignKey(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            if (empty($rule)) {
                throw new \Error(vsprintf('"%s foreignKey" field require one object',[$column,]));
            }

            if (!array_key_exists('table',$rule)) {
                throw new \Error(vsprintf('"%s foreignKey" field require one object',[$column,]));
            }

            $value = $callback();

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s foreignKey" field value can not be null',[$column,]));
                }

            } else {
                if (!is_object($value)) {
                    throw new \Error(vsprintf('"%s foreignKey" field value must be an object',[$column,]));
                }

                if (!is_object($rule['table'])) {
                    throw new \Error(vsprintf('"%s foreignKey" field reference must be an object',[$column,]));
                }

                if (!$value instanceof $rule['table']) {
                    throw new \Error(vsprintf('"%s foreignKey" field value must be an instance of the reference object',[$column,]));
                }
            }

            $primary_key = $value->getPrimaryKey();

            if (empty($primary_key)) {
                throw new \Error(vsprintf('"%s foreignKey" field error, dont find primaryKey field',[$column,]));
            }

            $object->value = $value->$primary_key;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function char(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s char" field value can not be null',[$column,]));
                }

            } else {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new \Error(vsprintf('rule key length must be an numeric, to field "%s char"',[$column,]));
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new \Error(vsprintf('"%s char" field length is greater than "%s"',[$rule_length,$column]));
                    }
                }
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return object
         * @throws \Error
         */
        protected static function text(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s text" field value can not be null',[$column,]));
                }

            } else {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new \Error(vsprintf('rule key length must be an numeric, to field "%s text"',[$column,]));
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new \Error(vsprintf('"%s text" field length is greater than "%s"',[$column,$rule_length,]));
                    }
                }
            }

            $object->value = $value;
            
            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function integer(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            $rule_length = null;

            if (array_key_exists('length',$rule)) {
                $rule_length = $rule['length'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s integer" field value can not be null',[$column,]));
                }

            } else {
                if (!empty($rule_length)) {
                    if (!is_numeric($rule_length)) {
                        throw new \Error(vsprintf('rule key length must be an numeric, to field "%s integer"',[$column,]));
                    }

                    if (strlen($value) > intval($rule_length)) {
                        throw new \Error(vsprintf('"%s integer" field length is greater than "%s"',[$column,$rule_length,]));
                    }
                }
            }

            $object->value = $value;
            
            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function boolean(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s boolean" field value can not be null',[$column,]));
                }
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function datetime(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s datetime" field value can not be null',[$column,]));
                }

            } else {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$/'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                    throw new \Error(vsprintf('"%s datetime" field value "%s" incorrect',[$column,$value,]));
                }
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function date(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s datetime" field value can not be null',[$column,]));
                }

            } else {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})$/'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                    throw new \Error(vsprintf('"%s date" field value "%s" incorrect',[$column,$value,]));
                }
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function time(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s time" field value can not be null',[$column,]));
                }

            } else {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'regexp' => '/^([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$/'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                    throw new WException(vsprintf('"%s time" field value "%s" incorrect',[$column,$value,]));
                }
            }

            $object->value = $value;

            return $object;
        }
        /**
         * @param array $rule []
         * @param callable $callback null
         * @param string $column
         * @param bool $flag null
         * @return \stdClass
         * @throws \Error
         */
        protected static function float(array $rule = [],?callable $callback = null,string $column,?bool $flag = false): \stdClass {
            $object = new \stdClass;

            if (empty($flag)) {
                $object->method = __function__;
                $object->rule = $rule;

                return $object;
            }

            $value = $callback();

            if (empty($rule)) {
                $object->value = $value;

                return $object;
            }

            $rule_null = null;

            if (array_key_exists('null',$rule)) {
                $rule_null = $rule['null'];
            }

            if (is_null($value)) {
                if (empty($rule_null)) {
                    throw new \Error(vsprintf('"%s float" field value can not be null',[$column,]));
                }

            } else {
                $filter_var_option = [
                    'options' => [
                        'default' => false,
                        'decimal' => '.'],
                    'flags' => []];

                if (filter_var($value,FILTER_VALIDATE_FLOAT,$filter_var_option) === false) {
                    throw new \Error(vsprintf('"%s float" field value "%s" incorrect',[$column,$value,]));
                }
            }

            $object->value = $value;

            return $object;
        }
    }
}
