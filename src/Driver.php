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

use VV\Db\Exceptions\ConnectionError;

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
     * @param string      $dbms
     * @param string|null $dsnPrefix
     */
    public function __construct(string $dbms, string $dsnPrefix = null) {
        $this->dbms = $dbms;
        $this->dsnPrefix = $dsnPrefix
            ?: match ($dbms) {
                self::DBMS_POSTGRES => 'pgsql',
                self::DBMS_ORACLE => 'oci',
                self::DBMS_MYSQL => 'mysql',
                self::DBMS_MSSQL => 'sqlsrv',
            };
    }

    /**
     * @inheritDoc
     */
    public function connect(string $host, string $user, string $passwd, ?string $scheme, ?string $charset): Connection {
        $port = 5432;
        if (preg_match('/^(.+):(\d+)$/', $host, $m)) {
            [, $host, $port] = $m;
        }

        $dsn = "$this->dsnPrefix:host=$host;port=$port;dbname=$scheme";
        try {
            $pdo = new \PDO($dsn, $user, $passwd);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new ConnectionError(null, null, $e);
        }

        $this->onPdoConnect($pdo);

        return new Connection($pdo);
    }

    public function sqlStringifiersFactory(): ?\VV\Db\Sql\Stringifiers\Factory {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function dbms(): string {
        return $this->dbms;
    }

    protected function onPdoConnect(\PDO $pdo): void { }
}
