<?php

namespace Source\Controllers\App;

use Source\Core\Controller;
use Source\Financial\FixedInvoice;
use Source\Financial\InstallmentInvoice;
use Source\Financial\SingleInvoice;

/**
 * FinancialAuth
 */
class FinancialAuth extends Controller
{
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function createInvoice(array $data)
    {
        # Validações de Input
        switch ($data["repeat_type"]) {
            case "single":
                (new SingleInvoice($data))->createSingle();
                break;
            case "fixed":
                (new FixedInvoice($data))->createFixed();
                break;
            case "installment";
                (new InstallmentInvoice($data))->createInstallment();
                break;
            default:
                echo "O tipo deve ser Único, Fixo ou Parcelado";
        }
    }
    
    /**
     * editInvoice
     *
     * @param  array $data
     * @return void
     */
    public function editInvoice(array $data)
    {
        # Validações de Input
        switch ($data["repeat_type"]) {
            case "single":
                (new SingleInvoice($data))->editSingle();
                break;
            case "fixed":
                (new FixedInvoice($data))->editFixed();
                break;
            case "installment":
                (new InstallmentInvoice($data))->editInstallment();
                break;
            default:
                echo "O tipo deve ser Único, Fixo ou Parcelado";
        }
    }
    
    /**
     * removeInvoice
     *
     * @param  array $data
     * @return void
     */
    public function removeInvoice(array $data)
    {
        # Validações de Input
        switch ($data["repeat_type"]) {
            case "single":
                (new SingleInvoice($data))->removeSingle();
                break;
            case "fixed":
                (new FixedInvoice($data))->removeFixed();
                break;
            case "installment":
                (new InstallmentInvoice($data))->removeInstallment();
                break;
        }
    }

    private function validacoesDeInput()
    {
        # code...
    }
}
