<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

enum ErrorEnum: int
{
    case CODE_FILE_DELETION_ERROR = 1;
    case CODE_DIRECTORY_DELETION_ERROR = 2;
    case CODE_DIRECTORY_CREATION_ERROR = 3;
    case CODE_FILE_CREATION_ERROR = 4;
    case CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP = 6;
    case CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP_ENTRY = 8;
    case CODE_INVALID_UNIFIED_CLASS_NAME = 9;
    case CODE_INVALID_UNIFIED_NAMESPACE = 10;
    case CODE_INVALID_SHOP_EDITION = 11;
    case CODE_NO_UNIFIED_NAMESPACE_FOUND = 12;
    case CODE_SMARTY_COMPILE_DIR_PERMISSIONS = 13;
    case CODE_DIRECTORY_DELETION_TIMING_ERROR = 14;

    case CODE_MISSING_BACKWARDS_COMPATIBILITY_CLASS_MAP = 5;
    case CODE_INVALID_BACKWARDS_COMPATIBILITY_CLASS_MAP = 7;
}
