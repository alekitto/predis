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

/**
 * @group commands
 * @group realm-string
 */
class BITOP_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand()
    {
        return 'Predis\Command\Redis\BITOP';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId()
    {
        return 'BITOP';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments()
    {
        $arguments = array('AND', 'key:dst', 'key:01', 'key:02');
        $expected = array('AND', 'key:dst', 'key:01', 'key:02');

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testFilterArgumentsKeysAsSingleArray()
    {
        $arguments = array('AND', 'key:dst', array('key:01', 'key:02'));
        $expected = array('AND', 'key:dst', 'key:01', 'key:02');

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse()
    {
        $raw = 10;
        $expected = 10;

        $command = $this->getCommand();

        $this->assertSame($expected, $command->parseResponse($raw));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testCanPerformBitwiseAND()
    {
        $redis = $this->getClient();

        $redis->set('key:src:1', "h\x80");
        $redis->set('key:src:2', 'R');

        $this->assertSame(2, $redis->bitop('AND', 'key:dst', 'key:src:1', 'key:src:2'));
        $this->assertSame("@\x00", $redis->get('key:dst'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testCanPerformBitwiseOR()
    {
        $redis = $this->getClient();

        $redis->set('key:src:1', "h\x80");
        $redis->set('key:src:2', 'R');

        $this->assertSame(2, $redis->bitop('OR', 'key:dst', 'key:src:1', 'key:src:2'));
        $this->assertSame("z\x80", $redis->get('key:dst'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testCanPerformBitwiseXOR()
    {
        $redis = $this->getClient();

        $redis->set('key:src:1', "h\x80");
        $redis->set('key:src:2', 'R');

        $this->assertSame(2, $redis->bitop('XOR', 'key:dst', 'key:src:1', 'key:src:2'));
        $this->assertSame(":\x80", $redis->get('key:dst'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testCanPerformBitwiseNOT()
    {
        $redis = $this->getClient();

        $redis->set('key:src:1', "h\x80");

        $this->assertSame(2, $redis->bitop('NOT', 'key:dst', 'key:src:1'));
        $this->assertSame("\x97\x7f", $redis->get('key:dst'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testBitwiseNOTAcceptsOnlyOneSourceKey()
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('ERR BITOP NOT must be called with a single source key');

        $this->getClient()->bitop('NOT', 'key:dst', 'key:src:1', 'key:src:2');
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testThrowsExceptionOnInvalidOperation()
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('ERR syntax error');

        $this->getClient()->bitop('NOOP', 'key:dst', 'key:src:1', 'key:src:2');
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testThrowsExceptionOnInvalidSourceKey()
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->lpush('key:src:1', 'list');
        $redis->bitop('AND', 'key:dst', 'key:src:1', 'key:src:2');
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testDoesNotThrowExceptionOnInvalidDestinationKey()
    {
        $redis = $this->getClient();

        $redis->lpush('key:dst', 'list');
        $redis->bitop('AND', 'key:dst', 'key:src:1', 'key:src:2');

        $this->assertEquals('none', $redis->type('key:dst'));
    }
}
