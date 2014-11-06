<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\ToursModule;

use Nette\Application\UI;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
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

    private $categories;
    
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
        
        $this->categories = $this->categoryRepository->findAll();

        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->category = $this->categoryRepository->findOneBy(array(
                'slug' => $slug
            ));

            $this->tours = $this->repository->findBy(array(
                'category' => $this->category
            ));

            if (isset($parameters['parameters'][1])) {
                $this->tour = $this->repository->findOneBy(array(
                    'slug' => $parameters['parameters'][1]
                ));
            }

        } else {
            $this->tours = $this->repository->findBy(array(
                'category' => $this->categories[0]
            ));
        }

    }

    public function createComponentForm($name, $context = null, $fromPage = null) 
    {

        if($context != null){

            $form = new UI\Form();

            $form->getElementPrototype()->action = $context->link('default', array(
                'path' => $fromPage->getPath(),
                'abbr' => $context->abbr,
                'parameters' => array($this->tour->getCategory()->getSlug(), $this->tour->getSlug()),
                'do' => 'form-submit'
            ));

            $form->setTranslator($context->translator);
            $form->setRenderer(new BootstrapRenderer);
            
            $form->getElementPrototype()->class = 'form-horizontal contact-agent-form';
            
        }else{
            $form = $this->createForm('form-submit', 'default', $context);
        }

        $form->addText('name', 'Name')->setRequired();
        $form->addText('email', 'E-mail')->setRequired();
        $form->addText('phone', 'Phone number');
        $form->addHidden('tourId', $this->tour->getId());

        $form->addSubmit('submit', 'Send demand')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {

        $values = $form->getValues();

        $this->flashMessage('Reservation form has been sent', 'success');

        $httpRequest = $this->getContext()->getService('httpRequest');

        $url = $httpRequest->getReferer();
        $url->appendQuery(array(self::FLASH_KEY => $this->getParam(self::FLASH_KEY)));

        $this->redirectUrl($url->absoluteUrl);
        
    }

    public function renderDefault($id)
    {   
        if ($this->tour) {

            $otherTours = $this->repository->findBy(array(
                'category' => $this->tour->getCategory()
            ));

            foreach ($otherTours as $key => $ot) {
                if($ot->getId() === $this->tour->getId()){
                    unset($otherTours[$key]);
                }
            }

            $this->template->otherTours = $otherTours;
            $this->template->tour = $this->tour;
            $this->template->reservationForm = $this->createComponentForm('form', $this, $this->actualPage);
            $this->template->seoTitle = $this->tour->getName() . ' - ' . $this->actualPage->getMetaTitle();
            $this->template->setFile(APP_DIR . '/templates/tours-module/Tours/detail.latte');
        }

        $this->template->page = $this->getParameter('p') ? $this->getParameter('p') : 0;
        $this->template->tours = $this->tours;
        $this->template->categories = $this->categories;
        $this->template->id = $id;
    }

    public function homepageBox($context)
    {
        $template = $context->createTemplate();
        $template->tours = $context->em->getRepository('WebCMS\ToursModule\Entity\Tour')->findBy(array(
            'hide' => false,
            'homepage' => true
        ));

        $template->tourPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Tours',
            'presenter' => 'Tours'
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
