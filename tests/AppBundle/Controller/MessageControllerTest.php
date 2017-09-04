<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Document\Message;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    const ADD_ENCRYPTED_MESSAGE_URL = '/api/messages';

    const HEADERS = [
        'CONTENT_TYPE' => 'application/ld+json',
        'ACCEPT' => 'application/ld+json',
        'HTTP_ACCEPT' => 'application/ld+json'
    ];

    /** @var  DocumentManager */
    protected $dm;

    /**
     * Set up initial preferences
     */
    public function setUp()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    /**
     * Tests getting encrypted message
     */
    public function testGetEncryptedMessage()
    {
        $message = new Message();
        $message->setExpires(strtotime("now + 30 minutes"));
        $message->setQueriesLimit('30');
        $message->setEncryptedMessage("Some encrypted Message");
        $this->dm->persist($message);
        $this->dm->flush();
        $client = static::CreateClient();
        $client->request('GET', "/api/messages/{$message->getId()}", [], [], self::HEADERS);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($message->getEncryptedMessage(),$response->encryptedMessage);
    }

    /**
     * Tests queries limit for getting encrypted message
     */
    public function testQueriesLimit()
    {
        $message = new Message();
        $message->setExpires(strtotime("now + 30 minutes"));
        $message->setQueriesLimit('5');
        $message->setEncryptedMessage("Some encrypted Message");
        $this->dm->persist($message);
        $this->dm->flush();
        $client = static::CreateClient();
        for ($queryCounter = 1; $queryCounter <= 6; $queryCounter++) {
            $client->request('GET', "/api/messages/{$message->getId()}", [], [], self::HEADERS);
            if ($queryCounter == 6) {
                $this->assertEquals(404, $client->getResponse()->getStatusCode());
            } else {
                $response = json_decode($client->getResponse()->getContent());
                $this->assertEquals($message->getEncryptedMessage(), $response->encryptedMessage);
            }
        }
    }

    /**
     * Tests minutes limit for getting encrypted message
     */
    public function testMinutesLimit()
    {
        $message = new Message();
        $message->setExpires(strtotime("now + 1 minutes"));
        $message->setQueriesLimit('30');
        $message->setEncryptedMessage("Some encrypted Message");
        $this->dm->persist($message);
        $this->dm->flush();
        sleep(120); //By Default, the TTLMonitor thread runs once in every 60 seconds. That's why we need 1 min more.
        $client = static::CreateClient();
        $client->request('GET', "/api/messages/{$message->getId()}", [], [], self::HEADERS);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests storing valid encrypted message
     */
    public function testStoreEncryptedMessage()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test message',
            'minutesLimit' => 10,
            'queriesLimit' => 5,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests storing encrypted message without "encryptedMessage" field.
     */
    public function testStoreEncryptedMessageWithoutEncryptedMessage()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'minutesLimit' => 10,
            'queriesLimit' => 5,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals('encryptedMessage: This value should not be blank.', $response['hydra:description']);
    }

    /**
     * Tests storing encrypted message without "minutesLimit" field.
     */
    public function testStoreEncryptedMessageWithoutMinutesLimit()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test Message',
            'queriesLimit' => 20,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals('minutesLimit: This value should not be blank.', $response['hydra:description']);
    }

    /**
     * Tests storing encrypted message without "queriesLimit" field.
     */
    public function testStoreEncryptedMessageWithoutQueriesLimit()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test Message',
            'minutesLimit' => 5,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests storing encrypted message with wrong "encryptedMessage" field type.
     */
    public function testStoreEncryptedMessageWithWrongTypeEncryptedMessage()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 1,
            'minutesLimit' => 5,
            'queriesLimit' => 20,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals(
            'The type of the "encryptedMessage" attribute must be "string", "integer" given.',
            $response['hydra:description']
        );
    }

    /**
     * Tests storing encrypted message with wrong "minutesLimit" field type.
     */
    public function testStoreEncryptedMessageWithWrongTypeMinutesLimit()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test Message',
            'minutesLimit' => 'two',
            'queriesLimit' => 20,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals(
            'The type of the "minutesLimit" attribute must be "int", "string" given.',
            $response['hydra:description']
        );
    }

    /**
     * Tests storing encrypted message with "minutesLimit" field value = 0.5.
     */
    public function testStoreEncryptedMessageWithTypeMinutesLimitFloatValue()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test Message',
            'minutesLimit' => 0.5,
            'queriesLimit' => 20,
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals(
            'The type of the "minutesLimit" attribute must be "int", "double" given.',
            $response['hydra:description']
        );
    }

    /**
     * Tests storing encrypted message with wrong "queriesLimit" field type.
     */
    public function testStoreEncryptedMessageWithWrongTypeQueriesLimit()
    {
        $client = static::CreateClient();
        $content = json_encode([
            'encryptedMessage' => 'Test Message',
            'minutesLimit' => 5,
            'queriesLimit' => 'two',
        ]);
        $client->request('POST', self::ADD_ENCRYPTED_MESSAGE_URL, [], [], self::HEADERS, $content);
        $response = get_object_vars(json_decode($client->getResponse()->getContent()));
        $this->assertEquals(
            'The type of the "queriesLimit" attribute must be "int", "string" given.',
            $response['hydra:description']
        );
    }
}