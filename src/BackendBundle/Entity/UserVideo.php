<?php

namespace BackendBundle\Entity;

/**
 * UserVideo
 */
class UserVideo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $youtubeVId;

    /**
     * @var string
     */
    private $vimeoVId;

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
     * Set youtubeVId
     *
     * @param string $youtubeVId
     *
     * @return UserVideo
     */
    public function setYoutubeVId($youtubeVId)
    {
        $this->youtubeVId = $youtubeVId;

        return $this;
    }

    /**
     * Get youtubeVId
     *
     * @return string
     */
    public function getYoutubeVId()
    {
        return $this->youtubeVId;
    }

    /**
     * Set vimeoVId
     *
     * @param string $vimeoVId
     *
     * @return UserVideo
     */
    public function setVimeoVId($vimeoVId)
    {
        $this->vimeoVId = $vimeoVId;

        return $this;
    }

    /**
     * Get vimeoVId
     *
     * @return string
     */
    public function getVimeoVId()
    {
        return $this->vimeoVId;
    }

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $publisher;


    /**
     * Set publisher
     *
     * @param \BackendBundle\Entity\WfUser $publisher
     *
     * @return UserVideo
     */
    public function setPublisher(\BackendBundle\Entity\WfUser $publisher = null)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @var \DateTime
     */
    private $createdAt;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return UserVideo
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
}
