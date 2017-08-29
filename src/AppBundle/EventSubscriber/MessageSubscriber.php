<?php

namespace AppBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Document\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Routing\Router;

/**
 * Class MessageSubscriber
 * @package AppBundle\EventSubscriber
 */
class MessageSubscriber implements EventSubscriberInterface
{
    const GET_URI = '/api/messages';

    protected $dm;

    protected $validator;

    protected $router;

    /**
     * MessageSubscriber constructor.
     *
     * @param DocumentManager $documentManager
     * @param Router $router
     */
    public function __construct(DocumentManager $documentManager, Router $router)
    {
        $this->dm = $documentManager;
        $this->router = $router;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['storeEncryptedMessage', EventPriorities::PRE_WRITE]],
        ];
    }

    /**
     * Stores encrypted message into DB.
     *
     * @param GetResponseEvent $event
     */
    public function storeEncryptedMessage(GetResponseEvent $event)
    {
        $uri = $event->getRequest()->getRequestUri();
        $method = $event->getRequest()->getMethod();

        if (($uri !== self::GET_URI) || ($method !== Request::METHOD_POST)) {
            return;
        }

        $content = json_decode($event->getRequest()->getContent());
        $message = new Message();
        $message->setEncryptedMessage($content->encryptedMessage);
        $message->setQueriesLimit($content->queriesLimit);
        $message->setMinutesLimit($content->minutesLimit);
        $this->dm->persist($message);
        $this->dm->flush();
        echo $message->getId();

        exit();
    }
}
