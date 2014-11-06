<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ToursModule;

/**
 * Description of tours
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class Tours extends \WebCMS\Module
{
	/**
	 * [$name description]
	 * @var string
	 */
    protected $name = 'Tours';
    
    /**
     * [$author description]
     * @var string
     */
    protected $author = 'Jakub Sanda';
    
    protected $searchable = true;

    /**
     * [$presenters description]
     * @var array
     */
    protected $presenters = array(
		array(
		    'name' => 'Tours',
		    'frontend' => true,
		    'parameters' => true
		),
		array(
		    'name' => 'Settings',
		    'frontend' => false
		)
    );

    public function __construct()
    {
        $this->addBox('homepage', 'Tours', 'homepageBox');
    }

    public function search(\Doctrine\ORM\EntityManager $em, $phrase, \WebCMS\Entity\Language $language)
    {
        
    }
}
