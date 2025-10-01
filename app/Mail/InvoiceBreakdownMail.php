<?php

namespace App\Mail;

use App\Models\InvoicePerson;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceBreakdownMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public InvoicePerson $invoicePerson;

    public function __construct(InvoicePerson $invoicePerson)
    {
        $this->invoicePerson = $invoicePerson->loadMissing(['invoice', 'person', 'lines']);
    }

    public function build(): self
    {
        $person = $this->invoicePerson->person;
        $invoice = $this->invoicePerson->invoice;

        $subject = 'Vyúčtování #' . ($invoice?->id ?? $this->invoicePerson->id);

        return $this->subject($subject)
            ->view('emails.invoices.breakdown')
            ->with([
                'invoicePerson' => $this->invoicePerson,
                'person' => $person,
                'invoice' => $invoice,
                'lines' => $this->invoicePerson->lines,
            ]);
    }
}
