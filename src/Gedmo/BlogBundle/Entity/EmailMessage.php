<?php

namespace Gedmo\BlogBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="email_messages")
 * @ORM\Entity
 */
class EmailMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Body cannot be empty")
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $error;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="change", field="status", value="sent")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sent;

    /**
     * Senders name
     *
     * @Assert\NotBlank(message="Sender name must be set")
     * @ORM\Column(length=64)
     */
    private $sender;

    /**
     * Sender's email address
     *
     * @Assert\NotBlank(message="Email address must be entered")
     * @Assert\Email(message="Email address is invalid")
     * @ORM\Column(length=255)
     */
    private $email;

    /**
     * Status
     *
     * @ORM\Column(length=8)
     */
    private $status;

    /**
     * Retries
     *
     * @ORM\Column(type="integer")
     */
    private $retry = 0;

    /**
     * Subject
     *
     * @Assert\NotBlank(message="Subject must be specified")
     * @ORM\Column(length=64)
     */
    private $subject;

    /**
     * Target email address
     *
     * @Assert\NotBlank(message="Email address must be entered")
     * @Assert\Email(message="Email address is invalid")
     * @ORM\Column(length=255)
     */
    private $target;

    public function getId()
    {
        return $this->id;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function incRetry()
    {
        $this->retry++;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getSent()
    {
        return $this->sent;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}