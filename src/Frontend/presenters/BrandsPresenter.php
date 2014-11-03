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
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class BrandsPresenter extends BasePresenter
{
    private $repository;

    private $brandRepository;

    private $cars = array();

    private $car;
    
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


    public function actionDefault($id)
    {
        $parameters = $this->getParameter();
        if (count($parameters['parameters']) > 0) {
            $brandSlug = $parameters['parameters'][0];

            if (isset($parameters['parameters'][1])) {
                $this->car = $this->repository->findOneBy(array(
                    'slug' => $parameters['parameters'][1],
                    'hide' => false
                ));
            }

            $brand = $this->brandRepository->findOneBySlug($brandSlug);

            $this->cars = $this->repository->findBy(array(
                'brand' => $brand,
                'hide' => false
            ), array('dateIn' => 'DESC'));
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

            $this->template->seoTitle = $this->actualPage->getMetaTitle(). ' - ' . $this->car->getName();
            $this->template->carPrev = $this->repository->findPrevious($this->car);
            $this->template->carNext = $this->repository->findNext($this->car);
            $this->template->setFile(APP_DIR . '/templates/cars-module/Brands/detail.latte');
        }

        $this->template->brandPage = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Cars',
            'presenter' => 'Brands'
        ));

        $this->template->carPage = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'moduleName' => 'Cars',
            'presenter' => 'Cars'
        ));
        $this->template->cars = $this->cars;
        $this->template->id = $id;
    }
}