<?php

namespace BackendBundle\Entity;

/**
 * PortadaFolds
 */
class PortadaFolds
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $idportada;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;


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
     * Set idportada
     *
     * @param integer $idportada
     *
     * @return PortadaFolds
     */
    public function setIdportada($idportada)
    {
        $this->idportada = $idportada;

        return $this;
    }

    /**
     * Get idportada
     *
     * @return int
     */
    public function getIdportada()
    {
        return $this->idportada;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return PortadaFolds
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
     * Set content
     *
     * @param string $content
     *
     * @return PortadaFolds
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PortadaFolds
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
     * @return PortadaFolds
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
     * @var integer
     */
    private $orden;


    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return PortadaFolds
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }
    /**
     * @var \BackendBundle\Entity\Folds
     */
    private $idfold;


    /**
     * Set idfold
     *
     * @param \BackendBundle\Entity\Folds $idfold
     *
     * @return PortadaFolds
     */
    public function setIdfold(\BackendBundle\Entity\Folds $idfold = null)
    {
        $this->idfold = $idfold;

        return $this;
    }

    /**
     * Get idfold
     *
     * @return \BackendBundle\Entity\Folds
     */
    public function getIdfold()
    {
        return $this->idfold;
    }
    /**
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @var \DateTime
     */
    private $nextPublishedAt;

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $updatedBy;


    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return PortadaFolds
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
     * @return PortadaFolds
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
     * Set updatedBy
     *
     * @param \BackendBundle\Entity\WfUser $updatedBy
     *
     * @return PortadaFolds
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
    /**
     * @var string
     */
    private $code;
    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $editingBy;

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
     * Set editingBy
     *
     * @param \BackendBundle\Entity\WfUser $editingBy
     *
     * @return PortadaFolds
     */
    public function setEditingBy(\BackendBundle\Entity\WfUser $editingBy = null)
    {
        $this->editingBy = $editingBy;

        return $this;
    }

    /**
     * Get editingBy
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getEditingBy()
    {
        return $this->editingBy;
    }

    private $visible;

    public function getVisible()
    {
        return $this->visible;
    }

    public function setVisible( $visible )
    {
        $this->visible = $visible;

        return $this->visible;
    }
    /**
     * @var integer
     */
    private $cloneId;


    /**
     * Set cloneId
     *
     * @param integer $cloneId
     *
     * @return PortadaFolds
     */
    public function setCloneId($cloneId)
    {
        $this->cloneId = $cloneId;

        return $this;
    }

    /**
     * Get cloneId
     *
     * @return integer
     */
    public function getCloneId()
    {
        return $this->cloneId;
    }
}
