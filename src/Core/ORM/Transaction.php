<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core/ORM
 */
namespace Core\ORM {
    /**
     * Class Transaction
     * @constant PDO_DRIVE_LIST ['mysql','pgsql','sqlite']
     * @constant PDO_DRIVE_MYSQL 'mysql'
     * @constant PDO_DRIVE_PGSQL 'pgsql'
     * @constant PDO_DRIVE_SQLITE 'sqlite'
     * @var object $resource
     * @var string $database
     * @var int $last_insert_id
     * @var string $database_file
     */
    class Transaction {
        private const PDO_DRIVE_LIST = ['mysql','pgsql','sqlite'];
        private const PDO_DRIVE_MYSQL = 'mysql';
        private const PDO_DRIVE_PGSQL = 'pgsql';
        private const PDO_DRIVE_SQLITE = 'sqlite';

        private $resource;
        private $database;
        private $last_insert_id;
        private $database_file;
        /**
         * Transaction constructor.
         */
        public function __construct() {
            if (empty(defined('DATABASE_FILE'))) {
                throw new \Error('constant DATABASE_FILE not defined');
            }

            if (empty(defined('DATABASE'))) {
                throw new \Error('constant DATABASE not defined');
            }

            if (!file_exists(DATABASE_FILE)) {
                throw new \Error(vsprintf('database path dont find in "%s"',[DATABASE_FILE,]));
            }

            $database_file = json_decode(file_get_contents(DATABASE_FILE),true);
            $this->setDatabasePath($database_file);

            if (empty($database_file)) {
                throw new \Error(vsprintf('json encode error in database path "%s"',[$this->getDatabasePath(),]));
            }

            if (!array_key_exists(DATABASE,$database_file)) {
                throw new \Error(vsprintf('database "%s" dont find in object "%s"',[DATABASE,print_r($database_file,true),]));
            }

            if (!array_key_exists('driver',$database_file[DATABASE])) {
                throw new \Error(vsprintf('database driver key not registered in database object "%s"',[print_r($database_file,true)]));
            }

            $this->setDatabase(DATABASE);
        }
        /**
         * @return object
         */
        public function getResource(): \PDO {
            return $this->resource;
        }
        /**
         * @param object $resource \PDO
         * @return self
         */
        protected function setResource(\PDO $resource): self {
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
            return $this->database_file;
        }
        /**
         * @param array $database_file
         * @return self
         */
        protected function setDatabasePath(array $database_file): self {
            $this->database_file = $database_file;

            return $this;
        }
        /**
         * @return int
         */
        public function getLastInsertId(): int {
            return $this->last_insert_id;
        }
        /**
         * @param int $id
         * @return self
         */
        protected function setLastInsertId(int $id): self {
            $this->last_insert_id = $id;

            return $this;
        }
        /**
         * @return array
         * @throws \Error
         */
        public function getDatabaseInfo(): array {
            $database = $this->getDatabase();
            $database_file = $this->getDatabasePath();

            if (!array_key_exists($database,$database_file)) {
                throw new \Error(vsprintf('database "%s" dont find in object "%s"',[$database,print_r($database_file,true),]));
            }

            return $database_file[$database];
        }
        /**
         * @return self
         * @throws \Error
         */
        public function connect(): self {
            $database_info = $this->getDatabaseInfo();

            if (!in_array($database_info['driver'],self::PDO_DRIVE_LIST)) {
                throw new \Error(vsprintf('database driver "%s" not registered',[$database_info['driver'],]));
            }

            try {
                if (in_array($database_info['driver'],[self::PDO_DRIVE_MYSQL,self::PDO_DRIVE_PGSQL])) {
                    $pdo = new \PDO(vsprintf('%s:host=%s;port=%s;dbname=%s',[$database_info['driver'],$database_info['host'],$database_info['port'],$database_info['name']]),$database_info['user'],$database_info['password']);

                } else if ($database_info['driver'] == self::PDO_DRIVE_SQLITE) {
                    $pdo = new \PDO(vsprintf('%s:%s',[$database_info['driver'],$database_info['host']]));
                }

                if ($database_info['driver'] == self::PDO_DRIVE_MYSQL) {
                    if ($database_info['autocommit'] == 0) {
                        $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);

                    } else if ($database_info['autocommit'] == 1) {
                        $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
                    }
                }

                if (array_key_exists('debug',$database_info)) {
                    if ($database_info['debug'] == 0) {
                        $pdo->setAttribute(\PDO::ATTR_ERRMODE,0);

                    } else if ($database_info['debug'] == 1) {
                        $pdo->setAttribute(\PDO::ATTR_ERRMODE,1);
                    }
                }

            } catch (\Error $error) {
                throw $error;
            }

            $this->setResource($pdo);

            return $this;
        }
        /**
         * @return self
         * @throws \Error
         */
        public function beginTransaction(): self {
            $this->connect();

            $resource = $this->getResource();

            try {
                $resource->beginTransaction();

            } catch (\Error $error) {
                throw $error;
            }

            return $this;
        }
        /**
         * @return self
         * @throws \Error
         */
        public function commit(): self {
            $resource = $this->getResource();

            if (!empty($resource)) {
                try {
                    $this->resource->commit();

                } catch (\Error $error) {
                    throw $error;
                }
            }

            return $this;
        }
        /**
         * @return self
         * @throws \Error
         */
        public function rollBack(): self {
            $resource = $this->getResource();

            if (!empty($resource)) {
                try {
                    $this->resource->rollBack();

                } catch (\Error $error) {
                    throw $error;
                }
            }

            return $this;
        }
        /**
         * @param string $sequence_name null
         * @return int
         * @throws \Error
         */
        public function lastInsertId(string $sequence_name = null): int {
            $resource = $this->getResource();

            try {
                $this->setLastInsertId((int) $resource->lastInsertId($sequence_name));

            } catch (\Error $error) {
                throw $error;
            }

            return $this->getLastInsertId();
        }
        /**
         * @param string $query_raw
         * @param array $value []
         * @param bool $cud null
         * @return array
         * @throws \Error
         */
        public function queryRaw(string $query_raw,array $value = [],?bool $cud = false): array {
            $resource = $this->getResource();

            if (empty($resource)) {
                throw \Error('database resource dont loaded');
            }

            try {
                $pdo_query = $resource->prepare($query);

                $transaction_resource_error_info = $resource->errorInfo();

                if ($transaction_resource_error_info[0] != '00000') {
                    throw new \Error(vsprintf('PDO error message "%s"',[$transaction_resource_error_info[2],]));
                }

                $pdo_query->execute($value);

                if (empty($cud)) {
                    $result = $pdo_query->fetchAll(\PDO::FETCH_OBJ);

                } else {
                    $result = [$pdo_query->fetch(\PDO::FETCH_OBJ)];
                }

            } catch (\Error $error) {
                throw $error;
            }

            return $result;
        }
    }
}
