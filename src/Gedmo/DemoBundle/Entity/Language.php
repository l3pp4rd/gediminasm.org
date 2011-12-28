<?php

namespace Gedmo\DemoBundle\Entity;

/**
 * @orm:Table(name="demo_languages")
 * @orm:Entity
 */
class Language
{
    /**
     * @var integer $id
     *
     * @orm:Column(type="integer")
     * @orm:Id
     * @orm:GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $title
     *
     * @validation:NotBlank()
     * @validation:MaxLength(16)
     * @gedmo:Sluggable
     * @orm:Column(length=16)
     */
    private $title;

    /**
     * @var string $language
     *
     * @gedmo:Slug(separator="_")
     * @orm:Column(length=16)
     */
    private $language;

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
     * Get language
     *
     * @return string $language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}