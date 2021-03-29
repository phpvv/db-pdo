<?php declare(strict_types=1);

/*
 * This file is part of the VV package.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VV\Db\Pdo;

use VV\Db\Driver\QueryStringifiers;
use VV\Db\Exceptions\ConnectionError as ConnError;
use VV\Db\Sql;

/**
 * Class Driver
 *
 * @package VV\Db\Pdo
 */
class Driver implements \VV\Db\Driver\Driver {

    private string $dsnPrefix;
    private string $dbms;

    /**
     * Pdo constructor.
     *
     * @param string $dsnPrefix
     * @param string $dbms
     */
    public function __construct(string $dsnPrefix, string $dbms) {
        $this->dsnPrefix = $dsnPrefix;
        $this->dbms = $dbms;
    }

    /**
     * @inheritDoc
     */
    public function connect(string $host, string $user, string $passwd, ?string $scheme, ?string $charset): Connection {
        $dsn = "$this->dsnPrefix:host=$host;dbname=$scheme";
        try {
            $pdo = new \PDO($dsn, $user, $passwd);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new ConnError(null, null, $e);
        }

        $this->onPdoConnect($pdo);

        return new Connection($pdo);
    }

    /**
     * @inheritDoc
     */
    public function createSelectStringifier(Sql\SelectQuery $query): QueryStringifiers\SelectStringifier {
        return new QueryStringifiers\SelectStringifier($query, $this);
    }

    /**
     * @inheritDoc
     */
    public function createInsertStringifier(Sql\InsertQuery $query): QueryStringifiers\InsertStringifier {
        return new QueryStringifiers\InsertStringifier($query, $this);
    }

    /**
     * @inheritDoc
     */
    public function createUpdateStringifier(Sql\UpdateQuery $query): QueryStringifiers\UpdateStringifier {
        return new QueryStringifiers\UpdateStringifier($query, $this);
    }

    /**
     * @inheritDoc
     */
    public function createDeleteStringifier(Sql\DeleteQuery $query): QueryStringifiers\DeleteStringifier {
        return new QueryStringifiers\DeleteStringifier($query, $this);
    }

    /**
     * @inheritDoc
     */
    public function dbms(): string {
        return $this->dbms;
    }

    protected function onPdoConnect(\PDO $pdo): void { }
}
