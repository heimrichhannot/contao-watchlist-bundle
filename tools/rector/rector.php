<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../src',
    ])
    ->withImportNames(true, true,false, true)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSets([
//        SetList::TYPE_DECLARATION,
        SetList::PHP_74,
        LevelSetList::UP_TO_PHP_74,
        SymfonySetList::SYMFONY_44,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        ContaoSetList::CONTAO_49,
        ContaoSetList::FQCN,
        ContaoLevelSetList::UP_TO_CONTAO_49,
    ]);
