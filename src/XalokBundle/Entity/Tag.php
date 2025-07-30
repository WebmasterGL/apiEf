<?php

namespace XalokBundle\Entity;

/**
 * Tag
 */
class Tag
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $title;

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
    private $type;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $audio;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $file;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $image;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $page;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $video;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->audio = new \Doctrine\Common\Collections\ArrayCollection();
        $this->file = new \Doctrine\Common\Collections\ArrayCollection();
        $this->image = new \Doctrine\Common\Collections\ArrayCollection();
        $this->page = new \Doctrine\Common\Collections\ArrayCollection();
        $this->video = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Tag
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
     * @return Tag
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Tag
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
     * @return Tag
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
     * Set type
     *
     * @param string $type
     *
     * @return Tag
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Tag
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add audio
     *
     * @param \XalokBundle\Entity\Audio $audio
     *
     * @return Tag
     */
    public function addAudio(\XalokBundle\Entity\Audio $audio)
    {
        $this->audio[] = $audio;

        return $this;
    }

    /**
     * Remove audio
     *
     * @param \XalokBundle\Entity\Audio $audio
     */
    public function removeAudio(\XalokBundle\Entity\Audio $audio)
    {
        $this->audio->removeElement($audio);
    }

    /**
     * Get audio
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAudio()
    {
        return $this->audio;
    }

    /**
     * Add file
     *
     * @param \XalokBundle\Entity\File $file
     *
     * @return Tag
     */
    public function addFile(\XalokBundle\Entity\File $file)
    {
        $this->file[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \XalokBundle\Entity\File $file
     */
    public function removeFile(\XalokBundle\Entity\File $file)
    {
        $this->file->removeElement($file);
    }

    /**
     * Get file
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Add image
     *
     * @param \XalokBundle\Entity\Image $image
     *
     * @return Tag
     */
    public function addImage(\XalokBundle\Entity\Image $image)
    {
        $this->image[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \XalokBundle\Entity\Image $image
     */
    public function removeImage(\XalokBundle\Entity\Image $image)
    {
        $this->image->removeElement($image);
    }

    /**
     * Get image
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add page
     *
     * @param \XalokBundle\Entity\Page $page
     *
     * @return Tag
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

    /**
     * Add video
     *
     * @param \XalokBundle\Entity\Video $video
     *
     * @return Tag
     */
    public function addVideo(\XalokBundle\Entity\Video $video)
    {
        $this->video[] = $video;

        return $this;
    }

    /**
     * Remove video
     *
     * @param \XalokBundle\Entity\Video $video
     */
    public function removeVideo(\XalokBundle\Entity\Video $video)
    {
        $this->video->removeElement($video);
    }

    /**
     * Get video
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideo()
    {
        return $this->video;
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

