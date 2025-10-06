<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InvoiceDebtorsSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    /**
     * @var Collection<int, \App\Models\InvoicePerson>
     */
    public Collection $debtors;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice->loadMissing([
            'people.person',
            'people.person.phones',
            'people.lines',
        ]);

        $this->debtors = $this->invoice->people;
    }

    public function build(): self
    {
        $subjectParts = ['Souhrn vyúčtování'];

        if ($this->invoice->id !== null) {
            $subjectParts[] = $this->invoice->billing_period;
        }

        return $this->subject(implode(' ', $subjectParts))
            ->view('emails.invoices.debtors_summary')
            ->with([
                'invoice' => $this->invoice,
                'debtors' => $this->debtors,
            ]);
    }
}
