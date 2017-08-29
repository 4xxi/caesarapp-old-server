<?php

namespace AppBundle\DataProvider;

use AppBundle\Document\Message;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MessageItemDataProvider
 * @package AppBundle\DataProvider
 */
final class MessageItemDataProvider implements ItemDataProviderInterface
{
    private $dm;

    /**
     * MessageItemDataProvider constructor.
     *
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    /**
     * Get an encrypted message from DB by ID.
     *
     * @param string      $resourceClass
     * @param int|string  $id
     * @param string|null $operationName
     * @param array       $context
     *
     * @return object
     *
     * @throws ResourceClassNotSupportedException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if (Message::class !== $resourceClass) {
            throw new ResourceClassNotSupportedException();
        }

        $message = $this->dm->getRepository('AppBundle:Message')->findOneBy(['id' => $id]);

        if (!$message) {
            throw new NotFoundHttpException();
        }

        if ($message->getQueriesLimit()) {
            $message->decrementQueriesLimit();
            $this->dm->flush($message);
        }

        if ($message->getQueriesLimit() === 0) {
            $this->dm->remove($message);
            $this->dm->flush();
        }

        dump($message);

        exit();
    }
}
