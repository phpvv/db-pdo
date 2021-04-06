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

/**
 * Class Connection
 *
 * @package VV\Db\Pdo
 */
class Connection implements \VV\Db\Driver\Connection {

    private ?\PDO $pdo;

    /**
     * Connection constructor.
     *
     * @param $pdo
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * @inheritdoc
     */
    public function prepare(\VV\Db\Driver\QueryInfo $query): \VV\Db\Driver\Statement {
        try {
            $stmt = $this->pdo->prepare($query->string());
        } catch (\PDOException $e) {
            throw new \VV\Db\Exceptions\SqlSyntaxError(null, null, $e);
        }

        return new Statement($stmt, $query);
    }

    /**
     * @inheritDoc
     */
    public function startTransaction(): void {
        $this->pdo->beginTransaction();
    }

    /**
     * @inheritdoc
     */
    public function commit(bool $autocommit = false): void {
        if ($autocommit) return;
        $this->pdo->commit();
    }

    /**
     * @inheritdoc
     */
    public function rollback(): void {
        $this->pdo->rollBack();
    }

    /**
     * @inheritdoc
     */
    public function disconnect(): void {
        $this->pdo = null;
    }
}
