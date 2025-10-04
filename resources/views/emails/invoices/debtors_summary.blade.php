<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Souhrn dlužníků</title>
</head>
<body style="margin:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;color:#111827;">
    <div style="max-width:760px;margin:0 auto;padding:32px 16px;">
        <div style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,0.12);">
            <div style="background:linear-gradient(120deg,#1f2937,#4338ca);color:#ffffff;padding:28px 32px;">
                <p style="margin:0;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;opacity:0.7;">Senát Parlamentu ČR</p>
                <h1 style="margin:8px 0 0;font-size:26px;font-weight:600;">Souhrn dlužníků vyúčtování</h1>
            </div>
            <div style="padding:30px 32px 36px;">
                @php
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

                    $totalWithoutVat = number_format($debtors->sum('total_without_vat'), 2, ',', ' ');
                    $totalWithVat = number_format($debtors->sum('total_with_vat'), 2, ',', ' ');
                    $totalPayable = number_format($debtors->sum('payable'), 2, ',', ' ');
                @endphp

                <p style="margin:0 0 18px;font-size:15px;line-height:1.6;">
                    Posíláme souhrnný přehled všech osob s částkou k úhradě
                    @if($billingLabel)
                        za období {{ \Illuminate\Support\Str::lower($billingLabel) }}
                    @endif
                    .
                </p>

                <div style="margin-bottom:28px;border-radius:14px;background-color:#eef2ff;padding:18px 20px;">
                    <div style="display:flex;flex-wrap:wrap;gap:18px;">
                        <div style="flex:1 1 180px;">
                            <div style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6366f1;">Počet dlužníků</div>
                            <div style="font-size:22px;font-weight:700;color:#312e81;">{{ $debtors->count() }}</div>
                        </div>
                    </div>
                </div>

                @foreach($debtors as $invoicePerson)
                    @php
                        $person = $invoicePerson->person;
                        $displayName = $person->name ?? $invoicePerson->phone ?? ('Osoba #'.$invoicePerson->id);
                        $phone = $invoicePerson->phone ?? ($person?->phones->first()?->phone ?? '—');
                        $personTotalWithoutVat = number_format($invoicePerson->total_without_vat, 2, ',', ' ');
                        $personTotalWithVat = number_format($invoicePerson->total_with_vat, 2, ',', ' ');
                        $personLimit = number_format($invoicePerson->limit, 2, ',', ' ');
                        $personPayable = number_format($invoicePerson->payable, 2, ',', ' ');
                        $vatAmount = max(0, $invoicePerson->total_with_vat - $invoicePerson->total_without_vat);
                        $vatFormatted = number_format($vatAmount, 2, ',', ' ');
                    @endphp

                    <div style="margin-bottom:32px;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                        <div style="background-color:#f9fafb;padding:18px 20px;">
                            <h2 style="margin:0;font-size:18px;font-weight:600;color:#1f2937;">{{ $displayName }}</h2>
                            <div style="margin-top:4px;font-size:13px;color:#6b7280;">Telefon: {{ $phone }}</div>
                        </div>
                        <div style="padding:20px 22px;">
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
                                        {{ $personTotalWithoutVat }} Kč
                                    </td>
                                    <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                                        {{ $vatFormatted }} Kč
                                    </td>
                                    <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                                        {{ $personTotalWithVat }} Kč
                                    </td>
                                    <td style="font-size:16px;font-weight:600;color:#111827;padding:6px 8px;">
                                        {{ $personLimit }} Kč
                                    </td>
                                    <td style="font-size:18px;font-weight:700;color:#4338ca;padding:6px 8px;">
                                        {{ $personPayable }} Kč
                                    </td>
                                </tr>
                            </table>

                            {{--                            <table style="width:100%;border-collapse:separate;border-spacing:0 10px;margin-bottom:18px;">--}}
{{--                                <tbody>--}}
{{--                                    <tr>--}}
{{--                                        <td style="width:55%;font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">Celkem bez DPH</td>--}}
{{--                                        <td style="text-align:right;font-size:16px;font-weight:600;color:#111827;">{{ $personTotalWithoutVat }} Kč</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">DPH</td>--}}
{{--                                        <td style="text-align:right;font-size:16px;font-weight:600;color:#111827;">{{ $vatFormatted }} Kč</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">Celkem s DPH</td>--}}
{{--                                        <td style="text-align:right;font-size:16px;font-weight:600;color:#111827;">{{ $personTotalWithVat }} Kč</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td style="font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">Limit</td>--}}
{{--                                        <td style="text-align:right;font-size:16px;font-weight:600;color:#111827;">{{ $personLimit }} Kč</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td style="font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#4338ca;">K úhradě</td>--}}
{{--                                        <td style="text-align:right;font-size:18px;font-weight:700;color:#4338ca;">{{ $personPayable }} Kč</td>--}}
{{--                                    </tr>--}}
{{--                                </tbody>--}}
{{--                            </table>--}}

{{--                            @if($invoicePerson->lines->isNotEmpty())--}}
{{--                                <h3 style="margin:0 0 10px;font-size:15px;font-weight:600;color:#1f2937;">Detailní přehled služeb</h3>--}}
{{--                                <table style="width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;margin-bottom:16px;">--}}
{{--                                    <thead>--}}
{{--                                        <tr style="background-color:#eef2ff;color:#3730a3;">--}}
{{--                                            <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Služba</th>--}}
{{--                                            <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Tarif</th>--}}
{{--                                            <th style="padding:10px 12px;text-align:right;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Cena bez DPH</th>--}}
{{--                                            <th style="padding:10px 12px;text-align:right;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;">Cena s DPH</th>--}}
{{--                                        </tr>--}}
{{--                                    </thead>--}}
{{--                                    <tbody>--}}
{{--                                        @foreach($invoicePerson->lines as $line)--}}
{{--                                            <tr style="background-color:#ffffff;border-bottom:1px solid #e5e7eb;">--}}
{{--                                                <td style="padding:10px 12px;font-size:14px;color:#111827;font-weight:500;">{{ $line->service_name }}</td>--}}
{{--                                                <td style="padding:10px 12px;font-size:14px;color:#374151;">{{ $line->tariff ?? '—' }}</td>--}}
{{--                                                <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_without_vat, 2, ',', ' ') }} Kč</td>--}}
{{--                                                <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_with_vat, 2, ',', ' ') }} Kč</td>--}}
{{--                                            </tr>--}}
{{--                                        @endforeach--}}
{{--                                    </tbody>--}}
{{--                                    <tfoot>--}}
{{--                                        <tr style="background-color:#f9fafb;font-weight:600;color:#111827;">--}}
{{--                                            <td colspan="3" style="padding:10px 12px;text-align:right;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;">Součet</td>--}}
{{--                                            <td style="padding:10px 12px;text-align:right;font-size:14px;">{{ $personTotalWithoutVat }} Kč</td>--}}
{{--                                            <td style="padding:10px 12px;text-align:right;font-size:14px;">{{ $personTotalWithVat }} Kč</td>--}}
{{--                                        </tr>--}}
{{--                                        <tr style="background-color:#eef2ff;font-weight:700;color:#4338ca;">--}}
{{--                                            <td colspan="3" style="padding:10px 12px;text-align:right;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;">Částka k úhradě</td>--}}
{{--                                            <td colspan="2" style="padding:10px 12px;text-align:right;font-size:16px;">{{ $personPayable }} Kč</td>--}}
{{--                                        </tr>--}}
{{--                                    </tfoot>--}}
{{--                                </table>--}}
{{--                            @endif--}}

                            @if(!empty($invoicePerson->applied_rules))
                                <div style="margin-top:18px;">
                                    <h3 style="margin:0 0 10px;font-size:15px;font-weight:600;color:#1f2937;">Aplikovaná pravidla</h3>
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
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:24px;padding:18px;border-radius:12px;background-color:#eef2ff;color:#4338ca;font-size:13px;line-height:1.7;">
                    <strong>Poznámka:</strong>
                    Částky k úhradě zohledňují nastavené limity jednotlivých osob. V případě dotazů se prosím obraťte na oddělení audiovizuální podpory.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
