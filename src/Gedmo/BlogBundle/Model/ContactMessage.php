<?php

namespace Gedmo\BlogBundle\Model;

class ContactMessage
{
    /**
     * Contact message body
     *
     * @validation:NotBlank()
     * @var string
     */
    private $message;

    /**
     * Senders name
     *
     * @validation:NotBlank()
     * @var string
     */
    private $sender;

    /**
     * Sender's email address
     *
     * @validation:NotBlank()
     * @validation:Email()
     * @var string
     */
    private $email;

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set sender
     *
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}