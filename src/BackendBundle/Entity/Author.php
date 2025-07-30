<?php

namespace BackendBundle\Entity;

/**
 * Author
 */
class Author
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $bio;

    /**
     * @var string
     */
    private $twitter;

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
     * Set name
     *
     * @param string $name
     *
     * @return Author
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
     * Set bio
     *
     * @param string $bio
     *
     * @return Author
     */
    public function setBio($bio)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return Author
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @var string
     */
    private $aMaterno;

    /**
     * @var string
     */
    private $aPaterno;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $facebook;

    /**
     * @var string
     */
    private $linkedin;

    /**
     * @var boolean
     */
    private $active;


    /**
     * Set aMaterno
     *
     * @param string $aMaterno
     *
     * @return Author
     */
    public function setAMaterno($aMaterno)
    {
        $this->aMaterno = $aMaterno;

        return $this;
    }

    /**
     * Get aMaterno
     *
     * @return string
     */
    public function getAMaterno()
    {
        return $this->aMaterno;
    }

    /**
     * Set aPaterno
     *
     * @param string $aPaterno
     *
     * @return Author
     */
    public function setAPaterno($aPaterno)
    {
        $this->aPaterno = $aPaterno;

        return $this;
    }

    /**
     * Get aPaterno
     *
     * @return string
     */
    public function getAPaterno()
    {
        return $this->aPaterno;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Author
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return Author
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     *
     * @return Author
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Author
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
     * @var string
     */
    private $googlePlus;

    /**
     * @var boolean
     */
    private $corresponsal;


    /**
     * Set googlePlus
     *
     * @param string $googlePlus
     *
     * @return Author
     */
    public function setGooglePlus($googlePlus)
    {
        $this->googlePlus = $googlePlus;

        return $this;
    }

    /**
     * Get googlePlus
     *
     * @return string
     */
    public function getGooglePlus()
    {
        return $this->googlePlus;
    }

    /**
     * Set corresponsal
     *
     * @param boolean $corresponsal
     *
     * @return Author
     */
    public function setCorresponsal($corresponsal)
    {
        $this->corresponsal = $corresponsal;

        return $this;
    }

    /**
     * Get corresponsal
     *
     * @return boolean
     */
    public function getCorresponsal()
    {
        return $this->corresponsal;
    }
    /**
     * @var string
     */
    private $sexo;


    /**
     * Set sexo
     *
     * @param string $sexo
     *
     * @return Author
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * Get sexo
     *
     * @return string
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    public function __toString() {

        $a = array(
                    "id"           => $this->id,
                    "name"         => $this->name,
                    "aPaterno"     => $this->aPaterno,
                    "aMaterno"     => $this->aMaterno,
                    "email"        => $this->email,
                    "bio"          => $this->bio,
                    "image"        => $this->image->getImagePath() ? $this->image->getImagePath() : null,
                    "imageSmall"   => $this->imageSmall->getImagePath() ? $this->imageSmall->getImagePath() : null,
                    "twitter"      => $this->twitter,
                    "facebook"     => $this->facebook,
                    "linkedin"     => $this->linkedin,
                    "googlePlus"   => $this->googlePlus,
                    "corresponsal" => $this->corresponsal,
                    "rss"          => $this->rss,
                    "slug"         => $this->slug,
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
    /**
     * @var boolean
     */
    private $rss;


    /**
     * Set rss
     *
     * @param boolean $rss
     *
     * @return Author
     */
    public function setRss($rss)
    {
        $this->rss = $rss;

        return $this;
    }

    /**
     * Get rss
     *
     * @return boolean
     */
    public function getRss()
    {
        return $this->rss;
    }
    /**
     * @var boolean
     */
    private $hiddenName;


    /**
     * Set hiddenName
     *
     * @param boolean $hiddenName
     *
     * @return Author
     */
    public function setHiddenName($hiddenName)
    {
        $this->hiddenName = $hiddenName;

        return $this;
    }

    /**
     * Get hiddenName
     *
     * @return boolean
     */
    public function getHiddenName()
    {
        return $this->hiddenName;
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
     * @return Author
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
     * @var \BackendBundle\Entity\Image
     */
    //private $image_small;


    /**
     * Set imageSmall
     *
     * @param \BackendBundle\Entity\Image $imageSmall
     *
     * @return Author
     */
    public function setImageSmall(\BackendBundle\Entity\Image $imageSmall = null)
    {
        $this->imageSmall = $imageSmall;

        return $this;
    }

    /**
     * Get imageSmall
     *
     * @return \BackendBundle\Entity\Image
     */
    public function getImageSmall()
    {
        return $this->imageSmall;
    }
    /**
     * @var \BackendBundle\Entity\Image
     */
    private $imageSmall;



    /**
     * @var string
     */
    private $slug;


    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Author
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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Author
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
     * @return Author
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
}
