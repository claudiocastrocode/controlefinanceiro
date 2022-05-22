<?php

namespace Source\Financial;

use Source\Models\AppFinancialList;

/**
 * FixedInvoice
 */
class FixedInvoice
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

        (isset($this->launche["due_date"])
            ? $this->dateToSql = convertDateToSql($this->launche["due_date"])
            : $this->dateToSql = (isset($this->launche["filter"])
                ? convertDateToSql($this->launche["filter"])
                : null)
        );
    }
    
    /**
     * createFixed
     *
     * @return void
     */
    public function createFixed(): void
    {
        $dueDate = $this->dateToSql;
        $dateFormat = $dueDate;
        $fixed = "fixed_mirror";
        ($this->launche["period"] == "year" ? $period = "Y" : $period = "M");
        $referenceId = null;
        $paidUnpaid = "unpaid";
        $cM = 4;

        # Define o intervalo entre os meses e soma + 3 à frente da data atual no looping
        if (strtotime(date("Y-m", strtotime($dueDate))) < strtotime(date("Y-m"))) {
            $start = date("Y-m", strtotime($dueDate));
            $end = date("Y-m", strtotime(date("Y-m-d")));
            $cM = differenceBetweenDates($start, $end, $this->launche["period"]) + 4;
        }

        for ($countPeriod = 0; $countPeriod <= $cM; $countPeriod++) {
            if ($referenceId != null) {
                $paidUnpaid = (strtotime(date("Y-m", strtotime($dueDate))) < strtotime(date("Y-m")) ? "paid" : "unpaid");
            }

            $invoice = new AppFinancialList();
            $invoice->description = $this->launche["description"];
            $invoice->amount = $this->launche["amount"];
            $invoice->cash_flow = $this->launche["cash_flow"];
            $invoice->due_date = $dueDate;
            $invoice->repeat_type = $fixed;
            $invoice->period = $this->launche["period"];
            $invoice->status = $paidUnpaid;
            $invoice->reference_id = $referenceId;
            $invoice->save();

            if ($referenceId != null) {
                $dueDate = monthShifter($dateFormat, $countPeriod, $period);
            }

            if ($invoice->reference_id == null) {
                $fixed = "fixed";
                $referenceId = $invoice->id;
            }
        }
    }
    
    /**
     * editFixed
     *
     * @return void
     */
    public function editFixed(): void
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);

        if (
            strtotime(date("Y-m", strtotime($invoice->due_date)))
            <> strtotime(date("Y-m", strtotime($this->dateToSql)))
        ) {
            echo "Você não pode sair do mês corrente do lançamento fixo!";
            return;
        }

        $referenceId = $invoice;
        $invoices = (new AppFinancialList())
            ->find(
                "reference_id = :refid",
                "refid={$referenceId->reference_id}",
            )->fetch(true);

        $fixedMirror = (new AppFinancialList())->findById($referenceId->reference_id);

        # Edita apenas este lançamento
        if ($this->launche["which_to_update"] == "this") {
            $invoice->description = $this->launche["description"];
            $invoice->amount = $this->launche["amount"];
            $invoice->due_date = $this->dateToSql;
            $invoice->save();
        }

        # Edita este lançamento e os pŕoximos
        if ($this->launche["which_to_update"] == "this_and_next") {
            $countPeriod = 1;
            $dueDate = $this->dateToSql;

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

            // Edita o lançamento espelho
            $this->fixedMirror($fixedMirror);
        }

        # Edita todos os lançamentos
        if ($this->launche["which_to_update"] == "all") {

            // Edita o lançamento espelho
            $this->fixedMirror($fixedMirror);

            // Altera o mês e ano do input para o mês e ano do lançamento espelho
            $entryDay = date("d", strtotime($this->dateToSql));
            $lastDayMonth = date("t", strtotime($fixedMirror->due_date));
            $yearAndMonth = date("Y-m", strtotime($fixedMirror->due_date));
            if ($entryDay > $lastDayMonth) {
                $this->dateToSql = $yearAndMonth . "-" . $lastDayMonth;
            } else {
                $this->dateToSql = $yearAndMonth . "-{$entryDay}";
            }

            $countPeriod = 1;
            $dueDate = $this->dateToSql;
            foreach ($invoices as $invoice) {
                $invoice->description = $this->launche["description"];
                $invoice->amount = $this->launche["amount"];
                $invoice->due_date = $dueDate;
                $invoice->save();
                $dueDate = monthShifter($this->dateToSql, $countPeriod, "M");
                $countPeriod = $countPeriod + 1;
            }
        }
    }
    
    /**
     * normalizeFixed
     *
     * @return void
     */
    public function normalizeFixed(): void
    {
        // Obtém as datas mais recentes dos lançamentos agrupados por reference_id
        $listRecentDate = (new AppFinancialList())
            ->find(
                "repeat_type = :rtp AND reference_id != :ref",
                "rtp=fixed&ref=null",
                "reference_id, period, MAX(due_date) AS recent_due_date"
            )->group("reference_id")->fetch(true);

        if (
            !$listRecentDate
            || strtotime(date("Y-m", strtotime($this->dateToSql)))
            <= strtotime(date("Y-m"))
        ) {
            return;
        }

        foreach ($listRecentDate as $list) {
            $recentDate = $list->data;

            if (
                strtotime(date("Y-m", strtotime($this->dateToSql)))
                > strtotime(date("Y-m", strtotime($recentDate->recent_due_date)))
                && strtotime(date("Y-m", strtotime($this->dateToSql)))
                <= strtotime(date("Y-m", strtotime(monthShifter(date("Y-m"), 10, "Y"))))
            ) {
                // Obtém o registro espelho dos lançamentos
                $fixedMirror = (new AppFinancialList())->findById($recentDate->reference_id);

                if ($fixedMirror->repeat_type == "fixed_mirror") {
                    $cM = 4;
                    ($recentDate->period == "year" ? $period = "Y" : $period = "M");
                    $dueDate = monthShifter($recentDate->recent_due_date, 1, $period);
                    $dateFormat = monthShifter($recentDate->recent_due_date, 1, $period);

                    // Define o intervalo entre os meses e soma + 3 à frente da data atual no looping
                    $start = date("Y-m", strtotime($recentDate->recent_due_date));
                    $end = date("Y-m", strtotime($this->dateToSql));
                    $cM = differenceBetweenDates($start, $end, $recentDate->period) + 3;

                    for ($countPeriod = 1; $countPeriod <= $cM; $countPeriod++) {
                        $invoice = new AppFinancialList();
                        $invoice->description = $fixedMirror->description;
                        $invoice->amount = $fixedMirror->amount;
                        $invoice->cash_flow = $fixedMirror->cash_flow;
                        $invoice->due_date = $dueDate;
                        $invoice->repeat_type = "fixed";
                        $invoice->period = $fixedMirror->period;
                        $invoice->reference_id = $fixedMirror->id;
                        $invoice->save();
                        $dueDate = monthShifter($dateFormat, $countPeriod, $period);
                    }
                }
            }
        }
    }
    
    /**
     * fixedMirror
     *
     * @param  mixed $fixedMirror
     * @return void
     */
    private function fixedMirror(object $fixedMirror): void
    {
        $fixedMirror->description = $this->launche["description"];
        $fixedMirror->amount = $this->launche["amount"];
        $fixedMirror->due_date = $fixedMirror->due_date;
        $fixedMirror->save();
    }
    
    /**
     * removeFixed
     *
     * @return void
     */
    public function removeFixed(): void
    {
        $invoice = (new AppFinancialList())->findById($this->launche["id"]);
        $invoices = $invoice
            ->find(
                "reference_id = :refid AND repeat_type = :rtp",
                "refid={$invoice->reference_id}&rtp=fixed"
            )->fetch(true);

        switch ($this->launche["which_to_update"]):
                # Remove apenas este lançamento
            case "this":
                $invoice->destroy();
                break;

                # Remove este lançamento e os pŕoximos
            case "this_and_next":
                foreach ($invoices as $listInvoice) {
                    if (
                        strtotime(date("Y-m", strtotime($listInvoice->due_date)))
                        >= strtotime(date("Y-m", strtotime($this->dateToSql)))
                    ) {
                        $listInvoice->destroy();
                    }
                }
                // Se o lançamento for o primeiro após o espelho, então remove o espelho
                $invoiceMirror = $invoice->findById($invoice->reference_id);
                if ($listInvoice->find(
                    "reference_id = :refid AND repeat_type = :rtp",
                    "refid={$invoice->reference_id}&rtp=fixed"
                )->count() >= 1) {
                    $invoiceMirror->repeat_type = "fixed_mirror_stopped";
                    $invoiceMirror->save();
                } else {
                    $invoiceMirror->destroy();
                }
                break;

                # Remove todos os lançamentos
            case "all":
                $invoiceMirror = $invoice->findById($invoice->reference_id);
                $invoiceMirror->destroy();
                break;
        endswitch;
    }
}
