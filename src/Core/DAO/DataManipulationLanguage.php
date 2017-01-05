<?php

/**
 * @author William Borba
 * @package Core/DAO
 * @uses \PDO
 * @uses \Exception
 * @uses \PDOException
 * @uses Core\Exception\WException
 */
namespace Core\DAO {
    use \PDO as PDO;
    use \Exception as Exception;
    use \PDOException as PDOException;
    use Core\Exception\WException;
    /**
     * Class DataManipulationLanguage
     * @package Core\DAO
     * @property mixed $transaction
     * @property string $db_escape
     * @property array $related
     * @property string $limit
     * @property array $limit_value
     * @property array $order_by
     * @property string $primary_key
     * @property integer $last_insert_id
     * @property array $where_unique
     * @property array $where_unique_value
     * @property array $where
     * @property array $where_value
     * @property array $like
     * @property array $like_value
     * @property array $between
     * @property array $between_value
     * @property array $query
     * @property boolean $flag_new_or_update
     */
    abstract class DataManipulationLanguage {
        private $transaction;
        private $db_escape;
        private $related;
        private $limit;
        private $limit_value;
        private $order_by;
        private $primary_key;
        private $last_insert_id;
        private $where_unique;
        private $where_unique_value;
        private $where;
        private $where_value;
        private $between;
        private $between_value;
        private $like;
        private $like_value;
        private $query;
        private $flag_new_or_update;
        /**
         * DataManipulationLanguage constructor.
         * @param null $transaction
         */
        public function __construct($transaction = null) {
            if (empty($transaction)) {
                throw new WException(vsprintf('Transaction object not loaded, in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            $this->setTransaction($transaction);

            $get_database_info = $this->transaction->getDatabaseInfo();
            $db_driver = $get_database_info['driver'];

            if ($db_driver == 'mysql') {
                $this->db_escape = '';

            } else if ($db_driver == 'sqlite') {
                $this->db_escape = '\'';

            } else if ($db_driver == 'pgsql') {
                $this->db_escape = '"';
            }

            $this->query = [];
        }
        /**
         * @return mixed
         */
        private function getClassName() {
            return $this->className();
        }
        /**
         * @return mixed
         */
        public function getTableName() {
            return $this->name();
        }
        /**
         * @return mixed
         */
        public function getTableColumn() {
            return $this->column();
        }
        /**
         * @return mixed
         */
        private function getTableSchema() {
            return $this->schema();
        }
        /**
         * @return mixed
         */
        protected function getTransaction() {
            return $this->transaction;
        }

        /**
         * @param $transaction
         * @return $this
         */
        protected function setTransaction($transaction) {
            $this->transaction = $transaction;

            return $this;
        }
        /**
         * @return string
         */
        private function getLimit() {
            return $this->limit;
        }
        /**
         * @param $limit
         * @return $this
         */
        private function setLimit($limit) {
            $this->limit = $limit;

            return $this;
        }
        /**
         * @return array
         */
        private function getLimitValue() {
            return $this->limit_value;
        }
        /**
         * @param $page
         * @param $limit
         * @return $this
         */
        private function setLimitValue($page, $limit) {
            $this->limit_value = [
                'page' => $page,
                'limit' => $limit,
            ];

            return $this;
        }
        /**
         * @return array
         */
        private function getOrderBy() {
            return $this->order_by;
        }
        /**
         * @param $order_by
         * @return $this
         */
        private function setOrderBy($order_by) {
            $this->order_by = $order_by;

            return $this;
        }
        /**
         * @return string
         */
        protected function getPrimaryKey() {
            return $this->primary_key;
        }
        /**
         * @param $column
         * @return $this
         */
        private function setPrimaryKey($column) {
            $this->primary_key = $column;

            return $this;
        }
        /**
         * @return int
         */
        private function getLastInsertId() {
            return $this->last_insert_id;
        }
        /**
         * @param $id
         * @return $this
         */
        private function setLastInsertId($id) {
            $this->last_insert_id = $id;

            return $this;
        }
        /**
         * @return array
         */
        private function getWhereUnique() {
            return $this->where_unique;
        }
        /**
         * @param $where_unique
         * @return $this
         */
        private function setWhereUnique($where_unique) {
            $this->where_unique = $where_unique;

            return $this;
        }
        /**
         * @return array
         */
        private function getWhereUniqueValue() {
            return $this->where_unique_value;
        }
        /**
         * @param $where_unique_value
         * @return $this
         */
        private function setWhereUniqueValue($where_unique_value) {
            $this->where_unique_value = $where_unique_value;

            return $this;
        }
        /**
         * @return array
         */
        private function getWhere() {
            return $this->where;
        }
        /**
         * @param $where
         * @return $this
         */
        private function setWhere($where) {
            $this->where = $where;

            return $this;
        }
        /**
         * @return array
         */
        private function getWhereValue() {
            return $this->where_value;
        }
        /**
         * @param $where_value
         * @return $this
         */
        private function setWhereValue($where_value) {
            $this->where_value = $where_value;

            return $this;
        }
        /**
         * @return array
         */
        private function getBetween() {
            return $this->between;
        }
        /**
         * @param $between
         * @return $this
         */
        private function setBetween($between) {
            $this->between = $between;

            return $this;
        }
        /**
         * @return array
         */
        private function getBetweenValue() {
            return $this->between_value;
        }
        /**
         * @param $between_value
         * @return $this
         */
        private function setBetweenValue($between_value) {
            $this->between_value = $between_value;

            return $this;
        }
        /**
         * @return array
         */
        private function getLike() {
            return $this->like;
        }
        /**
         * @param $like
         * @return $this
         */
        private function setLike($like) {
            $this->like = $like;

            return $this;
        }
        /**
         * @return array
         */
        private function getLikeValue() {
            return $this->like_value;
        }
        /**
         * @param $like_value
         * @return $this
         */
        private function setLikeValue($like_value) {
            $this->like_value = $like_value;

            return $this;
        }
        /**
         * @return array
         */
        private function getQuery() {
            return $this->query;
        }
        /**
         * @param $sql
         * @param $value
         * @return $this
         */
        private function setQuery($sql, $value) {
            $this->query[] = [
                'sql' => $sql,
                'value' => $value
            ];

            return $this;
        }
        /**
         * @param null $column
         * @throws WException
         */
        protected function definePrimaryKey($column = null) {
            $table_schema = $this->schema();

            foreach ($table_schema as $i => $value) {
                if ($value->method == 'primaryKey') {
                    if (!empty($primarykey_flag)) {
                        throw new WException(vsprintf('"%s" field error, primary key need be unique',[$i,]));
                    }

                    $column = $i;
                }
            }

            $this->setPrimaryKey($column);
        }
        /**
         * @param array $order_by
         * @return $this
         */
        public function orderBy($order_by = []) {
            if (!empty($order_by)) {
                $order_by_list = [];

                foreach ($order_by as $i => $value) {
                    $order_by_list[] = vsprintf('%s %s',[$i,$value]);
                }

                $get_order_by = $this->getOrderBy();

                if (empty($get_order_by)) {
                    $get_order_by = [];
                }

                $this->setOrderBy(array_merge($get_order_by,$order_by_list));
            }

            return $this;
        }
        /**
         * @param int $page
         * @param int $limit
         * @return $this
         */
        public function limit($page = 1, $limit = 1000) {
            $limit_value = null;

            $page = intval($page);
            $limit = intval($limit);

            if ($page <= 1) {
                $page = 1;

                $limit_value = vsprintf('limit %s offset 0',[$limit,]);

            } else {
                $page_ = $page - 1;
                $page_x_limit = $page_ * $limit;
                $limit_value = vsprintf('limit %s offset %s',[$limit,$page_x_limit]);
            }

            $this->setLimitValue($page,$limit);
            $this->setLimit($limit_value);

            return $this;
        }
        /**
         * @param $table_related
         * @param array $query_list
         * @param bool $join
         * @return array
         */
        private function related($table_related, $query_list = [], $join = false, $table_related_name_alias = null) {
            $table_name = $table_related->getTableName();
            $table_schema = $table_related->getTableSchema();

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            if (empty($query_list)) {
                $query_list = [
                    'column' => [],
                    'join' => [],
                ];
            }

            foreach ($table_schema as $i => $table) {
                if ($table->method == 'foreignKey') {
                    $table_foreign_key = $i;
                    $table_related = $table->rule['table'];
                    $table_related_table_name = $table_related->getTableName();
                    $table_related_table_column = $table_related->getTableColumn();
                    $table_related_primary_key = $table_related->getPrimaryKey();

                    if (empty($join)) {
                        $join = 'inner';

                        if (array_key_exists('null',$table->rule)) {
                            if (!empty($table->rule['null'])) {
                                $join = 'left';
                            }
                        }
                    }

                    $table_related_table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_related_table_name,$this->db_escape]);
                    $table_name_alias = vsprintf('%s_%s',[$table_name,$table_related_table_name]);
                    $table_name_alias_with_escape = vsprintf('%s%s_%s%s',[$this->db_escape,$table_name,$table_related_table_name,$this->db_escape]);

                    $column_list = [];

                    foreach ($table_related_table_column as $ii => $column) {
                        $column_list[] = vsprintf('%s.%s %s__%s',[$table_name_alias_with_escape,$ii,$table_name_alias,$ii]);
                    }

                    $query_list['column'][] = $column_list;

                    if (!empty($table_related_name_alias) && $table_related_name_alias != $table_name) {
                        $table_name_with_escape = vsprintf('%s%s_%s%s',[$this->db_escape,$table_related_name_alias,$table_name,$this->db_escape]);
                    }

                    $query_list['join'][] = vsprintf('%s join %s AS %s on %s.%s = %s.%s',[$join,$table_related_table_name_with_escape,$table_name_alias_with_escape,$table_name_alias_with_escape,$table_related_primary_key,$table_name_with_escape,$table_foreign_key]);

                    $table_related_name_alias = $table_name;

                    $query_list = $this->related($table_related,$query_list,$join,$table_related_name_alias);
                }
            }

            return $query_list;
        }
        /**
         * @param array $where
         * @return $this
         * @throws WException
         */
        public function where($where = []) {
            $where_value_list = [];

            if (empty($where)) {
                $where_query = null;

            } else {
                $where_query = [];

                foreach ($where as $key => $value) {
                    $where_value = null;

                    if (empty($value)) {
                        $where_value = vsprintf('%s is null',[$key,]);

                    } else if (!is_array($value) && (is_string($value) || is_numeric($value) || is_bool($value))) {
                        $where_value_list[] = $value;

                        $where_value = vsprintf('%s=?',[$key,]);

                    } else if (is_array($value)) {
                        $where_value_list = array_merge($where_value_list,$value);
                        $value = implode(',',array_map(function ($value) {
                            return '?';
                        },$value));

                        $where_value = vsprintf('%s in(%s)',[$key,$value]);

                    } else {
                        throw new WException(vsprintf('value is incorrect with type "%s", in instance of model "%s"',[gettype($value),$this->name()]));
                    }

                    $where_query[] = $where_value;
                }

                $this->setWhereUnique(null);
                $this->setWhereUniqueValue(null);

                $this->setWhere($where_query);
                $this->setWhereValue($where_value_list);
            }

            return $this;
        }
        /**
         * @param array $like
         * @return $this
         * @throws WException
         */
        public function like($like = []) {
            $like_value_list = [];

            if (empty($like)) {
                $like_query = null;

            } else {
                $like_query = [];

                foreach ($like as $key => $value) {
                    $like_value = null;

                    if (empty($value)) {
                        throw new WException(vsprintf('value for "%s" is null',[$key,]));

                    } else if (is_string($value) || is_numeric($value)) {
                        $like_value_list[] = $value;

                        $like_value = vsprintf('%s like ?',[$key,]);

                    } else {
                        throw new WException(vsprintf('value is incorrect with type "%s", in instance of model "%s"',[gettype($value),$this->name()]));
                    }

                    $like_query[] = $like_value;
                }

                $this->setLike($like_query);
                $this->setLikeValue($like_value_list);
            }

            return $this;
        }
        /**
         * @param array $between
         * @return $this
         * @throws WException
         */
        public function between($between = []) {
            $between_value_list = [];

            if (empty($between)) {
                $between_query = null;

            } else {
                $between_query = [];

                foreach ($between as $key => $value_list) {
                    $between_value = null;

                    if (empty($value_list)) {
                        throw new WException(vsprintf('value for "%s" is null',[$key,]));
                    }

                    if (!is_array($value_list)) {
                        throw new WException(vsprintf('value is incorrect with type "%s", in instance of model "%s"',[gettype($value_list),$this->name()]));
                    }

                    if (count($value_list) != 2) {
                        throw new WException(vsprintf('value require two date values for key "%s", in instance of model "%s"',[$key,$this->name()]));
                    }

                    $between_value_list[] = $value_list[0];
                    $between_value_list[] = $value_list[1];

                    $between_value = vsprintf('%s between ? and ?',[$key,]);

                    $between_query[] = $between_value;
                }

                $this->setBetween($between_query);
                $this->setBetweenValue($between_value_list);
            }

            return $this;
        }
        /**
         * @param array $where
         * @return mixed
         * @throws Exception
         * @throws WException
         */
        public function get($where = []) {
            $transaction = $this->getTransaction();

            if (empty($transaction)) {
                throw new WException(vsprintf('[get]transaction object not loaded in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('[get]incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            $transaction_resource = $transaction->getResource();

            if (empty($transaction_resource)) {
                throw new WException(vsprintf('[get]transaction instance not loaded, in model instance "%s"',[$this->name(),]));
            }

            if (empty($where)) {
                throw new WException(vsprintf('[get]where condition not defined, in model instance "%s"',[$this->name(),]));
            }

            $table_column = $this->getTableColumn();
            $table_name = $this->getTableName();
            $table_schema = $this->getTableSchema();
            $related = $this->related($this);

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            $related_join = null;
            $related_column = [];

            if (!empty($related) && !empty($related['join'])) {
                $related_join = implode(' ',$related['join']);

                foreach ($related['column'] as $i => $column) {
                    $related_column[] = implode(',',$column);
                }
            }

            $where_escape_list = [];
            $query_value_list = [];

            foreach ($where as $i => $value) {
                $where_escape_list[] = vsprintf('%s=?',[$i,]);
                $query_value_list[] = $value;
            }

            $column_list = [];

            foreach ($table_column as $i => $column) {
                if (!array_key_exists($i,$table_schema)) {
                    throw new WException(vsprintf('[get]field missing "%s", check your schema, in model instance "%s"',[$i,$this->name(),]));
                }

                $column_list[] = vsprintf('%s.%s %s__%s',[$table_name_with_escape,$i,$table_name,$i]);
            }

            $column_list = array_merge($related_column,$column_list);
            $column_list = implode(',',$column_list);

            $where = vsprintf('where %s',[implode(' and ',$where_escape_list),]);

            $query_total = vsprintf('select count(1) total from %s %s %s',[$table_name_with_escape,$related_join,$where]);
            $query = vsprintf('select %s from %s %s %s',[$column_list,$table_name_with_escape,$related_join,$where]);

            try {
                if (empty($this->flag_new_or_update)) {
                    $pdo_query_total = $transaction_resource->prepare($query_total);

                    $transaction_resource_error_info = $transaction_resource->errorInfo();

                    if ($transaction_resource_error_info[0] != '00000') {
                        throw new WException(vsprintf('[get]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                    }

                    $pdo_query_total->execute($query_value_list);
                    $pdo_query_total = $pdo_query_total->fetch(PDO::FETCH_OBJ);

                    $this->setQuery($query_total,$query_value_list);

                    if (empty($pdo_query_total)) {
                        throw new WException(vsprintf('[get]query error, in model instance "%s"',[$this->name(),]));
                    }

                    if ($pdo_query_total->total <= 0) {
                        throw new WException(vsprintf('[get]query result is empty, in model instance "%s"',[$this->name(),]));
                    }

                    if ($pdo_query_total->total > 1) {
                        throw new WException(vsprintf('[get]query result not unique, in model instance "%s"',[$this->name(),]));
                    }
                }

                $this->flag_new_or_update = false;

                $pdo_query = $transaction_resource->prepare($query);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[get]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $pdo_query->execute($query_value_list);
                $pdo_query_fetch = $pdo_query->fetch(PDO::FETCH_OBJ);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            foreach ($table_column as $column => $value) {
                $table_column_str = vsprintf('%s__%s',[$table_name,$column]);

                $this->$column = $pdo_query_fetch->$table_column_str;
            }

            $obj_column_list = $this->getTableColumn();
            $obj_schema_dict = $table_schema;

            $query_fetch = $pdo_query_fetch;
            $obj = $this;

            $related_fetch = $this->relatedFetch($obj_column_list,$obj_schema_dict,$query_fetch,$transaction,$obj);

            $this->setWhere(null);
            $this->setWhereValue(null);

            $this->setWhereUnique($where_escape_list);
            $this->setWhereUniqueValue($query_value_list);

            $this->setQuery($query,$query_value_list);

            return $related_fetch;
        }
        /**
         * @param null $field
         * @return $this
         * @throws Exception
         * @throws WException
         */
        public function save($field = null) {
            $transaction = $this->getTransaction();

            if (empty($transaction)) {
                throw new WException(vsprintf('[save]transaction object do not loaded in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('[save]incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            $transaction_resource = $transaction->getResource();

            if (empty($transaction_resource)) {
                throw new WException(vsprintf('[save]transaction instance not loaded, in model instance "%s"',[$this->name(),]));
            }

            $table_name = $this->getTableName();
            $table_column = $this->getTableColumn();
            $table_schema = $this->getTableSchema();
            $primary_key = $this->getPrimaryKey();
            $last_insert_id = $this->getLastInsertId();

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            $column_list = [];
            $query_escape_list = [];
            $query_value_update_list = [];
            $query_value_add_list = [];
            $set_escape = [];
            $flag_getdiscard = false;

            if (!empty($field)) {
                if (!is_array($field)) {
                    throw new WException(vsprintf('[save]incorrect type of parameter, in model instance "%s"',[$this->name(),]));
                }

                $this->setLastInsertId(null);

                $table_column = $field;
            }

            foreach ($table_column as $key => $value) {
                if (!array_key_exists($key,$table_schema)) {
                    throw new WException(vsprintf('[save]field missing "%s", check your schema, in model instance "%s"',[$key,$this->name(),]));
                }

                if ($primary_key != $key) {
                    $method = $table_schema[$key]->method;
                    $rule = $table_schema[$key]->rule;

                    $value = $this->$method($rule,$value,true);

                    $set_escape[] = vsprintf('%s=?',[$key,]);
                    $query_value_update_list[] = $value;

                    $column_list[] = $key;
                    $query_value_add_list[] = $value;
                    $query_escape_list[] = '?';
                }
            }

            $set_escape = implode(',',$set_escape);

            if (!empty($table_column[$primary_key])) {
                $where = vsprintf('%s=%s',[$primary_key,$table_column[$primary_key]]);

            } else {
                $where = vsprintf('%s=%s',[$primary_key,$last_insert_id]);
            }

            $column_list = implode(',',$column_list);
            $query_escape_list = implode(',',$query_escape_list);

            if (!empty($last_insert_id) || !empty($table_column[$primary_key])) {
                $query = vsprintf('update %s set %s where %s',[$table_name_with_escape,$set_escape,$where]);
                $query_value_list = $query_value_update_list;

                $flag_getdiscard = true;

            } else {
                $query = vsprintf('insert into %s (%s) values(%s)',[$table_name_with_escape,$column_list,$query_escape_list]);
                $query_value_list = $query_value_add_list;
            }

            try {
                $pdo_query = $transaction_resource->prepare($query);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[save]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $pdo_query->execute($query_value_list);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            $pdo_query_error_info = $pdo_query->errorInfo();

            if ($pdo_query_error_info[0] != '00000') {
                throw new WException(vsprintf('[save]PDO error message "%s", in model instance "%s"',[$pdo_query_error_info[2],$this->name(),]));
            }

            if (empty($flag_getdiscard)) {
                $this->flag_new_or_update = true;

                $get_database_info = $transaction->getDatabaseInfo();

                $sequence_name = null;

                if ($get_database_info['driver'] == 'pgsql') {
                    $sequence_name = vsprintf('%s_id_seq',[$table_name,]);
                }

                $last_insert_id = $transaction->lastInsertId($sequence_name);

                $this->setLastInsertId($last_insert_id);
                $this->get([
                    vsprintf('%s.id',[$table_name_with_escape,]) => $last_insert_id]);
            }

            $this->setQuery($query,$query_value_list);

            return $this;
        }
        /**
         * @param null $set
         * @return $this
         * @throws Exception
         * @throws WException
         */
        public function update($set = null) {
            $transaction = $this->getTransaction();

            if (empty($transaction)) {
                throw new WException(vsprintf('[update]transaction object do not loaded in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('[update]incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            if (empty($set)) {
                throw new WException(vsprintf('[update]set parameter missing, in model instance "%s"',[$this->name(),]));
            }

            if (!is_array($set)) {
                throw new WException(vsprintf('[update]set parameter not array, in model instance "%s"',[$this->name(),]));
            }

            $transaction_resource = $transaction->getResource();

            if (empty($transaction_resource)) {
                throw new WException(vsprintf('[update]transaction instance not loaded, in model instance "%s"',[$this->name(),]));
            }

            $table_name = $this->getTableName();
            $table_column = $this->getTableColumn();
            $table_schema = $this->getTableSchema();

            $set_escape = [];
            $query_value_update_list = [];

            foreach ($set as $key => $value) {
                if (!array_key_exists($key,$table_column)) {
                    throw new WException(vsprintf('[update]field missing "%s"(not use table.column notation), check your model, in model instance "%s"',[$key,$this->name(),]));
                }

                if (!array_key_exists($key,$table_schema)) {
                    throw new WException(vsprintf('[update]field missing "%s"(not use table.column notation), check your schema, in model instance "%s"',[$key,$this->name(),]));
                }

                $method = $table_schema[$key]->method;
                $rule = $table_schema[$key]->rule;

                $value = $this->$method($rule,$value,true);

                $set_escape[] = vsprintf('%s=?',[$key,]);
                $query_value_update_list[] = $value;
            }

            $set_escape = implode(',',$set_escape);

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            $where = '';
            $query_value = array_merge([],$query_value_update_list);

            $get_where_unique = $this->getWhereUnique();

            if (!empty($get_where_unique)) {
                $get_where_unique_value = $this->getWhereUniqueValue();

                $where = vsprintf('where %s',[implode(' and ',$get_where_unique),]);

                $query_value = array_merge($query_value,$get_where_unique_value);

            } else {
                $get_where = $this->getWhere();
                $get_where_value = $this->getWhereValue();

                if (!empty($get_where)) {
                    $where .= implode(' and ',$get_where);

                    $query_value = array_merge($query_value,$get_where_value);
                }

                if (!empty($where)) {
                    $where = vsprintf('where %s',[$where,]);
                }
            }

            $query = vsprintf('update %s set %s %s',[$table_name_with_escape,$set_escape,$where]);

            try {
                $query = $transaction_resource->prepare($query);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[update]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $query->execute($query_value);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            foreach ($set as $key => $value) {
                $this->$key = $value;
            }

            $this->setQuery($query,$query_value);

            return $this;
        }
        /**
         * @param null $where
         * @return $this
         * @throws Exception
         * @throws WException
         */
        public function delete($where = null) {
            $transaction = $this->getTransaction();

            if (empty($transaction)) {
                throw new WException(vsprintf('[delete]transaction object do not loaded in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('[delete]incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            $transaction_resource = $transaction->getResource();

            if (empty($transaction_resource)) {
                throw new WException(vsprintf('[delete]transaction instance not loaded, in model instance "%s"',[$this->name(),]));
            }

            $table_name = $this->getTableName();
            $table_column = $this->getTableColumn();
            $table_schema = $this->getTableSchema();

            $where_str = '';
            $query_value = [];

            if (!empty($where)) {
                $where_list = [];

                if (!is_array($where)) {
                    throw new WException(vsprintf('[delete]where parameter not array, in model instance "%s"',[$this->name(),]));
                }

                foreach ($where as $key => $value) {
                    if (!array_key_exists($key,$table_column)) {
                        throw new WException(vsprintf('[delete]field missing "%s"(not use table.column notation), check your model, in model instance "%s"',[$key,$this->name(),]));
                    }

                    if (!array_key_exists($key,$table_schema)) {
                        throw new WException(vsprintf('[delete]field missing "%s"(not use table.column notation), check your schema, in model instance "%s"',[$key,$this->name(),]));
                    }

                    if (empty($value)) {
                        throw new WException(vsprintf('[delete]field "%s" value is empty, in model instance "%s"',[$key,$this->name(),]));
                    }

                    if (!is_array($value) && (is_string($value) || is_numeric($value) || is_bool($value))) {
                        $query_value[] = $value;

                        $where_list[] = vsprintf('%s=?',[$key,]);

                    } else if (is_array($value)) {
                        $query_value = array_merge($query_value,$value);

                        $value = implode(',',array_map(function ($value) {
                            if (empty($value)) {
                                throw new WException(vsprintf('[delete]field value is empty, in model instance "%s"',[$this->name(),]));
                            }

                            return '?';
                        },$value));

                        $where_list[] = vsprintf('%s in(%s)',[$key,$value]);

                    } else {
                        throw new WException(vsprintf('value is incorrect with type "%s", in instance of model "%s"',[gettype($value),$this->name()]));
                    }
                }

                $where_str = vsprintf('where %s',[implode(' and ',$where_list),]);

            } else {
                $get_where_unique = $this->getWhereUnique();

                if (!empty($get_where_unique)) {
                    $get_where_unique_value = $this->getWhereUniqueValue();

                    $where_str = vsprintf('where %s',[implode(' and ',$get_where_unique),]);

                    $query_value = $get_where_unique_value;

                } else {
                    $get_where = $this->getWhere();
                    $get_where_value = $this->getWhereValue();

                    if (!empty($get_where)) {
                        $where_str .= implode(' and ',$get_where);

                        $query_value = array_merge($query_value,$get_where_value);
                    }

                    if (!empty($where_str)) {
                        $where_str = vsprintf('where %s',[$where_str,]);
                    }
                }
            }

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            $query = vsprintf('delete from %s %s',[$table_name_with_escape,$where_str]);

            try {
                $query = $transaction_resource->prepare($query);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[delete]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $query->execute($query_value);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            foreach ($table_column as $column => $value) {
                $this->$column = null;
            }

            $this->setQuery($query,$query_value);

            return $this;
        }
        /**
         * @param array $setting
         * @return array
         * @throws Exception
         * @throws WException
         */
        public function execute($setting = []) {
            $transaction = $this->getTransaction();

            if (empty($transaction)) {
                throw new WException(vsprintf('[execute]transaction object do not loaded in model instance "%s"',[$this->name(),]));
            }

            if (!$transaction instanceof Transaction) {
                throw new WException(vsprintf('[execute]incorrect loaded instance of Transaction, in model instance "%s"',[$this->name(),]));
            }

            $transaction_resource = $transaction->getResource();

            if (empty($transaction_resource)) {
                throw new WException(vsprintf('[execute]transaction instance not loaded, in model instance "%s"',[$this->name(),]));
            }

            $join = null;

            if (!empty($setting)) {
                if (array_key_exists('join',$setting)) {
                    $join = $setting['join'];
                }
            }

            $table_name = $this->getTableName();
            $table_column = $this->getTableColumn();
            $get_where = $this->getWhere();
            $get_where_value = $this->getWhereValue();
            $get_between = $this->getBetween();
            $get_between_value = $this->getBetweenValue();
            $get_like = $this->getLike();
            $get_like_value = $this->getLikeValue();
            $order_by = $this->getOrderBy();
            $related = $this->related($this,[],$join);
            $limit = $this->getLimit();

            if (empty($limit)) {
                $limit = defined(QUERY_LIMIT) ? QUERY_LIMIT : 1000;

                $this->setLimit(vsprintf('limit %s offset 0',[$limit,]));
                $this->setLimitValue(1,$limit);

                $limit = $this->getLimit();
            }

            $table_name_with_escape = vsprintf('%s%s%s',[$this->db_escape,$table_name,$this->db_escape]);

            $related_join = null;
            $related_column = [];

            if (!empty($related) && !empty($related['join'])) {
                $related_join = implode(' ',$related['join']);

                foreach ($related['column'] as $i => $column) {
                    $related_column[] = implode(',',$column);
                }

            }

            $column_list = [];

            foreach ($table_column as $i => $column) {
                $column_list[] = vsprintf('%s.%s %s__%s',[$table_name_with_escape,$i,$table_name,$i]);
            }

            $column_list = array_merge($related_column,$column_list);
            $column_list = implode(',',$column_list);

            $query_value = [];

            $where_implicit = 'where';

            if (empty($get_where)) {
                $where = '';

            } else {
                $where = vsprintf('%s',[implode(' and ',$get_where),]);

                $query_value = array_merge($query_value,$get_where_value);
            }

            if (empty($get_between)) {
                $between = '';

            } else {
                if (!empty($where)) {
                    $between = vsprintf('and %s',[implode(' and ',$get_between),]);

                } else {
                    $between = vsprintf('%s',[implode(' and ',$get_between),]);
                }

                $query_value = array_merge($query_value,$get_between_value);
            }

            if (empty($get_like)) {
                $like = '';

            } else {
                if (!empty($where) || !empty($between)) {
                    $like = vsprintf('and %s',[implode(' and ',$get_like),]);

                } else {
                    $like = vsprintf('%s',[implode(' and ',$get_like),]);
                }

                $query_value = array_merge($query_value,$get_like_value);
            }

            if (empty($where) && empty($like)) {
                $where_implicit = '';
            }

            if (empty($order_by)) {
                $order_by = '';

            } else {
                $order_by = vsprintf('order by %s',[implode(',',$order_by),]);
            }

            $query_total = vsprintf('select count(1) total from %s %s %s %s %s',[$table_name_with_escape,$related_join,$where_implicit,$where,$between,$like]);

            $this->setQuery($query_total,$query_value);

            $query = vsprintf('select %s from %s %s %s %s %s %s %s',[$column_list,$table_name_with_escape,$related_join,$where_implicit,$where,$between,$like,$order_by,$limit]);

            $this->setQuery($query,$query_value);

            try {
                $pdo_query_total = $transaction_resource->prepare($query_total);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[execute]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $pdo_query_total->execute($query_value);
                $pdo_query_total = $pdo_query_total->fetch(PDO::FETCH_OBJ);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            if (!empty($pdo_query_total)) {
                $pdo_query_total = $pdo_query_total->total;

            } else {
                $pdo_query_total = 0;
            }

            try {
                $pdo_query = $transaction_resource->prepare($query);

                $transaction_resource_error_info = $transaction_resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('[execute]PDO error message "%s", in model instance "%s"',[$transaction_resource_error_info[2],$this->name(),]));
                }

                $pdo_query->execute($query_value);
                $query_fetch_all = $pdo_query->fetchAll(PDO::FETCH_OBJ);

            } catch (PDOException $error) {
                throw $error;

            } catch (Exception $error) {
                throw $error;
            }

            $query_fetch_all_list = [];

            if (!empty($query_fetch_all)) {
                $class_name = $this->getClassName();
                $table_name = $this->getTableName();
                $column_list = $this->getTableColumn();
                $transaction = $this->getTransaction();

                foreach ($query_fetch_all as $i => $query_fetch) {
                    $obj = new $class_name($transaction);

                    foreach ($column_list as $column => $value) {
                        $table_column = vsprintf('%s__%s',[$table_name,$column]);

                        $obj->$column = $query_fetch->$table_column;
                    }

                    $obj_column_list = $obj->getTableColumn();
                    $obj_schema_dict = $obj->schema();

                    $related_fetch = $this->relatedFetch($obj_column_list,$obj_schema_dict,$query_fetch,$transaction,$obj);

                    $query_fetch_all_list[] = $related_fetch;
                }
            }

            $limit_value = $this->getLimitValue();
            $register_total = $pdo_query_total;
            $register_perpage = $limit_value['limit'];
            $page_total = ceil($register_total / $register_perpage);
            $page_current = $limit_value['page'] >= $page_total ? $page_total : $limit_value['page'];
            $page_next = $page_current + 1 >= $page_total ? $page_total : $page_current + 1;
            $page_previous = $page_current - 1 <= 0 ? 1 : $page_current - 1;

            $result = [
                'register_total' => $register_total,
                'register_perpage' => $register_perpage,
                'page_total' => $page_total,
                'page_current' => $page_current,
                'page_next' => $page_next,
                'page_previous' => $page_previous,
                'data' => $query_fetch_all_list,
            ];

            return $result;
        }
        /**
         * @param $obj_column_list
         * @param $obj_schema_dict
         * @param $fetch
         * @param $transaction
         * @param $obj
         * @return mixed
         */
        private function relatedFetch($obj_column_list, $obj_schema_dict, $fetch, $transaction, $obj) {
            $table_name = $obj->getTableName();

            foreach ($obj_column_list as $column => $value) {
                if ($obj_schema_dict[$column]->method == 'foreignKey') {
                    $obj_foreignkey = $obj_schema_dict[$column]->rule['table'];

                    $obj_foreignkey_class_name = $obj_foreignkey->getClassName();
                    $obj_foreignkey_table_name = $obj_foreignkey->getTableName();
                    $obj_foreignkey_column_list = $obj_foreignkey->getTableColumn();
                    $obj_foreignkey_schema_dict = $obj_foreignkey->schema();

                    $obj_foreignkey = new $obj_foreignkey_class_name($transaction);

                    foreach ($obj_foreignkey_column_list as $column_ => $value_) {
                        $table_column = vsprintf('%s_%s__%s',[$table_name,$obj_foreignkey_table_name,$column_]);

                        $obj_foreignkey->$column_ = $fetch->$table_column;
                    }

                    $obj->$column = $obj_foreignkey;

                    $this->relatedFetch($obj_foreignkey_column_list,$obj_foreignkey_schema_dict,$fetch,$transaction,$obj_foreignkey);
                }
            }

            return $obj;
        }
        /**
         * @return array
         */
        public function dumpQuery() {
            $query = $this->getQuery();

            return $query;
        }
    }
}
