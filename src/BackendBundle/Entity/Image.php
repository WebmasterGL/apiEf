<?php

namespace BackendBundle\Entity;

/**
 * Image
 */
class Image
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
    private $slug;

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
    private $sourceId;

    /**
     * @var string
     */
    private $imageName;

    /**
     * @var string
     */
    private $credito;

    /**
     * @var integer
     */
    private $portalId = '1';

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $gallery;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tag;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->gallery = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Image
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
     * @return Image
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Image
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
     * Set title
     *
     * @param string $title
     *
     * @return Image
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
     * @return Image
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
     * Set sourceId
     *
     * @param string $sourceId
     *
     * @return Image
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * Get sourceId
     *
     * @return string
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return Image
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
     * Set credito
     *
     * @param string $credito
     *
     * @return Image
     */
    public function setCredito($credito)
    {
        $this->credito = $credito;

        return $this;
    }

    /**
     * Get credito
     *
     * @return string
     */
    public function getCredito()
    {
        return $this->credito;
    }

    /**
     * Set portalId
     *
     * @param integer $portalId
     *
     * @return Image
     */
    public function setPortalId($portalId)
    {
        $this->portalId = $portalId;

        return $this;
    }

    /**
     * Get portalId
     *
     * @return integer
     */
    public function getPortalId()
    {
        return $this->portalId;
    }

    /**
     * Add gallery
     *
     * @param \BackendBundle\Entity\Gallery $gallery
     *
     * @return Image
     */
    public function addGallery(\BackendBundle\Entity\Gallery $gallery)
    {
        $this->gallery[] = $gallery;

        return $this;
    }

    /**
     * Remove gallery
     *
     * @param \BackendBundle\Entity\Gallery $gallery
     */
    public function removeGallery(\BackendBundle\Entity\Gallery $gallery)
    {
        $this->gallery->removeElement($gallery);
    }

    /**
     * Get gallery
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Add tag
     *
     * @param \BackendBundle\Entity\Tag $tag
     *
     * @return Image
     */
    public function addTag(\BackendBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \BackendBundle\Entity\Tag $tag
     */
    public function removeTag(\BackendBundle\Entity\Tag $tag)
    {
        $this->tag->removeElement($tag);
    }

    /**
     * Get tag
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }
    /**
     * @var string
     */
    private $footnote;

    /**
     * @var string
     */
    private $author;

    /**
     * @var \BackendBundle\Entity\Author
     */
    private $sourcecat;


    /**
     * Set footnote
     *
     * @param string $footnote
     *
     * @return Image
     */
    public function setFootnote($footnote)
    {
        $this->footnote = $footnote;

        return $this;
    }

    /**
     * Get footnote
     *
     * @return string
     */
    public function getFootnote()
    {
        return $this->footnote;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Image
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set sourcecat
     *
     * @param \BackendBundle\Entity\Author $sourcecat
     *
     * @return Image
     */
    public function setSourcecat(\BackendBundle\Entity\Author $sourcecat = null)
    {
        $this->sourcecat = $sourcecat;

        return $this;
    }

    /**
     * Get sourcecat
     *
     * @return \BackendBundle\Entity\Author
     */
    public function getSourcecat()
    {
        return $this->sourcecat;
    }
    /**
     * @var string
     */
    private $imagePath;


    /**
     * Set imagePath
     *
     * @param string $imagePath
     *
     * @return Image
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function __toString()
    {
        return $this->imagePath;
    }
    /**
     * @var string
     */
    private $type;


    /**
     * Set type
     *
     * @param string $type
     *
     * @return Image
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var string
     */
    private $versiones;


    /**
     * Set versiones
     *
     * @param string $versiones
     *
     * @return Image
     */
    public function setVersiones($versiones)
    {
        $this->versiones = $versiones;

        return $this;
    }

    /**
     * Get versiones
     *
     * @return string
     */
    public function getVersiones()
    {
        return $this->versiones;
    }
}
