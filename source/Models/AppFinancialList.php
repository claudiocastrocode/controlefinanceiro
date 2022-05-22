<?php

namespace Source\Models;

use CoffeeCode\DataLayer\DataLayer;

/**
 * AppFinancialList
 */
class AppFinancialList extends DataLayer
{    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct("app_financial_list", ["description", "amount", "cash_flow", "due_date", "repeat_type", "period"]);
    }
}
