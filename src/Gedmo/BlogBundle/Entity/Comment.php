<?php

namespace Gedmo\BlogBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gedmo\BlogBundle\Entity\Comment
 *
 * @ORM\Table(name="comments")
 * @ORM\Entity
 */
class Comment
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string $author
     *
     * @ORM\Column(length=128, nullable=true)
     */
    private $author;

    /**
     * @var string $subject
     *
     * @Assert\NotBlank(message="Subject must be set")
     * @ORM\Column(length=128)
     */
    private $subject;

    /**
     * @var text $content
     *
     * @Assert\NotBlank(message="Body cannot be left blank")
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var Application\BlogBundle\Entity\Article
     *
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="comments")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set author
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return string $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text $content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get created
     *
     * @return datetime $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Set article
     *
     * @param Entities\Article $article
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Get article
     *
     * @return Entities\Article $article
     */
    public function getArticle()
    {
        return $this->article;
    }
}