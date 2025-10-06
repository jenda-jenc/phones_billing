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


        $billingLabel = null;

        if (!empty($invoice->billing_period)) {
            try {
                $billingLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $invoice->billing_period)
                    ->locale('cs')
                    ->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $billingLabel = $invoice->billing_period;
            }
        }
        $subject = 'Vyúčtování ' . $billingLabel;

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
