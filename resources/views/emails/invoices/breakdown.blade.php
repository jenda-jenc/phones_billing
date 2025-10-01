<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vyúčtování</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <h1 style="font-size: 20px;">Dobrý den {{ $person->name ?? 'uživateli' }},</h1>
    <p>
        posíláme vám přehled vyúčtování
        @if($invoice)
            za fakturu <strong>#{{ $invoice->id }}</strong>
            @if($invoice->source_filename)
                ({{ $invoice->source_filename }})
            @endif
        @else
            za evidovaný záznam
        @endif
        .
    </p>

    <h2 style="font-size: 18px; margin-top: 20px;">Souhrn</h2>
    <ul>
        <li><strong>Telefon:</strong> {{ $invoicePerson->phone }}</li>
        <li><strong>Celkem bez DPH:</strong> {{ number_format($invoicePerson->total_without_vat, 2, ',', ' ') }} Kč</li>
        <li><strong>Celkem s DPH:</strong> {{ number_format($invoicePerson->total_with_vat, 2, ',', ' ') }} Kč</li>
        <li><strong>Limit:</strong> {{ number_format($invoicePerson->limit, 2, ',', ' ') }} Kč</li>
        <li><strong>K úhradě:</strong> {{ number_format($invoicePerson->payable, 2, ',', ' ') }} Kč</li>
    </ul>

    @if($lines->isNotEmpty())
        <h2 style="font-size: 18px; margin-top: 20px;">Detailní položky</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border-bottom: 1px solid #D1D5DB; text-align: left; padding: 8px;">Služba</th>
                    <th style="border-bottom: 1px solid #D1D5DB; text-align: left; padding: 8px;">Tarif</th>
                    <th style="border-bottom: 1px solid #D1D5DB; text-align: left; padding: 8px;">Skupina</th>
                    <th style="border-bottom: 1px solid #D1D5DB; text-align: right; padding: 8px;">Cena bez DPH</th>
                    <th style="border-bottom: 1px solid #D1D5DB; text-align: right; padding: 8px;">Cena s DPH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    <tr>
                        <td style="padding: 6px; border-bottom: 1px solid #E5E7EB;">{{ $line->service_name }}</td>
                        <td style="padding: 6px; border-bottom: 1px solid #E5E7EB;">{{ $line->tariff ?? '—' }}</td>
                        <td style="padding: 6px; border-bottom: 1px solid #E5E7EB;">{{ $line->group_name ?? '—' }}</td>
                        <td style="padding: 6px; border-bottom: 1px solid #E5E7EB; text-align: right;">{{ number_format($line->price_without_vat, 2, ',', ' ') }} Kč</td>
                        <td style="padding: 6px; border-bottom: 1px solid #E5E7EB; text-align: right;">{{ number_format($line->price_with_vat, 2, ',', ' ') }} Kč</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($invoicePerson->applied_rules))
        <h2 style="font-size: 18px; margin-top: 20px;">Aplikovaná pravidla</h2>
        <ul>
            @foreach($invoicePerson->applied_rules as $rule)
                <li>
                    {{ $rule['popis'] ?? 'Pravidlo' }}
                    @if(!empty($rule['sluzba']))
                        – {{ $rule['sluzba'] }}
                    @endif
                    @if(!empty($rule['cena_s_dph']))
                        ({{ number_format($rule['cena_s_dph'], 2, ',', ' ') }} Kč s DPH)
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <p style="margin-top: 24px;">Pokud máte k vyúčtování dotazy, obraťte se prosím na podporu.</p>
    <p>S pozdravem,<br>Telefonní účtárna</p>
</body>
</html>
