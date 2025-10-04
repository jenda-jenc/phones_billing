<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vyúčtování</title>
</head>
<body style="margin:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;color:#111827;">
    <div style="max-width:680px;margin:0 auto;padding:32px 16px;">
        <div style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,0.12);">
            <div style="background:linear-gradient(120deg,#1f2937,#4338ca);color:#ffffff;padding:28px 32px;">
                <p style="margin:0;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;opacity:0.7;">Senát Parlamentu ČR</p>
                <h1 style="margin:8px 0 0;font-size:26px;font-weight:600;">Vyúčtování služeb</h1>
            </div>
            <div style="padding:28px 32px 32px;">
                <p style="margin:0 0 16px;font-size:15px;">Vyúčtování <strong>{{ $person->name ?? 'uživateli' }}</strong></p>
                <p style="margin:0 0 28px;font-size:15px;line-height:1.6;">
                    přehled
                    @if($invoice)
                        čísla <strong>{{ $invoicePerson->phone }}</strong> za období <strong>{{ $invoice->billing_period }}</strong>
                    @else
                        za evidovaný záznam
                    @endif
                    .
                </p>

                @php
                    $totalWithoutVat = number_format($invoicePerson->total_without_vat, 2, ',', ' ');
                    $totalWithVat = number_format($invoicePerson->total_with_vat, 2, ',', ' ');
                    $limit = number_format($invoicePerson->limit, 2, ',', ' ');
                    $payable = number_format($invoicePerson->payable, 2, ',', ' ');
                    $vatAmount = max(0, $invoicePerson->total_with_vat - $invoicePerson->total_without_vat);
                    $vatFormatted = number_format($vatAmount, 2, ',', ' ');
                @endphp

                <table style="width:100%;border-collapse:collapse;margin-bottom:18px;text-align:center;">
                    <tr>
                        <th style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;padding:6px 8px;">
                            Celkem bez DPH
                        </th>
                        <th style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;padding:6px 8px;">
                            DPH
                        </th>
                        <th style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;padding:6px 8px;">
                            Celkem s DPH
                        </th>
                        <th style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;padding:6px 8px;">
                            Limit
                        </th>
                        <th style="font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#4338ca;padding:6px 8px;">
                            K úhradě
                        </th>
                    </tr>
                    <tr>
                        <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                            {{ $totalWithoutVat }} Kč
                        </td>
                        <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                            {{ $vatFormatted }} Kč
                        </td>
                        <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                            {{ $totalWithVat }} Kč
                        </td>
                        <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                            {{ $limit }} Kč
                        </td>
                        <td style="font-size:18px;font-weight:700;color:#4338ca;padding:6px 8px;">
                            {{ $payable }} Kč
                        </td>
                    </tr>
                </table>

                @if($lines->isNotEmpty())
                    <h2 style="margin:0 0 12px;font-size:17px;font-weight:600;color:#111827;">Detailní přehled</h2>
                    <table style="width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;">
                        <thead>
                            <tr style="background-color:#eef2ff;color:#3730a3;">
                                <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Služba</th>
                                <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Tarif</th>
                                <th style="padding:10px 12px;text-align:right;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Cena bez DPH</th>
                                <th style="padding:10px 12px;text-align:right;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Cena s DPH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                <tr style="background-color:#ffffff;border-bottom:1px solid #e5e7eb;">
                                    <td style="padding:10px 12px;font-size:14px;color:#111827;font-weight:500;">{{ $line->service_name }}</td>
                                    <td style="padding:10px 12px;font-size:14px;color:#374151;">{{ $line->tariff ?? '—' }}</td>
                                    <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_without_vat, 2, ',', ' ') }} Kč</td>
                                    <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_with_vat, 2, ',', ' ') }} Kč</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color:#f9fafb;font-weight:600;color:#111827;">
                                <td colspan="2" style="padding:10px 12px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;">Součet</td>
                                <td style="padding:10px 12px;text-align:right;font-size:14px;">{{ $totalWithoutVat }} Kč</td>
                                <td style="padding:10px 12px;text-align:right;font-size:14px;">{{ $totalWithVat }} Kč</td>
                            </tr>
                            <tr style="background-color:#eef2ff;font-weight:700;color:#4338ca;">
                                <td colspan="1" style="padding:10px 12px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;">Částka k úhradě</td>
                                <td colspan="1" style="padding:10px 12px;font-size:16px;"></td>
                                <td colspan="1" style="padding:10px 12px;font-size:16px;"></td>
                                <td colspan="1" style="padding:10px 12px;font-size:16px;">{{ $payable }} Kč</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if(!empty($invoicePerson->applied_rules))
                    <div style="margin-top:28px;">
                        <h2 style="margin:0 0 12px;font-size:17px;font-weight:600;color:#111827;">Aplikovaná pravidla</h2>
                        <ul style="margin:0;padding-left:18px;color:#374151;font-size:14px;line-height:1.6;">
                            @foreach($invoicePerson->applied_rules as $rule)
                                <li style="margin-bottom:8px;">
                                    <strong>{{ $rule['popis'] ?? 'Pravidlo' }}</strong>
                                    @if(!empty($rule['sluzba']))
                                        – {{ $rule['sluzba'] }}
                                    @endif
                                    @if(!empty($rule['cena_s_dph']))
                                        ({{ number_format($rule['cena_s_dph'], 2, ',', ' ') }} Kč s DPH)
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="margin-top:28px;padding:18px;border-radius:12px;background-color:#eef2ff;color:#4338ca;font-size:13px;line-height:1.7;">
                    <strong>Poznámka:</strong>
                    Částka k úhradě {{ $payable }} Kč zohledňuje nastavený limit {{ $limit }} Kč. V případě dotazů se prosím obraťte na svého nadřízeného nebo na oddělení audiovizuální podpory.
                </div>

{{--                <p style="margin:28px 0 0;font-size:15px;">S pozdravem,<br>Telefonní účtárna Senátu</p>--}}
            </div>
        </div>
    </div>
</body>
</html>
