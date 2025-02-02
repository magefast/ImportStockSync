<?php

namespace Strekoza\ImportStockSync\Api;

interface SettingsInterface
{
    public const ATTRIBUTE_SPECIAL_PRICE = 'special_price';
    public const ATTRIBUTE_SPECIAL_FROM = 'special_from_date';
    public const ATTRIBUTE_SPECIAL_TO = 'special_to_date';

    public const ATTRIBUTE_CENA_ZAKUPKI = 'cena_zakupki';
    public const ATTRIBUTE_PROFIT = 'profit';
    public const ATTRIBUTE_ROZETKA_PRICE = 'rozetka_price';
}
