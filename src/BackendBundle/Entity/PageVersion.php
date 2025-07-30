<?php

namespace BackendBundle\Entity;

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
     * @var \BackendBundle\Entity\Page
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
     * Set page
     *
     * @param \BackendBundle\Entity\Page $page
     *
     * @return PageVersion
     */
    public function setPage(\BackendBundle\Entity\Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \BackendBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
    /**
     * @var integer
     */
    private $categoryId;


    /**
     * Set categoryId
     *
     * @param integer $categoryId
     *
     * @return PageVersion
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
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
     * @return PageVersion
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
     * @var \BackendBundle\Entity\Image
     */
    private $mainImage;

    /**
     * @var \BackendBundle\Entity\PageVersion
     */
    private $version;


    /**
     * Set mainImage
     *
     * @param \BackendBundle\Entity\Image $mainImage
     *
     * @return PageVersion
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
     * Set version
     *
     * @param \BackendBundle\Entity\PageVersion $version
     *
     * @return PageVersion
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
     * @var \DateTime
     */
    private $createdAtPage;

    /**
     * @var \DateTime
     */
    private $updatedAtPage;

    /**
     * @var \DateTime
     */
    private $publishedAtPage;


    /**
     * Set createdAtPage
     *
     * @param \DateTime $createdAtPage
     *
     * @return PageVersion
     */
    public function setCreatedAtPage($createdAtPage)
    {
        $this->createdAtPage = $createdAtPage;

        return $this;
    }

    /**
     * Get createdAtPage
     *
     * @return \DateTime
     */
    public function getCreatedAtPage()
    {
        return $this->createdAtPage;
    }

    /**
     * Set updatedAtPage
     *
     * @param \DateTime $updatedAtPage
     *
     * @return PageVersion
     */
    public function setUpdatedAtPage($updatedAtPage)
    {
        $this->updatedAtPage = $updatedAtPage;

        return $this;
    }

    /**
     * Get updatedAtPage
     *
     * @return \DateTime
     */
    public function getUpdatedAtPage()
    {
        return $this->updatedAtPage;
    }

    /**
     * Set publishedAtPage
     *
     * @param \DateTime $publishedAtPage
     *
     * @return PageVersion
     */
    public function setPublishedAtPage($publishedAtPage)
    {
        $this->publishedAtPage = $publishedAtPage;

        return $this;
    }

    /**
     * Get publishedAtPage
     *
     * @return \DateTime
     */
    public function getPublishedAtPage()
    {
        return $this->publishedAtPage;
    }
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $seo;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return PageVersion
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
     * Set status
     *
     * @param string $status
     *
     * @return PageVersion
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
     * Set template
     *
     * @param string $template
     *
     * @return PageVersion
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
     * Set seo
     *
     * @param string $seo
     *
     * @return PageVersion
     */
    public function setSeo($seo)
    {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return string
     */
    public function getSeo()
    {
        return $this->seo;
    }
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
    private $related;


    /**
     * Set settings
     *
     * @param array $settings
     *
     * @return PageVersion
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
     * @return PageVersion
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
     * Set related
     *
     * @param array $related
     *
     * @return PageVersion
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
     * @var string
     */
    private $content;

    /**
     * @var \BackendBundle\Entity\WfUser
     */
    private $creator;


    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return PageVersion
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
     * @return PageVersion
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
     * @return PageVersion
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
     * @param string $content
     *
     * @return PageVersion
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
     * Set creator
     *
     * @param \BackendBundle\Entity\WfUser $creator
     *
     * @return PageVersion
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
     * @var integer
     */
    private $portalId;

    /**
     * @var string
     */
    private $social;

    /**
     * @var string
     */
    private $html;

    /**
     * @var string
     */
    private $html_serialize;

    /**
     * Set portalId
     *
     * @param integer $portalId
     *
     * @return PageVersion
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
     * @return PageVersion
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
     * Set html
     *
     * @param string $html
     *
     * @return PageVersion
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
     * Set htmlSerialize
     *
     * @param string $htmlSerialize
     *
     * @return PageVersion
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
        return $this->html_serialize;
    }

    /**
     * @var boolean
     */
    private $newslatter;

    /**
     * @var array
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
     * @var array
     */
    private $rss;

    /**
     * Set newslatter
     *
     * @param boolean $newslatter
     *
     * @return PageVersion
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
     * @param array $bullets
     *
     * @return PageVersion
     */
    public function setBullets($bullets)
    {
        $this->bullets = $bullets;

        return $this;
    }

    /**
     * Get bullets
     *
     * @return array
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
     * @return PageVersion
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
     * @return PageVersion
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
     * @param array $rss
     *
     * @return PageVersion
     */
    public function setRss($rss)
    {
        $this->rss = $rss;

        return $this;
    }

    /**
     * Get rss
     *
     * @return array
     */
    public function getRss()
    {
        return $this->rss;
    }
    /**
     * @var \BackendBundle\Entity\Tag
     */
    private $mainTag;

    /**
     * Set mainTag
     *
     * @param \BackendBundle\Entity\Tag $mainTag
     *
     * @return PageVersion
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
     * @var \BackendBundle\Entity\WfUser
     */
    private $editingBy;


    /**
     * Set editingBy
     *
     * @param \BackendBundle\Entity\WfUser $editingBy
     *
     * @return PageVersion
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
     * @return PageVersion
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
     * @return PageVersion
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
     * @return PageVersion
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
        return $this->elementHtmlSerialized;
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
     * @return PageVersion
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
     * @return PageVersion
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
     * @return PageVersion
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
     * @var array
     */
    private $fieldsManytoMany;


    /**
     * Set fieldsManytoMany
     *
     * @param array $fieldsManytoMany
     *
     * @return PageVersion
     */
    public function setFieldsManytoMany($fieldsManytoMany)
    {
        $this->fieldsManytoMany = $fieldsManytoMany;

        return $this;
    }

    /**
     * Get fieldsManytoMany
     *
     * @return array
     */
    public function getFieldsManytoMany()
    {
        return $this->fieldsManytoMany;
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
     * @return PageVersion
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
     * @var \DateTime
     */
    private $nextPublishedAt;

    /**
     * @var \DateTime
     */
    private $nextPublishedAtPage;


    /**
     * Set nextPublishedAt
     *
     * @param \DateTime $nextPublishedAt
     *
     * @return PageVersion
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
     * Set nextPublishedAtPage
     *
     * @param \DateTime $nextPublishedAtPage
     *
     * @return PageVersion
     */
    public function setNextPublishedAtPage($nextPublishedAtPage)
    {
        $this->nextPublishedAtPage = $nextPublishedAtPage;

        return $this;
    }

    /**
     * Get nextPublishedAtPage
     *
     * @return \DateTime
     */
    public function getNextPublishedAtPage()
    {
        return $this->nextPublishedAtPage;
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
     * @return PageVersion
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
