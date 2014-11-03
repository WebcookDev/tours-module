<?php

/**
 * This file is part of the Cars module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\CarsModule;

use WebCMS\CarsModule\Entity\Car;

/**
 * Description of CarsPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class CarsPresenter extends BasePresenter
{
    private $repository;

    private $brandRepository;

    private $brand;
    
    private $car;

    private $cars;

    private $cpp;
    
    protected function startup() 
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\CarsModule\Entity\Car');
        $this->brandRepository = $this->em->getRepository('WebCMS\CarsModule\Entity\Brand');
    }

    protected function beforeRender()
    {
        parent::beforeRender(); 
    }

    public function handleLoadCars($p) 
    {
        $this->cars = $this->repository->findAll();

        $json = array();
        foreach ($this->cars as $car) {
            $json[] = array(
                'fullName' => $car->getFullname(),
                'motorization' => $car->getMotorization(),
                'id' => $car->getId(),
                'url' => $this->link('default', array(
                    'path' => $this->actualPage->getPath(),
                    'abbr' => $this->abbr,
                    'parameters' => array($car->getSlug())
                )),
                'name' => $car->getName(),
                'drivenKm' => $car->getDrivenKm(),
                'price' => $car->getPrice(),
                'sold' => $car->getSold(),
                'photo' => \WebCMS\Helpers\SystemHelper::thumbnail($car->getDefaultPhoto()->getPath(), 'carBig_'),
                'brand' => $car->getBrand()->getSlug()
            );
        }

        $this->payload->data = $json;
        $this->sendPayload();
    }

    public function actionDefault($id)
    {
        $parameters = $this->getParameter();
        if (count($parameters['parameters']) > 0) {
            $slug = $parameters['parameters'][0];
            $this->brand = $this->brandRepository->findOneBy(array(
                'slug' => $slug
            ));
            
            if (!$this->brand) {
                $this->car = $this->repository->findOneBy(array(
                    'slug' => $slug,
                    'hide' => false
                ));
            }
        }
    }

    public function renderDefault($id)
    {   
        if ($this->car) {
            $this->template->car = $this->car;
            $this->template->similarCount = count($this->repository->findBy(array(
                'brand' => $this->car->getBrand(),
                'hide' => false
            ))) -1;

            $this->template->seoTitle = $this->car->getFullname() . ' - ' . $this->actualPage->getMetaTitle();
            $this->template->carPrev = $this->repository->findPrevious($this->car);
            $this->template->carNext = $this->repository->findNext($this->car);
            $this->template->setFile(APP_DIR . '/templates/cars-module/Cars/detail.latte');
        } else {
            $topCar = $this->em->getRepository('\WebCMS\CarsModule\Entity\Car')->findOneBy(array(
                'top' => true
            ));
            $this->template->topCar = $topCar ? $topCar : null;
        }
        
        $this->template->brandPage = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Cars',
            'presenter' => 'Brands'
        ));

        $this->template->page = $this->getParameter('p') ? $this->getParameter('p') : 0;
        $this->template->cars = $this->cars;
        $this->template->brand = $this->brand;
        $this->template->id = $id;
    }

    public function homepageBox($context)
    {
        $template = $context->createTemplate();
        $template->cars = $context->em->getRepository('WebCMS\CarsModule\Entity\Car')->findBy(array(
            'hide' => false,
            'homepage' => true
        ), array('dateIn' => 'DESC'));
        $template->carPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Cars',
            'presenter' => 'Cars'
        ));
        $topCar = $context->em->getRepository('\WebCMS\CarsModule\Entity\Car')->findOneBy(array(
            'top' => true
        ));
        $template->topCar = $topCar ? $topCar : null;
        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/cars-module/Cars/homepageBox.latte');

        return $template;  
    }

    public function brandsBox($context)
    {
        $template = $context->createTemplate();
        $template->brands = $context->em->getRepository('WebCMS\CarsModule\Entity\Brand')->findAll(array(
            'hide' => false
        ));
        $template->carPage = $context->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Cars',
            'presenter' => 'Cars'
        ));
        $template->abbr = $context->abbr;
        $template->setFile(APP_DIR . '/templates/cars-module/Brands/brandsBox.latte');

        return $template;
    }

}
