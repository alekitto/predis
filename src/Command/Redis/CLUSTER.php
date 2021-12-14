<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Command\Redis;

use Predis\Command\Command as RedisCommand;

/**
 * @link http://redis.io/commands/cluster-info
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class CLUSTER extends RedisCommand
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'CLUSTER';
    }
    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        $args = array_change_key_case($this->getArguments(), CASE_UPPER);

        switch (strtoupper($args[0])) {
            case 'INFO':
                return $this->parseClusterInfo($data);
            default:
                return $data;
        } // @codeCoverageIgnore
    }

    public function getSlot()
    {
        return mt_rand(0, 16384);
    }

    private function parseClusterInfo($data)
    {
        if ($data === null) {
            return [];
        }

        $rows = explode("\n", $data);
        $result = [];
        foreach ($rows as $row) {
            list($key, $value) = explode(':', $row, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }
}
