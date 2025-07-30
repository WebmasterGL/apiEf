<?php

namespace BackendBundle\Entity;

/**
 * Layout
 */
class Layout
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $html;

    /**
     * @var string
     */
    private $stylesheets;

    /**
     * @var string
     */
    private $javascripts;


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
     * @return Layout
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
     * Set html
     *
     * @param string $html
     *
     * @return Layout
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
     * Set stylesheets
     *
     * @param string $stylesheets
     *
     * @return Layout
     */
    public function setStylesheets($stylesheets)
    {
        $this->stylesheets = $stylesheets;

        return $this;
    }

    /**
     * Get stylesheets
     *
     * @return string
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * Set javascripts
     *
     * @param string $javascripts
     *
     * @return Layout
     */
    public function setJavascripts($javascripts)
    {
        $this->javascripts = $javascripts;

        return $this;
    }

    /**
     * Get javascripts
     *
     * @return string
     */
    public function getJavascripts()
    {
        return $this->javascripts;
    }
}
