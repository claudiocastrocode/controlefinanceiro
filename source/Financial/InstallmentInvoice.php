<?php

namespace Source\Financial;

use Source\Models\AppFinancialList;

/**
 * InstallmentInvoice
 */
class InstallmentInvoice
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
     * createInstallment
     *
     * @return void
     */
    public function createInstallment()
    {
        $dueDate = $this->dateToSql;
        $dateFormat = $dueDate;
        $installment = "installment_primary";
        $period = "M";
        $referenceId = null;
        $cM = $this->launche["installment_number"];

        for ($countPeriod = 1; $countPeriod <= $cM; $countPeriod++) {

            $paidUnpaid = (strtotime(date("Y-m", strtotime($dueDate)))
                < strtotime(date("Y-m")) ? "paid" : "unpaid");

            $invoice = new AppFinancialList();
            $invoice->description = $this->launche["description"];
            $invoice->amount = $this->launche["amount"];
            $invoice->cash_flow = $this->launche["cash_flow"];
            $invoice->due_date = $dueDate;
            $invoice->repeat_type = $installment;
            $invoice->period = "month";
            $invoice->installment_number = $countPeriod;
            $invoice->status = $paidUnpaid;
            $invoice->reference_id = $referenceId;
            $invoice->save();

            $dueDate = monthShifter($dateFormat, $countPeriod, $period);
            $installment = "installment";

            if ($referenceId == null)
                $referenceId = $invoice->id;
        }
    }
    
    /**
     * editInstallment
     *
     * @return void
     */
    public function editInstallment()
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);

        if (
            strtotime(date("Y-m", strtotime($invoice->due_date)))
            <> strtotime(date("Y-m", strtotime($this->dateToSql)))
        ) {
            echo "Você não pode sair do mês corrente da parcela!";
            return;
        }

        $referenceId = $invoice;
        $invoices = (new AppFinancialList())
            ->find(
                "reference_id = :refid",
                "refid={$referenceId->reference_id}",
            )->fetch(true);

        # Edita apenas este lançamento
        if ($this->launche["which_to_update"] == "this") {
            $invoice->description = $this->launche["description"];
            $invoice->amount = $this->launche["amount"];
            $invoice->due_date = $this->dateToSql;
            $invoice->save();
        }

        # Edita este lançamento e os pŕoximos (exceto para a primeira parcela)
        if ($this->launche["which_to_update"] == "this_and_next") {
            $countPeriod = 1;
            $dueDate = $this->dateToSql;

            if ($invoices == true) {
                foreach ($invoices as $invoice) {
                    if (
                        strtotime(date("Y-m", strtotime($invoice->due_date)))
                        >= strtotime(date("Y-m", strtotime($this->dateToSql)))
                    ) {
                        $invoice->description = $this->launche["description"];
                        $invoice->amount = $this->launche["amount"];
                        $invoice->due_date = $dueDate;
                        $invoice->save();
                        $dueDate = monthShifter($this->dateToSql, $countPeriod, "M");
                        $countPeriod = $countPeriod + 1;
                    }
                }
            } else {
                $this->launche["which_to_update"] = "all";
                // *Se 'this_and_next' for 'installment_primary', então ele entra na condição 'all'!
            }
        }

        # Edita todos os lançamentos
        if ($this->launche["which_to_update"] == "all") {

            if ($invoice->reference_id != null) {
                $installmentPrimary = (new AppFinancialList())
                    ->findById($referenceId->reference_id);

                // Altera o mês e ano do input para o mês e ano da parcela primária
                $entryDay = date("d", strtotime($this->dateToSql));
                $lastDayMonth = date("t", strtotime($installmentPrimary->due_date));
                $yearAndMonth = date("Y-m", strtotime($installmentPrimary->due_date));
                if ($entryDay > $lastDayMonth) {
                    $this->dateToSql = $yearAndMonth . "-" . $lastDayMonth;
                } else {
                    $this->dateToSql = $yearAndMonth . "-{$entryDay}";
                }

                // Edita a parcela primária
                $this->installmentPrimary($installmentPrimary);
            } else {
                $invoices = (new AppFinancialList())
                    ->find(
                        "reference_id = :refid",
                        "refid={$invoice->id}",
                    )->fetch(true);

                $this->installmentPrimary($invoice);
            }

            $countPeriod = 1;
            $dueDate = $this->dateToSql;
            foreach ($invoices as $invoice) {
                $invoice->description = $this->launche["description"];
                $invoice->amount = $this->launche["amount"];
                $dueDate = monthShifter($this->dateToSql, $countPeriod, "M");
                $invoice->due_date = $dueDate;
                $invoice->save();
                $countPeriod = $countPeriod + 1;
            }
        }
    }
    
    /**
     * installmentPrimary
     *
     * @param  mixed $installmentPrimary
     * @return void
     */
    private function installmentPrimary(object $installmentPrimary): void
    {
        $installmentPrimary->description = $this->launche["description"];
        $installmentPrimary->amount = $this->launche["amount"];
        $installmentPrimary->due_date
            = date("Y-m", strtotime($installmentPrimary->due_date))
            . "-"
            . date("d", strtotime($this->dateToSql));
        $installmentPrimary->save();
    }
    
    /**
     * removeInstallment
     *
     * @return void
     */
    public function removeInstallment()
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);
        $invoices = $invoice
            ->find(
                "reference_id = :refid AND repeat_type = :rtp",
                "refid={$invoice->reference_id}&rtp=installment"
            )->fetch(true);

        switch ($this->launche["which_to_update"]):
                # Remove apenas este lançamento
            case "this":
                $invoice->destroy();
                break;

                # Remove este lançamento e os pŕoximos
            case "this_and_next":
                if (!$invoices) {
                    $installmentPrimary = $invoice->findById($invoice->id);
                    $installmentPrimary->destroy();
                } else {
                    foreach ($invoices as $listInvoice) {
                        if (
                            strtotime(date("Y-m", strtotime($listInvoice->due_date)))
                            >= strtotime(date("Y-m", strtotime($this->dateToSql)))
                        ) {
                            $listInvoice->destroy();
                        }
                    }
                }
                break;

                # Remove todos os lançamentos
            case "all":
                $installmentPrimary = ($invoice->findById(($invoice->reference_id ?? $invoice->id)));
                $installmentPrimary->destroy();
                break;
        endswitch;
    }
}
