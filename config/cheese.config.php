<?php


/**
 * Cheese Assets
 *
 * @link
 * @copyright Copyright (c) 2018 Lenia Labs
 * @license
 */


namespace Cheese;


return array(

    'controllers' => array(
        'factories' => array(
            Controller\Cheese::class => function ($controllerManager) {
                return new Controller\CheeseController($controllerManager->getServiceLocator());
            },
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/default.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

);
