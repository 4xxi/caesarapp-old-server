<?php
/**
 * Created by PhpStorm.
 * User: aibragimov
 * Date: 25.08.17
 * Time: 16:25.
 */

namespace AppBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Document\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class MessageSubscriber.
 */
class MessageSubscriber implements EventSubscriberInterface
{
    const GET_URI = '/api/messages';

    protected $dm;

    /**
     * MessageSubscriber constructor.
     *
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['storeEncryptedMessage', EventPriorities::PRE_VALIDATE]],
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
        $message->setExpires(date('d-m-Y H:i:s', $content->expires));
        $message->setQueriesLimit($content->queriesLimit);
        $this->dm->persist($message);
        $this->dm->flush();

        exit();
    }
}
