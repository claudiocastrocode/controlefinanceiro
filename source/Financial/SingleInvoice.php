<?php

namespace Source\Financial;

use Source\Models\AppFinancialList;

/**
 * SingleInvoice
 */
class SingleInvoice
{
    protected $launche;
    protected $dateToSql;
    
    /**
     * __construct
     *
     * @param  array $launche
     * @return void
     */
    public function __construct(array $launche)
    {
        $this->launche = $launche;
        $this->dateToSql = (isset($this->launche["due_date"])
            ? convertDateToSql($this->launche["due_date"])
            : null);
    }
    
    /**
     * createSingle
     *
     * @return void
     */
    public function createSingle()
    {
        $invoice = new AppFinancialList();
        $invoice->description = $this->launche["description"];
        $invoice->amount = $this->launche["amount"];
        $invoice->cash_flow = $this->launche["cash_flow"];
        $invoice->due_date = $this->dateToSql;
        $invoice->repeat_type = "single";
        $invoice->period = "month";
        $invoice->status = (strtotime(date("Y-m", strtotime($this->dateToSql)))
            < strtotime(date("Y-m")) ? "paid" : "unpaid");
        $invoice->save();
    }
    
    /**
     * editSingle
     *
     * @return void
     */
    public function editSingle()
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);
        $invoice->description = $this->launche["description"];
        $invoice->amount = $this->launche["amount"];
        $invoice->due_date = $this->dateToSql;
        $invoice->save();
    }
    
    /**
     * removeSingle
     *
     * @return void
     */
    public function removeSingle()
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);
        $invoice->destroy();
    }
}
