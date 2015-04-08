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

    private $category;

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

    public function actionCategories($idPage)
    {
       
    }

    public function renderCategories($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ToursModule\Entity\Tour", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('category_id', 'Category')->setCustomRender(function($item) {
            return $item->getCategory()->getName();
        })->setSortable();

        $grid->addColumnText('tourTime', 'Tour time')->setSortable();

        $grid->addColumnText('top', 'Top')->setCustomRender(function($item) {
            return $item->getTop() ? 'yes' : 'no';
        })->setSortable();
        $grid->addColumnText('homepage', 'Added To homepage')->setCustomRender(function($item) {
            return $item->getHomepage() ? 'yes' : 'no';
        })->setSortable();

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("top", 'Top', 'top', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("addToHomepage", 'Add to homepage', 'addToHomepage', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger') , 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdate($id, $idPage)
    {
        $this->reloadContent();

        $this->tour = $id ? $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->find($id) : "";

        $this->template->idPage = $idPage;
        $this->template->tour = $this->tour;
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

    public function actionDelete($id){

        $act = $this->em->getRepository('\WebCMS\ToursModule\Entity\Tour')->find($id);
        $this->em->remove($act);
        $this->em->flush();
        
        $this->flashMessage('Tour has been removed.', 'success');
        
        if(!$this->isAjax()){
            $this->redirect('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    protected function createComponentCategoriesGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\ToursModule\Entity\Category", null, array(
            'page = '.$this->actualPage->getId()
        ));

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addActionHref("updateCategory", 'Edit', 'updateCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));

        return $grid;
    }

    public function actionUpdateCategory($id, $idPage)
    {
        $this->reloadContent();

        $this->category = $id ? $this->em->getRepository('\WebCMS\ToursModule\Entity\Category')->find($id) : "";

        $this->template->idPage = $idPage;
    }

    protected function createComponentCategoryForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired();

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'categoryFormSubmitted');
 
        if (is_object($this->category)) {
            $form->setDefaults($this->category->toArray());
        }
        
        return $form;
    }
    
    public function categoryFormSubmitted($form)
    {
        $values = $form->getValues();

        if(!is_object($this->category)){
            $this->category = new Category;
            $this->em->persist($this->category);
        }
        
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->category->$setter($value);
        }

        $this->category->setPage($this->actualPage);

        $this->em->flush();
        $this->flashMessage('Category has been added/updated.', 'success');
        
        $this->forward('categories', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentTourForm()
    {
        $form = $this->createForm();

        $categories = $this->em->getRepository('\WebCMS\ToursModule\Entity\Category')->findAll();
        $categoriesForSelect = array();
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesForSelect[$category->getId()] = $category->getName();
            }
        }

        $form->addText('name', 'Name')->setRequired();
        $form->addSelect('category_id', 'Category')->setItems($categoriesForSelect);
        $form->addText('tourTime', 'Tour time')->setRequired();
        $form->addTextArea('price', 'Price')->setAttribute('class', 'form-control editor');
        $form->addTextArea('shortInfo', 'Short info')->setAttribute('class', 'form-control editor');
        $form->addTextArea('info', 'Info')->setAttribute('class', 'form-control editor');
                
        $form->addCheckbox('hide', 'Hide');

        $form->addSubmit('submit', 'Save')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = callback($this, 'tourFormSubmitted');
 
        if (is_object($this->tour)) {
            $form->setDefaults($this->tour->toArray());
        }
        
        return $form;
    }
    
    public function tourFormSubmitted($form)
    {
        $values = $form->getValues();

        if(!is_object($this->tour)){
            $this->tour = new Tour;
            $this->em->persist($this->tour);
        }else{
            // delete old photos and save new ones
            $qb = $this->em->createQueryBuilder();
            $qb->delete('WebCMS\ToursModule\Entity\Photo', 'l')
                    ->where('l.tour = ?1')
                    ->setParameter(1, $this->tour)
                    ->getQuery()
                    ->execute();
        }

        $category = $this->em->getRepository('\WebCMS\ToursModule\Entity\Category')->find($values->category_id);
        
        $this->tour->setName($values->name);
        $this->tour->setCategory($category);
        $this->tour->setTourTime($values->tourTime);
        $this->tour->setPrice($values->price);
        $this->tour->setShortInfo($values->shortInfo);
        $this->tour->setInfo($values->info);
        $this->tour->setHide($values->hide);
        $this->tour->setPage($this->actualPage);
            
        if(array_key_exists('files', $_POST)){
            $counter = 0;
            if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
            else $default = -1;
            
            foreach($_POST['files'] as $path){

                $photo = new \WebCMS\ToursModule\Entity\Photo;
                $photo->setName($_POST['fileNames'][$counter]);
                
                if($default === $counter){
                    $photo->setMain(TRUE);
                }else{
                    $photo->setMain(FALSE);
                }
                
                $photo->setPath($path);
                $photo->setTour($this->tour);
                $photo->setCreated(new \DateTime);

                $this->em->persist($photo);

                $counter++;
            }
        }

        $this->em->flush();
        $this->flashMessage('Tour has been added/updated.', 'success');
        
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }
}