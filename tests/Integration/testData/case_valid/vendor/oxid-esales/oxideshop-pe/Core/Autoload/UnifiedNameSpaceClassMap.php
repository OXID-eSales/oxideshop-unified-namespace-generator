<?php

return [
    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInCommunityAndProfessionalEdition::class,
        'isAbstract'       => false,
        'isInterface'      => false,
        'isDeprecated'     => false
    ],
    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInAllEditions::class,
        'isAbstract'       => false,
        'isInterface'      => false,
        'isDeprecated'     => false
    ],
    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
        'editionClassName' => \OxidEsales\EshopProfessional\AbstractClassExistsInAllEditions::class,
        'isAbstract'       => true,
        'isInterface'      => false,
        'isDeprecated'     => false
    ],
];