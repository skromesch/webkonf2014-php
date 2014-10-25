<?php

return array(
    'router' => array(
        'routes' => array(
            'contact_service' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route'    => '/contact[/:id]',
                        'defaults' => array(
                            'controller' => 'ContactService\Controller\Contact'
                        ),
                    ),
             ),
        )
     ),
    'view_manager' => array( //Add this config
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ContactService\Controller\Contact' => 'ContactService\Controller\ContactController',
        ),
    ),     
);
