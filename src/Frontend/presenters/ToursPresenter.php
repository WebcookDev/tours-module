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
        
        $this->categories = $this->categoryRepository->findBy(array(
            'page' => $this->actualPage
        ));

        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->category = $this->categoryRepository->findOneBy(array(
                'slug' => $slug
            ));

            if ($this->category) {
                $this->tours = $this->repository->findBy(array(
                    'category' => $this->category
                ));

                if (isset($parameters['parameters'][1])) {
                    $this->tour = $this->repository->findOneBy(array(
                        'slug' => $parameters['parameters'][1]
                    ));
                }
            } else {
                $this->tour = $this->repository->findOneBy(array(
                    'slug' => $slug
                ));
            }



        } else {
            $this->tours = $this->repository->findBy(array(
                'page' => $this->actualPage
            ));
        }

    }

    public function createComponentForm($name, $context = null, $fromPage = null) 
    {

        if($context != null){

            $form = new UI\Form();

            // TODO fix this, fix for guide-prague
            if ($this->tour->getCategory()->getSlug() == "vylety-mimo-prahu" || $this->tour->getCategory()->getSlug() == "vylety-mimo-cr") {
                $category = "";
            } else {
                $category = $this->tour->getCategory()->getSlug();
            }

            $form->getElementPrototype()->action = $context->link('default', array(
                'path' => $fromPage->getPath(),
                'abbr' => $context->abbr,
                'parameters' => array($category, $this->tour->getSlug()),
                'do' => 'form-submit'
            ));

            $form->setTranslator($context->translator);
            $form->setRenderer(new BootstrapRenderer);
            
            $form->getElementPrototype()->class = 'form-horizontal contact-agent-form';
            
        }else{
            $form = $this->createForm('form-submit', 'default', $context);
        }

        $languages = array(
            'cz' => 'Czech',
            'en' => 'English',
            'it' => 'Italian',
            'ru' => 'Russian',
            'fr' => 'French',
            'other' => 'Other'
         );
        $form->addText('name', 'Name')->setRequired();
        $form->addText('email', 'E-mail')
            ->addRule(UI\Form::EMAIL, 'Email is not valid')
            ->setRequired();
        $form->addText('phone', 'Phone number');
        $form->addHidden('tourId', $this->tour->getId());
        $form->addHidden('tourName', $this->tour->getName());

        $form->addText('location', 'Location')->setRequired();
        $form->addText('people_count', 'People count')->setRequired();
        $form->addSelect('languages', 'Language', $languages)->setRequired();

        $form->addText('date', 'Date')->setRequired();
        $form->addText('time', 'Time')
            ->setAttribute('placeholder', '9:00')
            ->setRequired();

        $form->addTextArea('text', 'Text');

        $form->addSubmit('submit', 'Send demand')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {

        $values = $form->getValues();

        $mail = new \Nette\Mail\Message;
        $infoMail = $this->settings->get('Info email', 'basic', 'text')->getValue();
        $mail->addTo($infoMail);
        
        $domain = str_replace('www.', '', $this->getHttpRequest()->url->host);
        
        if($domain !== 'localhost') $mail->setFrom('no-reply@' . $domain);
        else $mail->setFrom('no-reply@test.cz'); // TODO move to settings

        $mailBody = '<h1>'.$values->tourName.'</h1>';
        $mailBody .= '<p><strong>Jméno: </strong>'.$values->name.'</p>';
        $mailBody .= '<p><strong>Email: </strong>'.$values->email.'</p>';
        $mailBody .= '<p><strong>Telefon: </strong>'.$values->phone.'</p>';
        $mailBody .= '<p><strong>Datum prohlídky: </strong>'.$values->date.'</p>';
        $mailBody .= '<p><strong>Čas začátku prohlídky: </strong>'.$values->time.'</p>';
        $mailBody .= '<p><strong>Místo začátku prohlídky: </strong>'.$values->location.'</p>';
        $mailBody .= '<p><strong>Počet lidí: </strong>'.$values->people_count.'</p>';
        $mailBody .= '<p><strong>Jazyk prohlídky: </strong>'.$values->languages.'</p>';
        $mailBody .= '<p><strong>Další požadavky: </strong>'.$values->text.'</p>';

        $mail->setSubject('Poptávka prohlídky '.$values->tourName);
        $mail->setHtmlBody($mailBody);

        try {
            $mail->send();  
            $this->flashMessage('Reservation form has been sent', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Cannot send email.', 'danger');                    
        }
       

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

        $actualCategory = end((explode('/', $_SERVER['REQUEST_URI'])));
        $urlCategory = $this->categoryRepository->findOneBy(array(
            'slug' => $actualCategory
        ));

        // dont care anymore
        if ($actualCategory == 'prohlidky-prahy') {
            $urlCategory = $this->categoryRepository->findOneBy(array(
                'slug' => 'za-pamatkami'
            ));
        }
        if ($actualCategory == 'prague-tours') {
            $urlCategory = $this->categoryRepository->findOneBy(array(
                'slug' => 'sightseeing'
            ));
        }
        if ($actualCategory == 'balades-a-prague-1') {
            $urlCategory = $this->categoryRepository->findOneBy(array(
                'slug' => 'endroits-historiques-1'
            ));
        }

        $this->template->page = $this->getParameter('p') ? $this->getParameter('p') : 0;
        $this->template->tours = $this->tours;
        $this->template->categories = $this->categories;
        $this->template->id = $id;
        $this->template->urlCategory = $urlCategory;
    }

    public function homepageBox($context, $fromPage)
    {
        $template = $context->createTemplate();
        $template->tours = $context->em->getRepository('WebCMS\ToursModule\Entity\Tour')->findBy(array(
            'hide' => false,
            'homepage' => true
        ));

        foreach ($template->tours as $key => $tour) {
            if ($tour->getPage()->getLanguage() != $fromPage->getLanguage()) {
                unset($template->tours[$key]);
            }
        }

        $template->tourPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Tours',
            'presenter' => 'Tours',
            'language' => $fromPage->getLanguage()
        ));

        $topTour = $context->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->findOneBy(array(
            'top' => true,
            'page' => $fromPage
        ));
        $template->topTour = $topTour ? $topTour : null;
        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/tours-module/Tours/homepageBox.latte');

        return $template;  
    }

    public function carouselBox($context, $fromPage)
    {
        $template = $context->createTemplate();
        $template->tours = $context->em->getRepository('WebCMS\ToursModule\Entity\Tour')->findBy(array(
            'hide' => false,
            'homepage' => true
        ));

        foreach ($template->tours as $key => $tour) {
            if ($tour->getPage()->getLanguage() != $fromPage->getLanguage()) {
                unset($template->tours[$key]);
            }
        }


        $photos = array();
        foreach ($template->tours as $tour) {
            if ($tour->getDefaultPhoto()) {
                $photos[$tour->getSlug()] = $tour->getDefaultPhoto()->getPath();
            }
        }

        $template->tourPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Tours',
            'presenter' => 'Tours',
            'language' => $fromPage->getLanguage()
        ));

        $topTour = $context->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->findOneBy(array(
            'top' => true,
            'page' => $fromPage
        ));

        $template->topTour = $topTour ? $topTour : null;
        $template->abbr = $context->abbr;
        $template->photos = $photos;
        $template->setFile(APP_DIR . '/templates/tours-module/Tours/homepageCarouselBox.latte');

        return $template;  
    }

}
