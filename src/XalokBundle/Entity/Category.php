<?php

namespace XalokBundle\Entity;

/**
 * Category
 */
class Category
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $active;

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
    private $picturename;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $articleTemplate;

    /**
     * @var integer
     */
    private $lft;

    /**
     * @var integer
     */
    private $rgt;

    /**
     * @var integer
     */
    private $root;

    /**
     * @var integer
     */
    private $lvl;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $type;

    /**
     * @var boolean
     */
    private $radar;

    /**
     * @var integer
     */
    private $portalId = '1';

    /**
     * @var \XalokBundle\Entity\Category
     */
    private $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $page;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->page = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Category
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
     * @return Category
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Category
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Category
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
     * @return Category
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
     * Set picturename
     *
     * @param string $picturename
     *
     * @return Category
     */
    public function setPicturename($picturename)
    {
        $this->picturename = $picturename;

        return $this;
    }

    /**
     * Get picturename
     *
     * @return string
     */
    public function getPicturename()
    {
        return $this->picturename;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return Category
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set articleTemplate
     *
     * @param string $articleTemplate
     *
     * @return Category
     */
    public function setArticleTemplate($articleTemplate)
    {
        $this->articleTemplate = $articleTemplate;

        return $this;
    }

    /**
     * Get articleTemplate
     *
     * @return string
     */
    public function getArticleTemplate()
    {
        return $this->articleTemplate;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return Category
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     *
     * @return Category
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param integer $root
     *
     * @return Category
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     *
     * @return Category
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Category
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
     * Set type
     *
     * @param string $type
     *
     * @return Category
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
     * Set radar
     *
     * @param boolean $radar
     *
     * @return Category
     */
    public function setRadar($radar)
    {
        $this->radar = $radar;

        return $this;
    }

    /**
     * Get radar
     *
     * @return boolean
     */
    public function getRadar()
    {
        return $this->radar;
    }

    /**
     * Set portalId
     *
     * @param integer $portalId
     *
     * @return Category
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
     * Set parent
     *
     * @param \XalokBundle\Entity\Category $parent
     *
     * @return Category
     */
    public function setParent(\XalokBundle\Entity\Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \XalokBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add page
     *
     * @param \XalokBundle\Entity\Page $page
     *
     * @return Category
     */
    public function addPage(\XalokBundle\Entity\Page $page)
    {
        $this->page[] = $page;

        return $this;
    }

    /**
     * Remove page
     *
     * @param \XalokBundle\Entity\Page $page
     */
    public function removePage(\XalokBundle\Entity\Page $page)
    {
        $this->page->removeElement($page);
    }

    /**
     * Get page
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPage()
    {
        return $this->page;
    }

    public function __toString(){
        $a = array(
            "id"    => $this->id,
            "title" => $this->title,
            "slug"  => $this->slug
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
}

