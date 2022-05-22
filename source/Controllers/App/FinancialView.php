<?php

namespace Source\Controllers\App;

use Source\Core\Controller;
use Source\Financial\FixedInvoice;
use Source\Models\AppFinancialList;

/**
 * FinancialView
 */
class FinancialView extends Controller
{    
    /**
     * __construct
     *
     * @param  mixed $router
     * @return void
     */
    public function __construct($router)
    {
        parent::__construct($router);
    }
    
    /**
     * home
     *
     * @return void
     */
    public function home()
    {
        echo "<h1>Home Page!</h1>";
    }
    
    /**
     * incomes
     *
     * @param  mixed $data
     * @return void
     */
    public function incomes(?array $data)
    {
        echo "<h1>Receitas</h1>";
    }
    
    /**
     * expenses
     *
     * @param  mixed $data
     * @return void
     */
    public function expenses(?array $data)
    {
        echo "<h1>Despesas</h1>";

        if (isset($data["filter"])) {
            (new FixedInvoice($data))->normalizeFixed();
            list($m, $y) = explode("-", $data["filter"]);
        } else {
            list($m, $y) = explode("-", date("m-Y"));
        }

        $financialList = new AppFinancialList();

        $invoices = $financialList->find(
            "year(due_date) = :y AND month(due_date) = :m",
            "y={$y}&m={$m}"
        )->fetch(true);

        if ($invoices) {
            foreach ($invoices as $list) :
                echo $list->description . " " . $list->amount . "etc...<br>";
            endforeach;
        }
    }
    
    /**
     * nofFound
     *
     * @param  mixed $data
     * @return void
     */
    public function nofFound($data)
    {
        echo "<h1>Ops! Erro [{$data["errcode"]}]<br>Página não encontrada!</h1>";
    }
}
