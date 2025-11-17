<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Entities;

use Okay\Core\Entity\Entity;

class RulesEntity extends Entity
{
    protected static $fields = [
        'id',
        'category_id',
        'amount',
    ];

    protected static $table = 'okaycms__minimal_order_amount__rules';
    protected static $tableAlias = 'oc_moa_rules';
}
