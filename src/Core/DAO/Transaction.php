<?php
/**
 * @author William Borba
 * @package Core/DAO
 * @uses Core\Exception\WException
 * @uses \PDO
 * @uses \PDOException
 */
namespace Core\DAO {
    use Core\Exception\WException;
    use \PDO as PDO;
    use \PDOException as PDOException;
    /**
     * Class Transaction
     * @constant PDO_DRIVE ['mysql','pgsql','sqlite']
     * @var object $resource
     * @var string $database
     * @var integer $last_insert_id
     * @var string $database_path
     */
    class Transaction {
        private const PDO_DRIVE = ['mysql','pgsql','sqlite'];

        private $resource;
        private $database;
        private $last_insert_id;
        private $database_path;
        /**
         * Transaction constructor.
         * @param string $database null
         * @return void
         */
        public function __construct(?string $database = null): void {
            if (empty(defined('DATABASE_PATH'))) {
                throw new WException('constant DATABASE_PATH not defined');
            }

            $this->setDatabasePath(DATABASE_PATH);

            if (empty(defined('DATABASE'))) {
                throw new WException('constant DATABASE not defined');
            }

            $this->setDatabase(DATABASE);

            if (empty($database)) {
                $database = $this->getDatabase();

                $this->setDatabase($database);
            }

            $database_path = $this->getDatabasePath();

            if (!file_exists($database_path)) {
                throw new WException(vsprintf('database path dont find in "%s"',[$database_path,]));
            }

            $database_path = json_decode(file_get_contents($database_path),true);
            $this->setDatabasePath($database_path);

            if (empty($database_path)) {
                throw new WException(vsprintf('json encode error in database path "%s"',[$this->getDatabasePath(),]));
            }

            if (!array_key_exists($database,$database_path)) {
                throw new WException(vsprintf('database "%s" dont find in object "%s"',[$database,print_r($database_path,true),]));
            }

            if (!array_key_exists('driver',$database_path[$database])) {
                throw new WException(vsprintf('database driver key not registered in database object "%s"',[print_r($database_path,true)]));
            }

            $this->setDatabase($database);
        }
        /**
         * @return object
         */
        public function getResource(): object {
            return $this->resource;
        }
        /**
         * @param object $resource
         * @return self
         */
        protected function setResource(object $resource): self {
            $this->resource = $resource;

            return $this;
        }
        /**
         * @return string
         */
        public function getDatabase(): string {
            return $this->database;
        }
        /**
         * @param string $database
         * @return self
         */
        protected function setDatabase(string $database): self {
            $this->database = $database;

            return $this;
        }
        /**
         * @return array
         */
        public function getDatabasePath(): array {
            return $this->database_path;
        }
        /**
         * @param array $database_path
         * @return self
         */
        protected function setDatabasePath(array $database_path): self {
            $this->database_path = $database_path;

            return $this;
        }
        /**
         * @return integer
         */
        public function getLastInsertId(): integer {
            return $this->last_insert_id;
        }
        /**
         * @param integer $id
         * @return self
         */
        protected function setLastInsertId(integer $id): self {
            $this->last_insert_id = $id;

            return $this;
        }
        /**
         * @return array
         * @throws WException
         */
        public function getDatabaseInfo(): array {
            $database = $this->getDatabase();
            $database_path = $this->getDatabasePath();

            if (!array_key_exists($database,$database_path)) {
                throw new WException(vsprintf('database "%s" dont find in object "%s"',[$database,print_r($database_path,true),]));
            }

            return $database_path[$database];
        }
        /**
         * @return self
         * @throws WException|PDOException
         */
        public function connect(): self {
            $database_info = $this->getDatabaseInfo();

            if (!in_array($database_info['driver'],self::PDO_DRIVE)) {
                throw new WException(vsprintf('database driver "%s" not registered',[$database_info['driver'],]));
            }

            try {
                if (in_array($database_info['driver'],['mysql','pgsql'])) {
                    $pdo = new PDO(vsprintf('%s:host=%s;port=%s;dbname=%s',[$database_info['driver'],$database_info['host'],$database_info['port'],$database_info['name']]),$database_info['user'],$database_info['password']);

                } else if ($database_info['driver'] == 'sqlite') {
                    $pdo = new PDO(vsprintf('%s:%s',[$database_info['driver'],$database_info['host']]));
                }

                if ($database_info['driver'] == 'mysql') {
                    if ($database_info['autocommit'] == 0) {
                        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT,0);

                    } else if ($database_info['autocommit'] == 1) {
                        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT,1);
                    }
                }

                if (array_key_exists('debug',$database_info)) {
                    if ($database_info['debug'] == 0) {
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,0);

                    } else if ($database_info['debug'] == 1) {
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,1);
                    }
                }

            } catch (PDOException | WException $error) {
                throw $error;
            }

            $this->setResource($pdo);

            return $this;
        }
        /**
         * @return self
         * @throws WException|PDOException
         */
        public function beginTransaction(): self {
            $this->connect();

            $resource = $this->getResource();

            try {
                $resource->beginTransaction();

            } catch (PDOException | WException $error) {
                throw $error;
            }

            return $this;
        }
        /**
         * @return self
         * @throws WException|PDOException
         */
        public function commit(): self {
            $resource = $this->getResource();

            if (!empty($resource)) {
                try {
                    $this->resource->commit();

                } catch (PDOException | WException $error) {
                    throw $error;
                }
            }

            return $this;
        }
        /**
         * @return self
         * @throws WException|PDOException
         */
        public function rollBack(): self {
            $resource = $this->getResource();

            if (!empty($resource)) {
                try {
                    $this->resource->rollBack();

                } catch (PDOException | WException $error) {
                    throw $error;
                }
            }

            return $this;
        }
        /**
         * @param string $sequence_name null
         * @return integer
         * @throws WException|PDOException
         */
        public function lastInsertId(string $sequence_name = null): integer {
            $resource = $this->getResource();

            try {
                $this->setLastInsertId($resource->lastInsertId($sequence_name));

            } catch (PDOException | WException $error) {
                throw $error;
            }

            return $this->getLastInsertId();
        }
        /**
         * @param string $query_raw
         * @param array $value []
         * @param boolean $cud false
         * @return array
         * @throws PDOException | WException
         */
        public function queryRaw(string $query_raw,array $value = [],boolean $cud = false): array {
            $resource = $this->getResource();

            if (empty($resource)) {
                throw WException('database resource dont loaded');
            }

            try {
                $pdo_query = $resource->prepare($query);

                $transaction_resource_error_info = $resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new WException(vsprintf('PDO error message "%s"',[$transaction_resource_error_info[2],]));
                }

                $pdo_query->execute($value);

                if (empty($cud)) {
                    $result = $pdo_query->fetchAll(PDO::FETCH_OBJ);

                } else {
                    $result = [$pdo_query->fetch(PDO::FETCH_OBJ)];
                }

            } catch (PDOException | WException $error) {
                throw $error;
            }

            return $result;
        }
    }
}
