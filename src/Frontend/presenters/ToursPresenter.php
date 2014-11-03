<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\ToursModule;

use WebCMS\ToursModule\Entity\Tour;

/**
 * Description of ToursPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ToursPresenter extends BasePresenter
{
    private $repository;

    private $categoryRepository;

    private $tour;

    private $tours;

    private $category;
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\ToursModule\Entity\Tour');
        $this->categoryRepository = $this->em->getRepository('WebCMS\ToursModule\Entity\Category');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function actionDefault($id)
    {
        $parameters = $this->getParameter();
        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->category = $this->categoryRepository->findOneBy(array(
                'slug' => $slug
            ));
            
            if (!$this->category) {
                $this->tour = $this->repository->findOneBy(array(
                    'slug' => $slug,
                    'hide' => false
                ));
            }
        }

        $this->tours = $this->repository->findAll();
    }

    public function renderDefault($id)
    {   
        if ($this->tour) {
            $this->template->tour = $this->tour;

            $this->template->seoTitle = $this->car->getFullname() . ' - ' . $this->actualPage->getMetaTitle();
            $this->template->setFile(APP_DIR . '/templates/tours-module/Tours/detail.latte');
        }

        $this->template->page = $this->getParameter('p') ? $this->getParameter('p') : 0;
        $this->template->tours = $this->tours;
        $this->template->category = $this->category;
        $this->template->id = $id;
    }

    public function homepageBox($context)
    {
        $template = $context->createTemplate();
        $template->cars = $context->em->getRepository('WebCMS\ToursModule\Entity\Car')->findBy(array(
            'hide' => false,
            'homepage' => true
        ));

        $topTour = $context->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->findOneBy(array(
            'top' => true
        ));
        $template->topTour = $topTour ? $topTour : null;
        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/tours-module/Tours/homepageBox.latte');

        return $template;  
    }

}
