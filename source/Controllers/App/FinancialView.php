<?php

namespace Source\Controllers\App;

use Source\Core\Controller;
use Source\Financial\FixedInvoice;
use Source\Models\AppFinancialList;

class FinancialView extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function home()
    {
        echo "<h1>Home Page!</h1>";
    }

    public function incomes(?array $data)
    {
        echo "<h1>Receitas</h1>";
    }

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
                echo $list->description . " " . $list->amount . "<br>";
            endforeach;
        }
    }

    public function nofFound($data)
    {
        echo "<h1>Ops! Erro [{$data["errcode"]}]<br>Página não encontrada!</h1>";
    }
}
