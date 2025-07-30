<?php

namespace XalokBundle\Entity;

/**
 * WfUser
 */
class WfUser
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $usernameCanonical;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $emailCanonical;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @var boolean
     */
    private $locked;

    /**
     * @var boolean
     */
    private $expired;

    /**
     * @var \DateTime
     */
    private $expiresAt;

    /**
     * @var string
     */
    private $confirmationToken;

    /**
     * @var \DateTime
     */
    private $passwordRequestedAt;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var boolean
     */
    private $credentialsExpired;

    /**
     * @var \DateTime
     */
    private $credentialsExpireAt;

    /**
     * @var string
     */
    private $contact;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $twitter;

    /**
     * @var string
     */
    private $facebook;

    /**
     * @var string
     */
    private $job;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $columnist;

    /**
     * @var \XalokBundle\Entity\Image
     */
    private $avatar;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $blog;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $page;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $group;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blog = new \Doctrine\Common\Collections\ArrayCollection();
        $this->page = new \Doctrine\Common\Collections\ArrayCollection();
        $this->group = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set username
     *
     * @param string $username
     *
     * @return WfUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set usernameCanonical
     *
     * @param string $usernameCanonical
     *
     * @return WfUser
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * Get usernameCanonical
     *
     * @return string
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return WfUser
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
     * Set emailCanonical
     *
     * @param string $emailCanonical
     *
     * @return WfUser
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * Get emailCanonical
     *
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return WfUser
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return WfUser
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return WfUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return WfUser
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return WfUser
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set expired
     *
     * @param boolean $expired
     *
     * @return WfUser
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * Get expired
     *
     * @return boolean
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     *
     * @return WfUser
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     *
     * @return WfUser
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set passwordRequestedAt
     *
     * @param \DateTime $passwordRequestedAt
     *
     * @return WfUser
     */
    public function setPasswordRequestedAt($passwordRequestedAt)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    /**
     * Get passwordRequestedAt
     *
     * @return \DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return WfUser
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set credentialsExpired
     *
     * @param boolean $credentialsExpired
     *
     * @return WfUser
     */
    public function setCredentialsExpired($credentialsExpired)
    {
        $this->credentialsExpired = $credentialsExpired;

        return $this;
    }

    /**
     * Get credentialsExpired
     *
     * @return boolean
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * Set credentialsExpireAt
     *
     * @param \DateTime $credentialsExpireAt
     *
     * @return WfUser
     */
    public function setCredentialsExpireAt($credentialsExpireAt)
    {
        $this->credentialsExpireAt = $credentialsExpireAt;

        return $this;
    }

    /**
     * Get credentialsExpireAt
     *
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return WfUser
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return WfUser
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return WfUser
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return WfUser
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
     * Set twitter
     *
     * @param string $twitter
     *
     * @return WfUser
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
     * Set facebook
     *
     * @param string $facebook
     *
     * @return WfUser
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
     * Set job
     *
     * @param string $job
     *
     * @return WfUser
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return WfUser
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
     * Set columnist
     *
     * @param boolean $columnist
     *
     * @return WfUser
     */
    public function setColumnist($columnist)
    {
        $this->columnist = $columnist;

        return $this;
    }

    /**
     * Get columnist
     *
     * @return boolean
     */
    public function getColumnist()
    {
        return $this->columnist;
    }

    /**
     * Set avatar
     *
     * @param \XalokBundle\Entity\Image $avatar
     *
     * @return WfUser
     */
    public function setAvatar(\XalokBundle\Entity\Image $avatar = null)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return \XalokBundle\Entity\Image
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Add blog
     *
     * @param \XalokBundle\Entity\Blog $blog
     *
     * @return WfUser
     */
    public function addBlog(\XalokBundle\Entity\Blog $blog)
    {
        $this->blog[] = $blog;

        return $this;
    }

    /**
     * Remove blog
     *
     * @param \XalokBundle\Entity\Blog $blog
     */
    public function removeBlog(\XalokBundle\Entity\Blog $blog)
    {
        $this->blog->removeElement($blog);
    }

    /**
     * Get blog
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Add page
     *
     * @param \XalokBundle\Entity\Page $page
     *
     * @return WfUser
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
     * Add group
     *
     * @param \XalokBundle\Entity\WfGroup $group
     *
     * @return WfUser
     */
    public function addGroup(\XalokBundle\Entity\WfGroup $group)
    {
        $this->group[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param \XalokBundle\Entity\WfGroup $group
     */
    public function removeGroup(\XalokBundle\Entity\WfGroup $group)
    {
        $this->group->removeElement($group);
    }

    /**
     * Get group
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function __toString() {
        $a = array(
            "id"        => $this->id,
            "username"  => $this->username,
            "firstName" => $this->firstName
        );

        return json_encode( $a, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT );
    }
}

