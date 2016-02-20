<?php

namespace Core {
    use \Core\DAO\DataManipulationLanguage;
    use \Core\Exception\WF_Exception;

    abstract class Model extends DataManipulationLanguage {
        public function __construct($transaction = null) {
            $this->definePrimaryKey(null);

            if (!empty($transaction)) {
                parent::__construct($transaction);
            }
        }

        public function __debugInfo() {
            return $this->column();
        }

        protected function className() {
            return get_class($this);
        }

        protected function column() {
            return get_object_vars($this);
        }

        private static function filterRule($rule_list,$value,$function_name,$function_filter) {
            if (empty($rule_list)) {
                if ($function_name != 'boolean' && $function_name != 'integer' && empty($value)) {
                    throw new WF_Exception(vsprintf('"%s" field value can not be null',[$function_name,]));
                }

            } else {
                $rule_null = null;
                $rule_length = null;
                $rule_table = null;

                foreach ($rule_list as $rule_name => $rule_value) {
                    if (!in_array($rule_name,['null','length','table'])) {
                        throw new WF_Exception(vsprintf('"%s" field rule "%s" incorrect, possible values "null,length and table"',[$function_name,$rule_name]));

                    } else if ($rule_name == 'null') {
                        $rule_null = $rule_value;

                    } else if ($rule_name == 'length') {
                        $rule_length = $rule_value;

                    } else if ($rule_name == 'table') {
                        $rule_table = $rule_value;
                    }
                }

                if (empty($rule_null)) {
                    if ($value !== 0 && empty($value)) {
                        throw new WF_Exception(vsprintf('"%s" field value can not be null',[$function_name,]));
                    }
                }

                switch ($function_name) {
                    case 'foreignKey':
                        if (empty($rule_table)) {
                            throw new WF_Exception('foreign key field require one object');
                        }

                        break;

                    default:
                        if (empty($rule_null) && !empty($value)) {
                            if (is_object($value)) {
                                throw new WF_Exception(vsprintf('"%s" field value can not be object',[$function_name,]));
                            }
                        }
                }

                if (!empty($rule_table)) {
                    if (empty($value)) {
                        if (empty($rule_null)) {
                            throw new WF_Exception('foreign key field value is missing');
                        }

                    } else {
                        if (!is_object($rule_table)) {
                            throw new WF_Exception('foreign key field value is not object');
                        }

                        if (!$value instanceof $rule_table) {
                            throw new WF_Exception('foreign key field value is not object instance of referral');
                        }
                    }

                } else if (!empty($rule_length)) {
                    if (empty($value)) {
                        if (empty($rule_null)) {
                            throw new WF_Exception(vsprintf('"%s" field value can not be null',[$function_name,]));
                        }

                    } else {
                        if (!is_numeric($rule_length)) {
                            throw new WF_Exception(vsprintf('"%s" field length is not numeric',[$function_name,]));
                        }

                        if (strlen($value) > $rule_length) {
                            throw new WF_Exception(vsprintf('"%s" field length is greater than "%s"',[$function_name,$rule_length]));
                        }
                    }
                }
            }

            if (!empty($value)) {
                $value = $function_filter($value);
            }

            return $value;
        }

        protected static function primaryKey($rule = [],$value = null,$flag = false) {
            return (object) [
                'method' => __function__,
                'rule' => $rule];
        }

        protected static function foreignKey($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $get_primary_key = $value->getPrimaryKey();

                    if (empty($get_primary_key)) {
                        throw new WF_Exception('foreignKey field error, primary key is empty');
                    }

                    return $value->$get_primary_key;
                });

                return $filter_rule;
            }
        }

        protected static function char($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'regexp' => '/^[\w\W\d\D]+$/'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('char field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function text($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'regexp' => '/^[\w\W\d\D]+$/'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('text field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function integer($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_INT,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('integer field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function boolean($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false],
                        'flags' => FILTER_NULL_ON_FAILURE];

                    if (filter_var($value,FILTER_VALIDATE_BOOLEAN,$filter_var_option) === null) {
                        throw new WF_Exception(vsprintf('boolean field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function datetime($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})[ ]([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$/'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('datetime field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function date($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'regexp' => '/^([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]))$|^((0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4})$/'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('date field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function time($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'regexp' => '/^([01][0-9]|2[0123]):([012345][0-9]):([012345][0-9])$/'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_REGEXP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('date field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function float($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,
                            'decimal' => '.'],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_FLOAT,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('float field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function email($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_EMAIL,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('email field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function ip($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_IP,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('ip field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }

        protected static function url($rule = [],$value = null,$flag = false) {
            if (empty($flag)) {
                return (object) [
                    'method' => __function__,
                    'rule' => $rule];

            } else {
                $filter_rule = self::filterRule($rule,$value,__function__,function($value) {
                    $filter_var_option = [
                        'options' => [
                            'default' => false,],
                        'flags' => []];

                    if (filter_var($value,FILTER_VALIDATE_URL,$filter_var_option) === false) {
                        throw new WF_Exception(vsprintf('url field value "%s" incorrect',[$value,]));
                    }

                    return $value;
                });

                return $filter_rule;
            }
        }
    }
}
