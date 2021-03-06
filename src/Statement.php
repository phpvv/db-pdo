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

use VV\Db\Exceptions\ConnectionError;
use VV\Db\Exceptions\SqlExecutionError;
use VV\Db\Param;

/**
 * Class Statement
 *
 * @package VV\Db\Pdo
 */
class Statement implements \VV\Db\Driver\Statement
{
    private ?\PDOStatement $stmt;
    private bool $hasInsertedId = false;

    public function __construct(\PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bind(array $params): void
    {
        if (!$params) {
            return;
        }

        $i = 0;
        foreach ($params as $k => &$param) {
            if ($param instanceof Param && $param->isForInsertedId()) {
                $this->hasInsertedId = true;
                continue;
            }

            if (is_string($k)) {
                $name = ":$k";
            } elseif ($param instanceof Param && ($n = $param->getName())) {
                $name = ":$n";
            } else {
                $name = ':p' . ++$i;
            }

            if ($param instanceof Param) {
                $this->pdoBindParam($name, $param->getValue(), $this->toPdoType($param), $param->getSize() ?: 0);
                $param->setBinded();
            } else {
                $this->pdoBindParam($name, $param, $this->toPdoType($param));
            }
        }
        unset($param);
    }

    public function exec(): \VV\Db\Driver\Result
    {
        // execute query
        try {
            $this->stmt->execute();
        } catch (\PDOException $e) {
            static $reconnectCodes = ['HY000', '57P01', '57P02', '57P03'];
            if (in_array($e->getCode(), $reconnectCodes)) {
                throw new ConnectionError();
            }

            throw new SqlExecutionError(null, null, $e);
        }

        $insertedId = $this->hasInsertedId ? $this->stmt->fetch()['_insertedid'] : null;

        return new Result($this->stmt, $insertedId);
    }

    /**
     * @inheritDoc
     */
    public function setFetchSize(int $size): void
    {
        $this->stmt->setAttribute(\PDO::ATTR_PREFETCH, $size);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->stmt = null;
    }

    private function pdoBindParam($name, &$value, int $type, int $size = 0)
    {
        if ($value instanceof \Generator) {
            $strvalue = '';
            foreach ($value as $s) {
                $strvalue .= $s;
            }

            $value = &$strvalue; // unlink old reference
        }

        $res = $this->stmt->bindParam($name, $value, $type, $size);
        if (!$res) {
            throw new \RuntimeException('Bind params error: ' . $name . ' ' . $value);
        }
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    private function toPdoType(mixed $value): int
    {
        $paramType = null;
        if ($value instanceof Param) {
            $paramType = $value->getType();
            $value = $value->getValue();
        }

        if ($paramType) {
            switch ($paramType) {
                case Param::T_INT:
                    return \PDO::PARAM_INT;

                case Param::T_CHR:
                case Param::T_TEXT:
                    return \PDO::PARAM_STR;

                case Param::T_BLOB:
                    return \PDO::PARAM_LOB;

                case Param::T_BIN:
                    throw new \LogicException('Not supported yet');
                // return \PDO::BIN;
            }
        }

        if (is_int($value)) {
            return \PDO::PARAM_INT;
        }

        return \PDO::PARAM_STR;
    }
}
