<?php
/**
 * Created by PhpStorm.
 * User: aibragimov
 * Date: 25.08.17
 * Time: 15:51.
 */

namespace AppBundle\DataProvider;

use AppBundle\Document\Message;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class MessageItemDataProvider.
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

        return $message;
    }
}
