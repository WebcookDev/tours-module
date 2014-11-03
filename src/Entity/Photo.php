<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ToursModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tours_photo")
 */
class Photo extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="Tour", inversedBy="photos") 
     */
    private $tour;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;


    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of path.
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the value of path.
     *
     * @param mixed $path the path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Gets the value of tour.
     *
     * @return mixed
     */
    public function getTour()
    {
        return $this->tour;
    }

    /**
     * Sets the value of tour.
     *
     * @param mixed $tour the tour
     *
     * @return self
     */
    public function setTour($tour)
    {
        $this->tour = $tour;

        return $tour;
    }

    /**
     * Gets the value of main.
     *
     * @return mixed
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Sets the value of main.
     *
     * @param mixed $main the main
     *
     * @return self
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Gets the value of created.
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the value of created.
     *
     * @param mixed $created the created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }
}
