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

use VV\Db;
use VV\Db\Driver\Result as ResultInterface;

/**
 * Class Result
 *
 * @package VV\Db\Pdo
 */
class Result implements ResultInterface
{
    private \PDOStatement $stmt;
    private int|string|null $insertedId;

    public function __construct(\PDOStatement $stmt, int|string|null $insertedId)
    {
        $this->stmt = $stmt;
        $this->insertedId = $insertedId;
    }

    public function getIterator(int $flags): \Traversable
    {
        $pdoFlags = 0;
        if ($fetchAssoc = (bool)($flags & Db::FETCH_ASSOC)) {
            $pdoFlags = \PDO::FETCH_ASSOC;
        }
        if ($flags & Db::FETCH_NUM) {
            $pdoFlags = $fetchAssoc ? \PDO::FETCH_BOTH : \PDO::FETCH_NUM;
        }

        $this->stmt->setFetchMode($pdoFlags);
        while ($row = $this->stmt->fetch()) {
            yield $row;
        }
    }

    /**
     * @inheritdoc
     */
    public function getInsertedId(): int|string|null
    {
        return $this->insertedId;
    }

    public function getAffectedRows(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Closes statement
     */
    public function close(): void
    {
    }
}
