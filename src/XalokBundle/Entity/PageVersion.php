<?php

namespace XalokBundle\Entity;

/**
 * PageVersion
 */
class PageVersion
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
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @var integer
     */
    private $versionNo;

    /**
     * @var string
     */
    private $pageClass;

    /**
     * @var string
     */
    private $pageData;

    /**
     * @var \XalokBundle\Entity\Page
     */
    private $page;


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
     * @return PageVersion
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
     * @return PageVersion
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
     * @return PageVersion
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
     * Set versionNo
     *
     * @param integer $versionNo
     *
     * @return PageVersion
     */
    public function setVersionNo($versionNo)
    {
        $this->versionNo = $versionNo;

        return $this;
    }

    /**
     * Get versionNo
     *
     * @return integer
     */
    public function getVersionNo()
    {
        return $this->versionNo;
    }

    /**
     * Set pageClass
     *
     * @param string $pageClass
     *
     * @return PageVersion
     */
    public function setPageClass($pageClass)
    {
        $this->pageClass = $pageClass;

        return $this;
    }

    /**
     * Get pageClass
     *
     * @return string
     */
    public function getPageClass()
    {
        return $this->pageClass;
    }

    /**
     * Set pageData
     *
     * @param string $pageData
     *
     * @return PageVersion
     */
    public function setPageData($pageData)
    {
        $this->pageData = $pageData;

        return $this;
    }

    /**
     * Get pageData
     *
     * @return string
     */
    public function getPageData()
    {
        return $this->pageData;
    }

    /**
     * Set page
     *
     * @param \XalokBundle\Entity\Page $page
     *
     * @return PageVersion
     */
    public function setPage(\XalokBundle\Entity\Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \XalokBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
}

