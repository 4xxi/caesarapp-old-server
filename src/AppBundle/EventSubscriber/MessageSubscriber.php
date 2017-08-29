<?php

namespace AppBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Document\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class MessageSubscriber
 */
class MessageSubscriber implements EventSubscriberInterface
{
    /**
     * @var DocumentManager
     */
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
            KernelEvents::VIEW => [['storeEncryptedMessage', EventPriorities::PRE_WRITE]],
        ];
    }

    /**
     * Stores encrypted message into DB.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function storeEncryptedMessage(GetResponseForControllerResultEvent $event)
    {
        $message = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$message instanceof Message || Request::METHOD_POST !== $method) {
            return;
        }

        $this->dm->persist($message);
        $this->dm->flush();
    }
}
