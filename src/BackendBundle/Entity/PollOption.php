<?php

namespace BackendBundle\Entity;

/**
 * PollOption
 */
class PollOption
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $optionName;

    /**
     * @var integer
     */
    private $voteCount;

    /**
     * @var \BackendBundle\Entity\Poll
     */
    private $poll;


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
     * Set optionName
     *
     * @param string $optionName
     *
     * @return PollOption
     */
    public function setOptionName($optionName)
    {
        $this->optionName = $optionName;

        return $this;
    }

    /**
     * Get optionName
     *
     * @return string
     */
    public function getOptionName()
    {
        return $this->optionName;
    }

    /**
     * Set voteCount
     *
     * @param integer $voteCount
     *
     * @return PollOption
     */
    public function setVoteCount($voteCount)
    {
        $this->voteCount = $voteCount;

        return $this;
    }

    /**
     * Get voteCount
     *
     * @return integer
     */
    public function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * Set poll
     *
     * @param \BackendBundle\Entity\Poll $poll
     *
     * @return PollOption
     */
    public function setPoll(\BackendBundle\Entity\Poll $poll = null)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll
     *
     * @return \BackendBundle\Entity\Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }
}
