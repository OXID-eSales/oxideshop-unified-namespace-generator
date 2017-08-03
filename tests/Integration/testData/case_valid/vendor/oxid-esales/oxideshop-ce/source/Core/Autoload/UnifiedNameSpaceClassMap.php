<?php

return [
    'OxidEsales\Eshop\ClassExistsOnlyInCommunityEdition'            => [
        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsOnlyInCommunityEdition::class,
        'isAbstract'       => false,
        'isInterface'      => false
    ],
    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsInCommunityAndProfessionalEdition::class,
        'isAbstract'       => false,
        'isInterface'      => false
    ],
    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsInAllEditions::class,
        'isAbstract'       => false,
        'isInterface'      => false
    ],
    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
        'editionClassName' => \OxidEsales\EshopCommunity\AbstractClassExistsInAllEditions::class,
        'isAbstract'       => true,
        'isInterface'      => false
    ],
];