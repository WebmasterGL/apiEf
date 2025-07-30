<?php

namespace BackendBundle\Entity;

/**
 * Blog
 */
class Blog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $imageName;

    /**
     * @var \BackendBundle\Entity\Category
     */
    private $category;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->author = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Blog
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Blog
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Blog
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Blog
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return Blog
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }


    /**
     * Set category
     *
     * @param \BackendBundle\Entity\Category $category
     *
     * @return Blog
     */
    public function setCategory(\BackendBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \BackendBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @var string
     */
    private $identidad;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $metadatos;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $author;

    /**
     * Set identidad
     *
     * @param string $identidad
     *
     * @return Blog
     */
    public function setIdentidad($identidad)
    {
        $this->identidad = $identidad;

        return $this;
    }

    /**
     * Get identidad
     *
     * @return string
     */
    public function getIdentidad()
    {
        return $this->identidad;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Blog
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set metadatos
     *
     * @param string $metadatos
     *
     * @return Blog
     */
    public function setMetadatos($metadatos)
    {
        $this->metadatos = $metadatos;

        return $this;
    }

    /**
     * Get metadatos
     *
     * @return string
     */
    public function getMetadatos()
    {
        return $this->metadatos;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Blog
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add author
     *
     * @param \BackendBundle\Entity\Author $author
     *
     * @return Blog
     */
    public function addAuthor(\BackendBundle\Entity\Author $author)
    {
        $this->author[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param \BackendBundle\Entity\Author $author
     */
    public function removeAuthor(\BackendBundle\Entity\Author $author)
    {
        $this->author->removeElement($author);
    }

    /**
     * Get author
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthor()
    {
        return $this->author;
    }

    public function __toString() {
        $a = array(
            "id"    => $this->id,
            "title" => $this->title,
            "slug"  => $this->slug
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
}
