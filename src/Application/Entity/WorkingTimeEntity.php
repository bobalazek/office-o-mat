<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Working Time Entity
 *
 * @ORM\Table(name="working_times")
 * @ORM\Entity(repositoryClass="Application\Repository\WorkingTimeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WorkingTimeEntity
{
    /*************** Variables ***************/
    /********** General Variables **********/
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    protected $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=true)
     */
    protected $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_started", type="datetime")
     */
    protected $timeStarted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_ended", type="datetime", nullable=true)
     */
    protected $timeEnded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_created", type="datetime")
     */
    protected $timeCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_updated", type="datetime")
     */
    protected $timeUpdated;

    /***** Relationship Variables *****/
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\UserEntity", inversedBy="workingTimes")
     */
    protected $user;

    /*************** Methods ***************/
    /********** General Methods **********/
    /***** Getters, Setters and Other stuff *****/
    /*** Id ***/
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /*** Notes ***/
    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /*** Location ***/
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /*** Time started ***/
    public function getTimeStarted()
    {
        return $this->timeStarted;
    }

    public function setTimeStarted(\DateTime $timeStarted)
    {
        $this->timeStarted = $timeStarted;

        return $this;
    }

    /*** Time ended ***/
    public function getTimeEnded()
    {
        return $this->timeEnded;
    }

    public function setTimeEnded(\DateTime $timeEnded = null)
    {
        $this->timeEnded = $timeEnded;

        return $this;
    }

    public function isTimeEndedAfterTimeStarted()
    {
        $timeStarted = $this->getTimeStarted();
        $timeEnded = $this->getTimeEnded();

        if ($timeEnded) {
            return $timeStarted < $timeEnded;
        }

        return true;
    }

    /*** Time created ***/
    public function getTimeCreated()
    {
        return $this->timeCreated;
    }

    public function setTimeCreated(\DateTime $timeCreated)
    {
        $this->timeCreated = $timeCreated;

        return $this;
    }

    /*** Time updated ***/
    public function getTimeUpdated()
    {
        return $this->timeUpdated;
    }

    public function setTimeUpdated(\DateTime $timeUpdated)
    {
        $this->timeUpdated = $timeUpdated;

        return $this;
    }

    /*** User ***/
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\Application\Entity\UserEntity $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /********** API ***********/
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'time_started' => $this->getTimeStarted()->format(DATE_ATOM),
            'time_ended' => $this->getTimeEnded()
                ? $this->getTimeEnded()->format(DATE_ATOM)
                : null,
            'notes' => $this->getNotes(),
            'location' => $this->getLocation(),
            'time_created' => $this->getTimeCreated()->format(DATE_ATOM),
        );
    }

    /********** Magic Methods **********/
    public function __toString()
    {
        return 'Working Time #'.$this->getId();
    }

    /********** Callback Methods **********/
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setTimeUpdated(new \DateTime('now'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setTimeUpdated(new \DateTime('now'));
        $this->setTimeCreated(new \DateTime('now'));
    }
}
