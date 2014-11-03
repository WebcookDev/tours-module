<?php

/**
 * This file is part of the Tours module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\ToursModule;

use Nette\Forms\Form;
use WebCMS\ToursModule\Entity\Tour;
use WebCMS\ToursModule\Entity\Photo;
use WebCMS\ToursModule\Entity\Category;

/**
 * Main controller
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ToursPresenter extends BasePresenter
{
    private $tour;

    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();
    }

    public function actionDefault($idPage)
    {

    }

    public function renderDefault($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\CarsModule\Entity\Tour");

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('top', 'Top')->setCustomRender(function($item) {
            return $item->getTop() ? 'yes' : 'no';
        })->setSortable();
        $grid->addColumnText('homepage', 'Added To homepage')->setCustomRender(function($item) {
            return $item->getHomepage() ? 'yes' : 'no';
        })->setSortable();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("top", 'Top', 'top', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("addToHomepage", 'Add to homepage', 'addToHomepage', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));

        return $grid;
    }

    public function actionUpdate($id, $idPage)
    {
        $this->reloadContent();

        $this->tour = $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->find($id);

        $this->template->idPage = $idPage;
    }

    public function actionTop($id, $idPage)
    {
        $topTour = $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->findOneBy(array(
            'top' => true
        ));
        if ($topTour) {
            $topTour->setTop(false);
        }

        $this->tour = $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->find($id);
        $this->tour->setTop(true);

        $this->em->flush();

        $this->flashMessage('Tour has been set as top tour', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionAddToHomepage($id, $idPage)
    {
        $this->tour = $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->find($id);
        $this->tour->setHomepage($this->tour->getHomepage() ? false : true);

        $this->em->flush();

        $this->flashMessage('Tour has been set as homepage tour', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentTourForm()
    {
        $form = $this->createForm();
                
        $form->addCheckbox('hide', 'Hide');

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'tourFormSubmitted');

        $form->setDefaults($this->tour->toArray());
        
        return $form;
    }
    
    public function tourFormSubmitted($form)
    {
        $values = $form->getValues();
        
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->car->$setter($value);
        }

        $this->em->flush();
        $this->flashMessage('Tour has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }
}