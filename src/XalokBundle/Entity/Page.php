<?php

namespace XalokBundle\Entity;

/**
 * Page
 */
class Page
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
     * @var \DateTime
     */
    private $nextPublishedAt;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $images;

    /**
     * @var array
     */
    private $videos;

    /**
     * @var array
     */
    private $audios;

    /**
     * @var array
     */
    private $javascripts;

    /**
     * @var array
     */
    private $styles;

    /**
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $modules;

    /**
     * @var array
     */
    private $seo;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $epigraph;

    /**
     * @var string
     */
    private $excerpt;

    /**
     * @var array
     */
    private $related;

    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $pageType;

    /**
     * @var string
     */
    private $firstTitle;

    /**
     * @var string
     */
    private $shortDescription;

    /**
     * @var array
     */
    private $content;

    /**
     * @var boolean
     */
    private $hasimages;

    /**
     * @var boolean
     */
    private $hasvideos;

    /**
     * @var boolean
     */
    private $hasaudios;

    /**
     * @var \DateTime
     */
    private $dateEdition;

    /**
     * @var string
     */
    private $paperCategory;

    /**
     * @var boolean
     */
    private $highlight;

    /**
     * @var integer
     */
    private $portalId = '1';

    /**
     * @var string
     */
    private $social;

    /**
     * @var string
     */
    private $killerExtra;

    /**
     * @var \XalokBundle\Entity\Tag
     */
    private $mainTag;

    /**
     * @var \XalokBundle\Entity\WfUser
     */
    private $publisher;

    /**
     * @var \XalokBundle\Entity\PageVersion
     */
    private $version;

    /**
     * @var \XalokBundle\Entity\WfUser
     */
    private $creator;

    /**
     * @var \XalokBundle\Entity\PageMetadata
     */
    private $metadata;

    /**
     * @var \XalokBundle\Entity\Image
     */
    private $mainImage;

    /**
     * @var \XalokBundle\Entity\PageVersion
     */
    private $nextVersion;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $author;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tag;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->author = new \Doctrine\Common\Collections\ArrayCollection();
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * Set title
     *
     * @param string $title
     *
     * @return Page
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
     * Set images
     *
     * @param array $images
     *
     * @return Page
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set videos
     *
     * @param array $videos
     *
     * @return Page
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Get videos
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set audios
     *
     * @param array $audios
     *
     * @return Page
     */
    public function setAudios($audios)
    {
        $this->audios = $audios;

        return $this;
    }

    /**
     * Get audios
     *
     * @return array
     */
    public function getAudios()
    {
        return $this->audios;
    }

    /**
     * Set javascripts
     *
     * @param array $javascripts
     *
     * @return Page
     */
    public function setJavascripts($javascripts)
    {
        $this->javascripts = $javascripts;

        return $this;
    }

    /**
     * Get javascripts
     *
     * @return array
     */
    public function getJavascripts()
    {
        return $this->javascripts;
    }

    /**
     * Set styles
     *
     * @param array $styles
     *
     * @return Page
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;

        return $this;
    }

    /**
     * Get styles
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Page
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
     * Set position
     *
     * @param integer $position
     *
     * @return Page
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return Page
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
     * Set settings
     *
     * @param array $settings
     *
     * @return Page
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set modules
     *
     * @param array $modules
     *
     * @return Page
     */
    public function setModules($modules)
    {
        $this->modules = $modules;

        return $this;
    }

    /**
     * Get modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Set seo
     *
     * @param array $seo
     *
     * @return Page
     */
    public function setSeo($seo)
    {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return array
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return Page
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set epigraph
     *
     * @param string $epigraph
     *
     * @return Page
     */
    public function setEpigraph($epigraph)
    {
        $this->epigraph = $epigraph;

        return $this;
    }

    /**
     * Get epigraph
     *
     * @return string
     */
    public function getEpigraph()
    {
        return $this->epigraph;
    }

    /**
     * Set excerpt
     *
     * @param string $excerpt
     *
     * @return Page
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * Get excerpt
     *
     * @return string
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * Set related
     *
     * @param array $related
     *
     * @return Page
     */
    public function setRelated($related)
    {
        $this->related = $related;

        return $this;
    }

    /**
     * Get related
     *
     * @return array
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Set sourceId
     *
     * @param string $sourceId
     *
     * @return Page
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Page
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
     * Set pageType
     *
     * @param string $pageType
     *
     * @return Page
     */
    public function setPageType($pageType)
    {
        $this->pageType = $pageType;

        return $this;
    }

    /**
     * Get pageType
     *
     * @return string
     */
    public function getPageType()
    {
        return $this->pageType;
    }

    /**
     * Set firstTitle
     *
     * @param string $firstTitle
     *
     * @return Page
     */
    public function setFirstTitle($firstTitle)
    {
        $this->firstTitle = $firstTitle;

        return $this;
    }

    /**
     * Get firstTitle
     *
     * @return string
     */
    public function getFirstTitle()
    {
        return $this->firstTitle;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return Page
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set content
     *
     * @param array $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set hasimages
     *
     * @param boolean $hasimages
     *
     * @return Page
     */
    public function setHasimages($hasimages)
    {
        $this->hasimages = $hasimages;

        return $this;
    }

    /**
     * Get hasimages
     *
     * @return boolean
     */
    public function getHasimages()
    {
        return $this->hasimages;
    }

    /**
     * Set hasvideos
     *
     * @param boolean $hasvideos
     *
     * @return Page
     */
    public function setHasvideos($hasvideos)
    {
        $this->hasvideos = $hasvideos;

        return $this;
    }

    /**
     * Get hasvideos
     *
     * @return boolean
     */
    public function getHasvideos()
    {
        return $this->hasvideos;
    }

    /**
     * Set hasaudios
     *
     * @param boolean $hasaudios
     *
     * @return Page
     */
    public function setHasaudios($hasaudios)
    {
        $this->hasaudios = $hasaudios;

        return $this;
    }

    /**
     * Get hasaudios
     *
     * @return boolean
     */
    public function getHasaudios()
    {
        return $this->hasaudios;
    }

    /**
     * Set dateEdition
     *
     * @param \DateTime $dateEdition
     *
     * @return Page
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set paperCategory
     *
     * @param string $paperCategory
     *
     * @return Page
     */
    public function setPaperCategory($paperCategory)
    {
        $this->paperCategory = $paperCategory;

        return $this;
    }

    /**
     * Get paperCategory
     *
     * @return string
     */
    public function getPaperCategory()
    {
        return $this->paperCategory;
    }

    /**
     * Set highlight
     *
     * @param boolean $highlight
     *
     * @return Page
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;

        return $this;
    }

    /**
     * Get highlight
     *
     * @return boolean
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * Set portalId
     *
     * @param integer $portalId
     *
     * @return Page
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
     * Set social
     *
     * @param string $social
     *
     * @return Page
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get social
     *
     * @return string
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set killerExtra
     *
     * @param string $killerExtra
     *
     * @return Page
     */
    public function setKillerExtra($killerExtra)
    {
        $this->killerExtra = $killerExtra;

        return $this;
    }

    /**
     * Get killerExtra
     *
     * @return string
     */
    public function getKillerExtra()
    {
        return $this->killerExtra;
    }

    /**
     * Set mainTag
     *
     * @param \XalokBundle\Entity\Tag $mainTag
     *
     * @return Page
     */
    public function setMainTag(\XalokBundle\Entity\Tag $mainTag = null)
    {
        $this->mainTag = $mainTag;

        return $this;
    }

    /**
     * Get mainTag
     *
     * @return \XalokBundle\Entity\Tag
     */
    public function getMainTag()
    {
        return $this->mainTag;
    }

    /**
     * Set publisher
     *
     * @param \XalokBundle\Entity\WfUser $publisher
     *
     * @return Page
     */
    public function setPublisher(\XalokBundle\Entity\WfUser $publisher = null)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return \XalokBundle\Entity\WfUser
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set version
     *
     * @param \XalokBundle\Entity\PageVersion $version
     *
     * @return Page
     */
    public function setVersion(\XalokBundle\Entity\PageVersion $version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return \XalokBundle\Entity\PageVersion
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set creator
     *
     * @param \XalokBundle\Entity\WfUser $creator
     *
     * @return Page
     */
    public function setCreator(\XalokBundle\Entity\WfUser $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \XalokBundle\Entity\WfUser
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set metadata
     *
     * @param \XalokBundle\Entity\PageMetadata $metadata
     *
     * @return Page
     */
    public function setMetadata(\XalokBundle\Entity\PageMetadata $metadata = null)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return \XalokBundle\Entity\PageMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set mainImage
     *
     * @param \XalokBundle\Entity\Image $mainImage
     *
     * @return Page
     */
    public function setMainImage(\XalokBundle\Entity\Image $mainImage = null)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * Get mainImage
     *
     * @return \XalokBundle\Entity\Image
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * Set nextVersion
     *
     * @param \XalokBundle\Entity\PageVersion $nextVersion
     *
     * @return Page
     */
    public function setNextVersion(\XalokBundle\Entity\PageVersion $nextVersion = null)
    {
        $this->nextVersion = $nextVersion;

        return $this;
    }

    /**
     * Get nextVersion
     *
     * @return \XalokBundle\Entity\PageVersion
     */
    public function getNextVersion()
    {
        return $this->nextVersion;
    }

    /**
     * Add author
     *
     * @param \XalokBundle\Entity\WfUser $author
     *
     * @return Page
     */
    public function addAuthor(\XalokBundle\Entity\WfUser $author)
    {
        $this->author[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param \XalokBundle\Entity\WfUser $author
     */
    public function removeAuthor(\XalokBundle\Entity\WfUser $author)
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

    /**
     * Add category
     *
     * @param \XalokBundle\Entity\Category $category
     *
     * @return Page
     */
    public function addCategory(\XalokBundle\Entity\Category $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \XalokBundle\Entity\Category $category
     */
    public function removeCategory(\XalokBundle\Entity\Category $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add tag
     *
     * @param \XalokBundle\Entity\Tag $tag
     *
     * @return Page
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

    /**
     * @var \XalokBundle\Entity\Category
     */
    private $category_id;


    /**
     * Set categoryId
     *
     * @param \XalokBundle\Entity\Category $categoryId
     *
     * @return Page
     */
    public function setCategoryId(\XalokBundle\Entity\Category $categoryId = null)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return \XalokBundle\Entity\Category
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }
}
