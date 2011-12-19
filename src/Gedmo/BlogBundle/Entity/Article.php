<?php

namespace Gedmo\BlogBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gedmo\BlogBundle\Entity\Article
 *
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article
{
    const TYPE_EXTENSION = 1;

    const TYPE_BLOG = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=64, unique=true)
     */
    private $slug;


    /**
     * @Assert\NotBlank(message="Title must be set")
     * @ORM\Column(length=64)
     */
    private $title;

    /**
     * @Assert\NotBlank(message="Summary must be set")
     * @ORM\Column(type="text")
     */
    private $summary;

    /**
     * @Assert\NotBlank(message="Content cannot be empty")
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @Assert\NotBlank(message="Content cannot be empty")
     * @ORM\Column(length=256)
     */
    private $meta;

    /**
     * @Assert\NotBlank(message="Type must be set")
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="article")
     */
    private $comments;

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
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set summary
     *
     * @param text $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * Get summary
     *
     * @return text $summary
     */
    public function getSummary()
    {
        return $this->summary;
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
     * Get updated
     *
     * @return datetime $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set metaDescription
     *
     * @param string $keys
     */
    public function setMeta($metaDescription)
    {
        $this->meta = $metaDescription;
    }

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Add comments
     *
     * @param Application\BlogBundle\Entity\Comment $comments
     */
    public function addComments(Comment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection $comments
     */
    public function getComments()
    {
        return $this->comments;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}