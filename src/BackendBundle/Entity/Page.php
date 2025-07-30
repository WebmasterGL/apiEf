<?php

namespace BackendBundle\Entity;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
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
     * @var \BackendBundle\Entity\Tag
     */
    private $mainTag;

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $publisher;

    /**
     * @var \BackendBundle\Entity\PageVersion
     */
    private $version;

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $creator;

    /**
     * @var \BackendBundle\Entity\PageMetadata
     */
    private $metadata;

    /**
     * @var \BackendBundle\Entity\Image
     */
    private $mainImage;

    /**
     * @var \BackendBundle\Entity\PageVersion
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
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);

        $jsonModules = $serializer->serialize( $this->modules, 'json');


        return $jsonModules;

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
        return $this->content ;
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
     * @param \BackendBundle\Entity\Tag $mainTag
     *
     * @return Page
     */
    public function setMainTag(\BackendBundle\Entity\Tag $mainTag = null)
    {
        $this->mainTag = $mainTag;

        return $this;
    }

    /**
     * Get mainTag
     *
     * @return \BackendBundle\Entity\Tag
     */
    public function getMainTag()
    {
        return $this->mainTag;
    }

    /**
     * Set publisher
     *
     * @param \BackendBundle\Entity\WfUser $publisher
     *
     * @return Page
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
     * Set version
     *
     * @param \BackendBundle\Entity\PageVersion $version
     *
     * @return Page
     */
    public function setVersion(\BackendBundle\Entity\PageVersion $version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return \BackendBundle\Entity\PageVersion
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set creator
     *
     * @param \BackendBundle\Entity\WfUser $creator
     *
     * @return Page
     */
    public function setCreator(\BackendBundle\Entity\WfUser $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \BackendBundle\Entity\WfUser
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set metadata
     *
     * @param \BackendBundle\Entity\PageMetadata $metadata
     *
     * @return Page
     */
    public function setMetadata(\BackendBundle\Entity\PageMetadata $metadata = null)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return \BackendBundle\Entity\PageMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set mainImage
     *
     * @param \BackendBundle\Entity\Image $mainImage
     *
     * @return Page
     */
    public function setMainImage(\BackendBundle\Entity\Image $mainImage = null)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * Get mainImage
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * Set nextVersion
     *
     * @param \BackendBundle\Entity\PageVersion $nextVersion
     *
     * @return Page
     */
    public function setNextVersion(\BackendBundle\Entity\PageVersion $nextVersion = null)
    {
        $this->nextVersion = $nextVersion;

        return $this;
    }

    /**
     * Get nextVersion
     *
     * @return \BackendBundle\Entity\PageVersion
     */
    public function getNextVersion()
    {
        return $this->nextVersion;
    }

    /**
     * Add author
     *
     * @param \BackendBundle\Entity\Author $author
     *
     * @return Page
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

    /**
     * Add category
     *
     * @param \BackendBundle\Entity\Category $category
     *
     * @return Page
     */
    public function addCategory(\BackendBundle\Entity\Category $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \BackendBundle\Entity\Category $category
     */
    public function removeCategory(\BackendBundle\Entity\Category $category)
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
     * Add
     *
     *
     * @param \BackendBundle\Entity\Tag $tag
     *
     * @return Page
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
    private $html;


    /**
     * Set html
     *
     * @param string $html
     *
     * @return Page
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @var string
     */
    private $html_serialize;


    /**
     * Set htmlSerialize
     *
     * @param string $htmlSerialize
     *
     * @return Page
     */
    public function setHtmlSerialize($htmlSerialize)
    {
        $this->html_serialize = $htmlSerialize;

        return $this;
    }

    /**
     * Get htmlSerialize
     *
     * @return string
     */
    public function getHtmlSerialize()
    {
        $a = count( $this->html_serialize ) > 0 ? json_encode( $this->html_serialize ) : null;

        return $a;
    }

    /**
     * @var boolean
     */
    private $newslatter;

    /**
     * @var string
     */
    private $bullets;

    /**
     * @var string
     */
    private $place;

    /**
     * @var boolean
     */
    private $mostViewed;

    /**
     * @var string
     */
    private $rss;

    /**
     * Set newslatter
     *
     * @param boolean $newslatter
     *
     * @return Page
     */
    public function setNewslatter($newslatter)
    {
        $this->newslatter = $newslatter;

        return $this;
    }

    /**
     * Get newslatter
     *
     * @return boolean
     */
    public function getNewslatter()
    {
        return $this->newslatter;
    }

    /**
     * Set bullets
     *
     * @param string $bullets
     *
     * @return Page
     */
    public function setBullets($bullets)
    {
        $this->bullets = $bullets;

        return $this;
    }

    /**
     * Get bullets
     *
     * @return string
     */
    public function getBullets()
    {
        return $this->bullets;
    }

    /**
     * Set place
     *
     * @param string $place
     *
     * @return Page
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set mostViewed
     *
     * @param boolean $mostViewed
     *
     * @return Page
     */
    public function setMostViewed($mostViewed)
    {
        $this->mostViewed = $mostViewed;

        return $this;
    }

    /**
     * Get mostViewed
     *
     * @return boolean
     */
    public function getMostViewed()
    {
        return $this->mostViewed;
    }

    /**
     * Set rss
     *
     * @param string $rss
     *
     * @return Page
     */
    public function setRss($rss)
    {
        $this->rss = $rss;

        return $this;
    }

    /**
     * Get rss
     *
     * @return string
     */
    public function getRss()
    {
        return $this->rss;
    }
    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $editingBy;


    /**
     * Set editingBy
     *
     * @param \BackendBundle\Entity\WfUser $editingBy
     *
     * @return Page
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

    /**
     * @var \BackendBundle\Entity\Columna
     */
    private $columna;


    /**
     * Set columna
     *
     * @param \BackendBundle\Entity\Columna $columna
     *
     * @return Page
     */
    public function setColumna(\BackendBundle\Entity\Columna $columna = null)
    {
        $this->columna = $columna;

        return $this;
    }

    /**
     * Get columna
     *
     * @return \BackendBundle\Entity\Columna
     */
    public function getColumna()
    {
        return $this->columna;
    }
    /**
     * @var string
     */
    private $elementHtml;

    /**
     * @var string
     */
    private $elementHtmlSerialized;


    /**
     * Set elementHtml
     *
     * @param string $elementHtml
     *
     * @return Page
     */
    public function setElementHtml($elementHtml)
    {
        $this->elementHtml = $elementHtml;

        return $this;
    }

    /**
     * Get elementHtml
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->elementHtml;
    }

    /**
     * Set elementHtmlSerialized
     *
     * @param string $elementHtmlSerialized
     *
     * @return Page
     */
    public function setElementHtmlSerialized($elementHtmlSerialized)
    {
        $this->elementHtmlSerialized = $elementHtmlSerialized;

        return $this;
    }

    /**
     * Get elementHtmlSerialized
     *
     * @return string
     */
    public function getElementHtmlSerialized()
    {
        $a = count( $this->elementHtmlSerialized ) > 0 ? json_encode( $this->elementHtmlSerialized, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT ) : null;

        return $a;
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
     * @return Page
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
     * @var \BackendBundle\Entity\Blog
     */
    private $blog;

    /**
     * Set blog
     *
     * @param \BackendBundle\Entity\Blog $blog
     *
     * @return Page
     */
    public function setBlog(\BackendBundle\Entity\Blog $blog = null)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * Get blog
     *
     * @return \BackendBundle\Entity\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @var boolean
     */
    private $isBreaking;


    /**
     * Set isBreaking
     *
     * @param boolean $isBreaking
     *
     * @return Page
     */
    public function setIsBreaking($isBreaking)
    {
        $this->isBreaking = $isBreaking;

        return $this;
    }

    /**
     * Get isBreaking
     *
     * @return boolean
     */
    public function getIsBreaking()
    {
        return $this->isBreaking;
    }


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $image;


    /**
     * Add image
     *
     * @param \BackendBundle\Entity\Image $image
     *
     * @return Page
     */
    public function addImage(\BackendBundle\Entity\Image $image)
    {
        $this->image[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \BackendBundle\Entity\Image $image
     */
    public function removeImage(\BackendBundle\Entity\Image $image)
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
     * @var \BackendBundle\Entity\Flags
     */
    private $flag;


    /**
     * Set flag
     *
     * @param \BackendBundle\Entity\Flags $flag
     *
     * @return Page
     */
    public function setFlag(\BackendBundle\Entity\Flags $flag = null)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * Get flag
     *
     * @return \BackendBundle\Entity\Flags
     */
    public function getFlag()
    {
        return $this->flag;
    }


    /**
     * @var \BackendBundle\Entity\Category
     */
    private $categoryId;


    /**
     * Set categoryId
     *
     * @param \BackendBundle\Entity\Category $categoryId
     *
     * @return PageVersion
     */
    public function setCategoryId(\BackendBundle\Entity\Category $categoryId = null)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return \BackendBundle\Entity\Category
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
    /**
     * @var string
     */
    private $slug_redirect;


    /**
     * Set slugRedirect
     *
     * @param string $slugRedirect
     *
     * @return Page
     */
    public function setSlugRedirect($slugRedirect)
    {
        $this->slug_redirect = $slugRedirect;

        return $this;
    }

    /**
     * Get slugRedirect
     *
     * @return string
     */
    public function getSlugRedirect()
    {
        return $this->slug_redirect;
    }
}
