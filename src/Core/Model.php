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
     */
    abstract class Model extends DataManipulationLanguage {
        /**
         * Model constructor.
         * @param Transaction $transaction
         */
        public function __construct(?Transaction $transaction): void {
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
         * @param $rule_list
         * @param $value
         * @param $function_name
         * @param $function_filter
         * @return mixed
         * @throws WException
         */
        private static function filterRule($rule_list,$value,$function_name,$function_filter) {
            if (!empty($rule_list)) {
                $rule_null = null;
                $rule_length = null;
                $rule_table = null;

                foreach ($rule_list as $rule_name => $rule_value) {
                    if (!in_array($rule_name,['null','length','table','label','option','multiple','hidden','filter','reference','password','disabled'])) {
                        throw new WException(vsprintf('"%s" field rule "%s" incorrect"',[$function_name,$rule_name]));

                    } else if ($rule_name == 'null') {
                        $rule_null = $rule_value;

                    } else if ($rule_name == 'length') {
                        $rule_length = $rule_value;

                    } else if ($rule_name == 'table') {
                        $rule_table = $rule_value;
                    }
                }

                if (empty($rule_null)) {
                    if ($value !== '0' && empty($value)) {
                        throw new WException(vsprintf('"%s" field value can not be null',[$function_name,]));
                    }
                }

                switch ($function_name) {
                    case 'foreignKey':
                        if (empty($rule_table)) {
                            throw new WException('foreign key field require one object');
                        }

                        if (empty($value)) {
                            if (empty($rule_null)) {
                                throw new WException('foreign key field value is missing');
                            }

                        } else {
                            if (!is_object($rule_table)) {
                                throw new WException('foreign key field value is not object');
                            }

                            if (!$value instanceof $rule_table) {
                                throw new WException('foreign key field value is not object instance of referral');
                            }
                        }

                        break;

                    default:
                        if (empty($rule_null) && !empty($value)) {
                            if (is_object($value)) {
                                throw new WException(vsprintf('"%s" field value can not be object',[$function_name,]));
                            }
                        }
                }

                if (!empty($rule_length)) {
                    if (empty($value)) {
                        if (empty($rule_null)) {
                            throw new WException(vsprintf('"%s" field value can not be null',[$function_name,]));
                        }

                    } else {
                        if (!is_numeric($rule_length)) {
                            throw new WException(vsprintf('"%s" field length is not numeric',[$function_name,]));
                        }

                        if (strlen($value) > $rule_length) {
                            throw new WException(vsprintf('"%s" field length is greater than "%s"',[$function_name,$rule_length]));
                        }
                    }
                }
            }

            if (!empty($value)) {
                $value = $function_filter($value);
            }

            return $value;
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
