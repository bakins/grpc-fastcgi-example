<?php
/*
 *
 * Copyright 2015, Google Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

class ChannelTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        if (!empty($this->channel)) {
            $this->channel->close();
        }
    }

    public function testInsecureCredentials()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $this->assertSame('Grpc\Channel', get_class($this->channel));
    }

    public function testGetConnectivityState()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $state = $this->channel->getConnectivityState();
        $this->assertEquals(0, $state);
    }

    public function testGetConnectivityStateWithInt()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $state = $this->channel->getConnectivityState(123);
        $this->assertEquals(0, $state);
    }

    public function testGetConnectivityStateWithString()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $state = $this->channel->getConnectivityState('hello');
        $this->assertEquals(0, $state);
    }

    public function testGetConnectivityStateWithBool()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $state = $this->channel->getConnectivityState(true);
        $this->assertEquals(0, $state);
    }

    public function testGetTarget()
    {
        $this->channel = new Grpc\Channel('localhost:8888',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $target = $this->channel->getTarget();
        $this->assertTrue(is_string($target));
    }

    public function testWatchConnectivityState()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $time = new Grpc\Timeval(1000);
        $state = $this->channel->watchConnectivityState(1, $time);
        $this->assertTrue($state);
        unset($time);
    }

    public function testClose()
    {
        $this->channel = new Grpc\Channel('localhost:0',
             ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $this->assertNotNull($this->channel);
        $this->channel->close();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstructorWithNull()
    {
        $this->channel = new Grpc\Channel();
        $this->assertNull($this->channel);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstructorWith()
    {
        $this->channel = new Grpc\Channel('localhost:0', 'invalid');
        $this->assertNull($this->channel);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidCredentials()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['credentials' => new Grpc\Timeval(100)]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidOptionsArray()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['abc' => []]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidGetConnectivityStateWithArray()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $this->channel->getConnectivityState([]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWatchConnectivityState()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $this->channel->watchConnectivityState([]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWatchConnectivityState2()
    {
        $this->channel = new Grpc\Channel('localhost:0',
            ['credentials' => Grpc\ChannelCredentials::createInsecure()]);
        $this->channel->watchConnectivityState(1, 'hi');
    }


    public function assertConnecting($state) {
      $this->assertTrue($state == GRPC\CHANNEL_CONNECTING ||
                        $state == GRPC\CHANNEL_TRANSIENT_FAILURE);
    }

    public function waitUntilNotIdle($channel) {
        for ($i = 0; $i < 10; $i++) {
            $now = Grpc\Timeval::now();
            $deadline = $now->add(new Grpc\Timeval(1000));
            if ($channel->watchConnectivityState(GRPC\CHANNEL_IDLE,
                                                 $deadline)) {
                return true;
            }
        }
        $this->assertTrue(false);
    }

    public function testPersistentChannelSameHost()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        // the underlying grpc channel is the same by default
        // when connecting to the same host
        $this->channel2 = new Grpc\Channel('localhost:1', []);

        // both channels should be IDLE
        $state = $this->channel1->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        // both channels should now be in the CONNECTING state
        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelDifferentHost()
    {
        // two different underlying channels because different hostname
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:2', []);

        // both channels should be IDLE
        $state = $this->channel1->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        // channel1 should now be in the CONNECTING state
        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        // channel2 should still be in the IDLE state
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelSameArgs()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', ["abc" => "def"]);
        $this->channel2 = new Grpc\Channel('localhost:1', ["abc" => "def"]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelDifferentArgs()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1', ["abc" => "def"]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelSameChannelCredentials()
    {
        $creds1 = Grpc\ChannelCredentials::createSsl();
        $creds2 = Grpc\ChannelCredentials::createSsl();

        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds1]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds2]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelDifferentChannelCredentials()
    {
        $creds1 = Grpc\ChannelCredentials::createSsl();
        $creds2 = Grpc\ChannelCredentials::createSsl(
            file_get_contents(dirname(__FILE__).'/../data/ca.pem'));

        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds1]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds2]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelSameChannelCredentialsRootCerts()
    {
        $creds1 = Grpc\ChannelCredentials::createSsl(
            file_get_contents(dirname(__FILE__).'/../data/ca.pem'));
        $creds2 = Grpc\ChannelCredentials::createSsl(
            file_get_contents(dirname(__FILE__).'/../data/ca.pem'));

        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds1]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds2]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelDifferentSecureChannelCredentials()
    {
        $creds1 = Grpc\ChannelCredentials::createSsl();
        $creds2 = Grpc\ChannelCredentials::createInsecure();

        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds1]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds2]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testPersistentChannelSharedChannelClose()
    {
        // same underlying channel
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1', []);

        // close channel1
        $this->channel1->close();

        // channel2 is now in SHUTDOWN state
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_FATAL_FAILURE, $state);

        // calling it again will result in an exception because the
        // channel is already closed
        $state = $this->channel2->getConnectivityState();
    }

    public function testPersistentChannelCreateAfterClose()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', []);

        $this->channel1->close();

        $this->channel2 = new Grpc\Channel('localhost:1', []);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel2->close();
    }

    public function testPersistentChannelSharedMoreThanTwo()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1', []);
        $this->channel3 = new Grpc\Channel('localhost:1', []);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        // all 3 channels should be in CONNECTING state
        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel3->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
    }

    public function callbackFunc($context)
    {
        return [];
    }

    public function callbackFunc2($context)
    {
        return ["k1" => "v1"];
    }

    public function testPersistentChannelWithCallCredentials()
    {
        $creds = Grpc\ChannelCredentials::createSsl();
        $callCreds = Grpc\CallCredentials::createFromPlugin(
            [$this, 'callbackFunc']);
        $credsWithCallCreds = Grpc\ChannelCredentials::createComposite(
            $creds, $callCreds);

        // If a ChannelCredentials object is composed with a
        // CallCredentials object, the underlying grpc channel will
        // always be created new and NOT persisted.
        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" =>
                                            $credsWithCallCreds]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" =>
                                            $credsWithCallCreds]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelWithDifferentCallCredentials()
    {
        $callCreds1 = Grpc\CallCredentials::createFromPlugin(
            [$this, 'callbackFunc']);
        $callCreds2 = Grpc\CallCredentials::createFromPlugin(
            [$this, 'callbackFunc2']);

        $creds1 = Grpc\ChannelCredentials::createSsl();
        $creds2 = Grpc\ChannelCredentials::createComposite(
            $creds1, $callCreds1);
        $creds3 = Grpc\ChannelCredentials::createComposite(
            $creds1, $callCreds2);

        // Similar to the test above, anytime a ChannelCredentials
        // object is composed with a CallCredentials object, the
        // underlying grpc channel will always be separate and not
        // persisted
        $this->channel1 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds1]);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds2]);
        $this->channel3 = new Grpc\Channel('localhost:1',
                                           ["credentials" => $creds3]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);
        $state = $this->channel3->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
        $this->channel3->close();
    }

    public function testPersistentChannelForceNew()
    {
        $this->channel1 = new Grpc\Channel('localhost:1', []);
        // even though all the channel params are the same, channel2
        // has a new and different underlying channel
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["force_new" => true]);

        // try to connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    public function testPersistentChannelForceNewOldChannelIdle()
    {

        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["force_new" => true]);
        // channel3 shares with channel1
        $this->channel3 = new Grpc\Channel('localhost:1', []);

        // try to connect on channel2
        $state = $this->channel2->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel2);

        $state = $this->channel1->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);
        $state = $this->channel2->getConnectivityState();
        $this->assertConnecting($state);
        $state = $this->channel3->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        $this->channel1->close();
        $this->channel2->close();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testPersistentChannelForceNewOldChannelClose()
    {

        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["force_new" => true]);
        // channel3 shares with channel1
        $this->channel3 = new Grpc\Channel('localhost:1', []);

        $this->channel1->close();

        $state = $this->channel2->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        // channel3 already closed
        $state = $this->channel3->getConnectivityState();
    }

    public function testPersistentChannelForceNewNewChannelClose()
    {

        $this->channel1 = new Grpc\Channel('localhost:1', []);
        $this->channel2 = new Grpc\Channel('localhost:1',
                                           ["force_new" => true]);
        $this->channel3 = new Grpc\Channel('localhost:1', []);

        $this->channel2->close();

        $state = $this->channel1->getConnectivityState();
        $this->assertEquals(GRPC\CHANNEL_IDLE, $state);

        // can still connect on channel1
        $state = $this->channel1->getConnectivityState(true);
        $this->waitUntilNotIdle($this->channel1);

        $state = $this->channel1->getConnectivityState();
        $this->assertConnecting($state);

        $this->channel1->close();
    }
}
