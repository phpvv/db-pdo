<?php

/*
 * This file is part of the VV package.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace VV\Db\Pdo;

use VV\Db\Driver\QueryInfo;
use VV\Db\Exceptions\SqlSyntaxError;

/**
 * Class Connection
 *
 * @package VV\Db\Pdo
 */
class Connection implements \VV\Db\Driver\Connection
{
    private ?\PDO $pdo;

    /**
     * Connection constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritdoc
     */
    public function prepare(QueryInfo $query): \VV\Db\Driver\Statement
    {
        try {
            $stmt = $this->pdo->prepare($query->getString());
        } catch (\PDOException $e) {
            throw new SqlSyntaxError(previous: $e);
        }

        return new Statement($stmt);
    }

    /**
     * @inheritDoc
     */
    public function startTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * @inheritdoc
     */
    public function commit(bool $autocommit = false): void
    {
        if ($autocommit) {
            return;
        }
        $this->pdo->commit();
    }

    /**
     * @inheritdoc
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * @inheritdoc
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }
}
