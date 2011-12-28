<?php
namespace Gedmo\DemoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @gedmo:Loggable
 * @gedmo:Tree(type="nested")
 * @orm:Table(name="demo_categories")
 * @orm:Entity(repositoryClass="Gedmo\DemoBundle\Entity\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @orm:Column(type="integer")
     * @orm:Id
     * @orm:GeneratedValue
     */
    private $id;

    /**
     * @validation:NotBlank()
     * @validation:MaxLength(64)
     * @gedmo:Translatable
     * @gedmo:Sluggable
     * @orm:Column(length=64)
     */
    private $title;

    /**
     * @gedmo:TreeLeft
     * @orm:Column(type="integer")
     */
    private $lft;

    /**
     * @gedmo:TreeRight
     * @orm:Column(type="integer")
     */
    private $rgt;

    /**
     * @gedmo:TreeParent
     * @orm:ManyToOne(targetEntity="Category", inversedBy="children")
     * @orm:JoinColumns({
     *   @orm:JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $parent;

    /**
     * @gedmo:TreeRoot
     * @orm:Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @gedmo:TreeLevel
     * @orm:Column(name="lvl", type="integer")
     */
    private $level;

    /**
     * @orm:OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * @gedmo:Translatable
     * @orm:Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var datetime $created
     *
     * @gedmo:Timestampable(on="create")
     * @orm:Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     *
     * @gedmo:Timestampable(on="update")
     * @orm:Column(type="datetime")
     */
    private $updated;

    /**
     * Used locale to override Translation listener`s locale
     * @gedmo:Locale
     */
    private $locale;

    /**
     * @gedmo:Translatable
     * @gedmo:Slug
     * @orm:Column(length=64, unique=true)
     */
    private $slug;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = strip_tags($description);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getLeft()
    {
        return $this->lft;
    }

    public function getRight()
    {
        return $this->rgt;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
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
     * Get updated
     *
     * @return datetime $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}