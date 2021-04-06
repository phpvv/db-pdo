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
 * Class Result
 *
 * @package VV\Db\Pdo
 */
class Result implements \VV\Db\Driver\Result {

    private \PDOStatement $stmt;
    private mixed $insertedId;

    public function __construct($stmt, $insertedId) {
        $this->stmt = $stmt;
        $this->insertedId = $insertedId;
    }

    public function fetchIterator(int $flags): \Traversable {
        $pdoFlags = 0;
        if ($fassoc = (bool)($flags & \VV\Db::FETCH_ASSOC)) {
            $pdoFlags = \PDO::FETCH_ASSOC;
        }
        if ($flags & \VV\Db::FETCH_NUM) {
            $pdoFlags = $fassoc ? \PDO::FETCH_BOTH : \PDO::FETCH_NUM;
        }

        $this->stmt->setFetchMode($pdoFlags);
        while ($row = $this->stmt->fetch()) {
            yield $row;
        }
    }

    /**
     * @inheritdoc
     */
    public function insertedId() {
        return $this->insertedId;
    }

    public function affectedRows(): int {
        return $this->stmt->rowCount();
    }

    /**
     * Closes statement
     */
    public function close(): void { }
}
