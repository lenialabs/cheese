<?php


/**
 * Cheese Assets
 *
 * @link
 * @copyright Copyright (c) 2018 Lenia Labs
 * @license
 */


namespace LeniaLabs\Cheese;


use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


    public function getConfig ()
    {
        return include __DIR__ . '/cheese.module.config.php';
    }


    public function onBootstrap (MvcEvent $event)
    {
        $event->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'addRoutes'), 100);
    }


    public function addRoutes ($event)
    {
        $event->getRouter()->addRoute('cheese-assets-install', array(
            'type' => 'literal',
            'options' => array(
                'route' => '/cheese/assets/install',
                'defaults' => array(
                    'controller' => Controller\Cheese::class,
                    'action' => 'installAssets'
                ),
            ),
        ));
    }

}
