<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(collection="message")
 */
class Message
{
    /**
     * @var integer
     *
     * @MongoDB\Id(strategy="UUID", type="string")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Groups({"read", "write"})
     */
    private $encryptedMessage;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(name="expires", expireAfterSeconds="0")
     */
    private $expires;

    /**
     * @var integer
     *
     * @MongoDB\Field(type="int")
     * @Assert\NotBlank()
     * @Assert\Type("int")
     * @Groups({"write"})
     */
    private $minutesLimit;

    /**
     * @var integer
     *
     * @MongoDB\Field(type="int")
     * @Assert\Type("int")
     * @Groups({"write"})
     */
    private $queriesLimit;

    /**
     * Get id.
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set encryptedMessage.
     *
     * @param string $encryptedMessage
     *
     * @return $this
     */
    public function setEncryptedMessage($encryptedMessage)
    {
        $this->encryptedMessage = $encryptedMessage;

        return $this;
    }

    /**
     * Get encryptedMessage.
     *
     * @return string $encryptedMessage
     */
    public function getEncryptedMessage()
    {
        return $this->encryptedMessage;
    }

    /**
     * Set expires.
     *
     * @param \DateTime $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires.
     *
     * @return \DateTime $expires
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set queriesLimit.
     *
     * @param int $queriesLimit
     *
     * @return $this
     */
    public function setQueriesLimit($queriesLimit)
    {
        if (!$queriesLimit) {
            $queriesLimit = null;
        }

        $this->queriesLimit = $queriesLimit;

        return $this;
    }

    /**
     * Get queriesLimit.
     *
     * @return int $queriesLimit
     */
    public function getQueriesLimit()
    {
        return $this->queriesLimit;
    }

    /**
     * Set minutesLimit
     *
     * @param int $minutesLimit
     * @return $this
     */
    public function setMinutesLimit($minutesLimit)
    {
        $this->minutesLimit = $minutesLimit;
        $this->setExpires(strtotime("now +{$minutesLimit} minutes"));

        return $this;
    }

    /**
     * Get minutesLimit
     *
     * @return int $minutesLimit
     */
    public function getMinutesLimit()
    {
        return $this->minutesLimit;
    }

    /**
     * Decrements queriesLimit field
     *
     * @return $this
     */
    public function decrementQueriesLimit()
    {
        $this->queriesLimit--;

        return $this;
    }
}
