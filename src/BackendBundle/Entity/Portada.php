<?php

namespace BackendBundle\Entity;

/**
 * Portada
 */
class Portada
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $observaciones;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @var \DateTime
     */
    private $nextPublishedAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Portada
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Portada
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     *
     * @return Portada
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Portada
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
     * @return Portada
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
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return Portada
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set nextPublishedAt
     *
     * @param \DateTime $nextPublishedAt
     *
     * @return Portada
     */
    public function setNextPublishedAt($nextPublishedAt)
    {
        $this->nextPublishedAt = $nextPublishedAt;

        return $this;
    }

    /**
     * Get nextPublishedAt
     *
     * @return \DateTime
     */
    public function getNextPublishedAt()
    {
        return $this->nextPublishedAt;
    }

    /**
     * @var \BackendBundle\Entity\Image
     */
    private $image;


    /**
     * Set image
     *
     * @param \BackendBundle\Entity\Image $image
     *
     * @return Portada
     */
    public function setImage(\BackendBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $createdBy;


    /**
     * Set createdBy
     *
     * @param \BackendBundle\Entity\WfUser $createdBy
     *
     * @return Portada
     */
    public function setCreatedBy(\BackendBundle\Entity\WfUser $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @var \BackendBundle\Entity\Category
     */
    private $category;


    /**
     * Set category
     *
     * @param \BackendBundle\Entity\Category $category
     *
     * @return Portada
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
     * @var \BackendBundle\Entity\WfUser
     */
    private $updatedBy;


    /**
     * Set updatedBy
     *
     * @param \BackendBundle\Entity\WfUser $updatedBy
     *
     * @return Portada
     */
    public function setUpdatedBy(\BackendBundle\Entity\WfUser $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function __toString()
    {
        $a = array(
            "id"      => $this->id,
            "title"   => $this->name,
            "status"  => $this->status,
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }

    /**
 * @var string
 */
    private $code;

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PortadaFolds
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $editingById;


    /**
     * Set editingBy
     *
     * @param \BackendBundle\Entity\WfUser $editingBy
     *
     * @return Portada
     */
    public function setEditingById(\BackendBundle\Entity\WfUser $editingById = null)
    {
        $this->editingById = $editingById;

        return $this;
    }

    /**
     * Get editingBy
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getEditingById()
    {
        return $this->editingById;
    }
}
