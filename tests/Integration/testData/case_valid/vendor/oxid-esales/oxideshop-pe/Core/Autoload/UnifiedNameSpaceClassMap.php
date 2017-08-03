<?php

return [
    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInCommunityAndProfessionalEdition::class,
        'isAbstract'       => false,
        'isInterface'      => false
    ],
    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInAllEditions::class,
        'isAbstract'       => false,
        'isInterface'      => false
    ],
    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
        'editionClassName' => \OxidEsales\EshopProfessional\AbstractClassExistsInAllEditions::class,
        'isAbstract'       => true,
        'isInterface'      => false
    ],
];