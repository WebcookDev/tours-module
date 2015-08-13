<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ToursModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tours_category")
 */
class Category extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

    /**
     * @gedmo\Slug(fields={"name"})
     * @orm\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @orm\Column(type="text", nullable=true)
     */
    private $info;

    /**
     * @ORM\OneToMany(targetEntity="Tour", mappedBy="category") 
     * @var Array
     */
    private $tours;

    /**
     * @orm\ManyToOne(targetEntity="WebCMS\Entity\Page")
     * @orm\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $page;


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
     * Gets the value of slug.
     *
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the value of info.
     *
     * @param mixed $info the info
     *
     * @return self
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Gets the value of info.
     *
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Gets the tours of tours.
     *
     * @return mixed
     */
    public function getTours()
    {
        return $this->tours;
    }

    /**
     * Sets the value of tours.
     *
     * @param mixed $tours the tours
     *
     * @return self
     */
    public function setTours($tours)
    {
        $this->tours = $tours;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }
    
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }
}
