<?php

namespace XalokBundle\Entity;

/**
 * Video
 */
class Video
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
    private $description;

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
    private $videoName;

    /**
     * @var string
     */
    private $thumbName;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $mediaId;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tag;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @return Video
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
     * @return Video
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
     * Set description
     *
     * @param string $description
     *
     * @return Video
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Video
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
     * @return Video
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
     * Set videoName
     *
     * @param string $videoName
     *
     * @return Video
     */
    public function setVideoName($videoName)
    {
        $this->videoName = $videoName;

        return $this;
    }

    /**
     * Get videoName
     *
     * @return string
     */
    public function getVideoName()
    {
        return $this->videoName;
    }

    /**
     * Set thumbName
     *
     * @param string $thumbName
     *
     * @return Video
     */
    public function setThumbName($thumbName)
    {
        $this->thumbName = $thumbName;

        return $this;
    }

    /**
     * Get thumbName
     *
     * @return string
     */
    public function getThumbName()
    {
        return $this->thumbName;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Video
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set mediaId
     *
     * @param string $mediaId
     *
     * @return Video
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * Get mediaId
     *
     * @return string
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * Add tag
     *
     * @param \XalokBundle\Entity\Tag $tag
     *
     * @return Video
     */
    public function addTag(\XalokBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \XalokBundle\Entity\Tag $tag
     */
    public function removeTag(\XalokBundle\Entity\Tag $tag)
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
}

