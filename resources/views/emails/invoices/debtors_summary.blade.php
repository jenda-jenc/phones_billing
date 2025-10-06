<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Souhrn dlužníků</title>
</head>
<body style="margin:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;color:#111827;">
<div style="max-width:100vw; margin:0 auto;padding:32px 16px;">
    <div style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 18px 48px rgba(15,23,42,0.12);">
        <div style="background:linear-gradient(115deg,#1e3a8a,#4338ca);color:#ffffff;padding:28px 32px;">
            <p style="margin:0;font-size:13px;letter-spacing:0.12em;text-transform:uppercase;opacity:0.7;">Senát Parlamentu ČR</p>
            <h1 style="margin:8px 0 0;font-size:26px;font-weight:600;">Souhrn nedoplatků vyúčtování</h1>
        </div>

        <div style="padding:22px 24px 30px;">
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
            @endphp

            <p style="margin:0 0 26px;font-size:15px;line-height:1.6;">
                Níže naleznete přehled osob a jejich telefonních čísel s částkami k úhradě
                @if($billingLabel)
                    za období: <strong>{{ \Illuminate\Support\Str::lower($billingLabel) }}</strong>
                @endif
            </p>

            {{-- ✅ KOMPAKTNÍ TABULKA – přehledná i v Outlooku i v mobilu --}}
            <table style="width:100%;border-collapse:collapse;margin-bottom:40px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                <thead>
                <tr style="background-color:#e0e7ff;color:#1e3a8a;">
                    <th style="padding:10px 8px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;width:20%;">Jméno</th>
                    <th style="padding:10px 8px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;width:15%;">Telefon</th>
                    <th style="padding:10px 8px;text-align:right;font-size:12px;font-weight:700;text-transform:uppercase;width:12%;">Bez DPH</th>
                    <th style="padding:10px 8px;text-align:right;font-size:12px;font-weight:700;text-transform:uppercase;width:10%;">DPH</th>
                    <th style="padding:10px 8px;text-align:right;font-size:12px;font-weight:700;text-transform:uppercase;width:12%;">S DPH</th>
                    <th style="padding:10px 8px;text-align:right;font-size:12px;font-weight:700;text-transform:uppercase;width:12%;">Limit</th>
                    <th style="padding:10px 8px;text-align:right;font-size:12px;font-weight:700;text-transform:uppercase;width:12%;">K úhradě</th>
                </tr>
                </thead>
                <tbody>
                @foreach($debtors as $invoicePerson)
                    @php
                        $person = $invoicePerson->person;
                        $displayName = $person->name ?? 'Osoba #' . $invoicePerson->id;
                        $phone = $invoicePerson->phone ?? ($person?->phones?->first()?->phone ?? '—');
                        $personTotalWithoutVat = number_format($invoicePerson->total_without_vat, 2, ',', ' ');
                        $personTotalWithVat = number_format($invoicePerson->total_with_vat, 2, ',', ' ');
                        $personLimit = number_format($invoicePerson->limit, 2, ',', ' ');
                        $personPayable = number_format($invoicePerson->payable, 2, ',', ' ');
                        $vatAmount = max(0, $invoicePerson->total_with_vat - $invoicePerson->total_without_vat);
                        $vatFormatted = number_format($vatAmount, 2, ',', ' ');
                    @endphp

                    <tr style="border-bottom:1px solid #e5e7eb; background-color: #fee2e2">
                        <td style="padding:10px 8px;font-size:14px;font-weight:600;color:#111827;">{{ $displayName }}</td>
                        <td style="padding:10px 8px;font-size:13px;color:#374151;">{{ $phone }}</td>
                        <td style="padding:10px 8px;text-align:right;font-size:14px;">{{ $personTotalWithoutVat }} Kč</td>
                        <td style="padding:10px 8px;text-align:right;font-size:14px;">{{ $vatFormatted }} Kč</td>
                        <td style="padding:10px 8px;text-align:right;font-size:14px;">{{ $personTotalWithVat }} Kč</td>
                        <td style="padding:10px 8px;text-align:right;font-size:14px;">{{ $personLimit }} Kč</td>
                        <td style="padding:10px 8px;text-align:right;font-size:15px;font-weight:700;color:#4338ca;">{{ $personPayable }} Kč</td>
                    </tr>

{{--                    @if(!empty($invoicePerson->applied_rules))--}}
{{--                        <tr style="background-color:#f9fafb;">--}}
{{--                            <td colspan="7" style="padding:6px 12px 10px;font-size:13px;color:#374151;">--}}
{{--                                <ul style="margin:0;padding-left:18px;list-style:disc;">--}}
{{--                                    @foreach($invoicePerson->applied_rules as $rule)--}}
{{--                                        <li style="margin-bottom:4px;">--}}
{{--                                            {{ $rule['popis'] ?? 'Pravidlo' }}--}}
{{--                                            @if(!empty($rule['sluzba'])) – {{ $rule['sluzba'] }} @endif--}}
{{--                                            @if(!empty($rule['cena_s_dph']))--}}
{{--                                                ({{ number_format($rule['cena_s_dph'], 2, ',', ' ') }} Kč s DPH)--}}
{{--                                            @endif--}}
{{--                                        </li>--}}
{{--                                    @endforeach--}}
{{--                                </ul>--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    @endif--}}
                @endforeach
                </tbody>
            </table>

            <br>
            <br>
            {{-- 🔽 DETAILNÍ ČÁST PRO KAŽDÉ ČÍSLO --}}
            <h2 style="margin:0 0 18px;font-size:18px;font-weight:600;color:#1f2937;">Detailní přehled služeb</h2>

            @foreach($debtors as $invoicePerson)
                @php
                    $person = $invoicePerson->person;
                    $displayName = $person->name ?? 'Osoba #' . $invoicePerson->id;
                    $phone = $invoicePerson->phone ?? ($person?->phones?->first()?->phone ?? '—');
                    $personTotalWithVat = number_format($invoicePerson->total_with_vat, 2, ',', ' ');
                    $personPayable = number_format($invoicePerson->payable, 2, ',', ' ');
                @endphp

                <div style="margin-bottom:32px;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                    {{-- Hlavicka osoby --}}
                    <div style="background-color:#f9fafb;padding:16px 20px;">
                        <h3 style="margin:0;font-size:17px;font-weight:600;color:#1f2937;">{{ $displayName }}</h3>
                        <div style="margin-top:4px;font-size:13px;color:#6b7280;">Telefon: {{ $phone }} &nbsp; limit: {{ $invoicePerson->limit }} Kč</div>
                    </div>

                    {{-- Obsah --}}
                    <div style="padding:20px;">
                        {{-- Tabulka služeb --}}
                        @if($invoicePerson->lines->isNotEmpty())
                            @php
                                // Seznam názvů služeb z aplikovaných pravidel (pro zvýraznění)
                                $highlightedServices = collect($invoicePerson->applied_rules ?? [])
                                    ->pluck('sluzba')
                                    ->filter()
                                    ->map(fn($s) => trim(\Illuminate\Support\Str::lower($s)))
                                    ->toArray();
                            @endphp

                            <table style="width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;margin-bottom:16px;">
                                <thead>
                                <tr style="background-color:#eef2ff;color:#3730a3;">
                                    <th style="padding:10px 12px;text-align:left;font-size:12px;text-transform:uppercase;">Služba</th>
                                    <th style="padding:10px 12px;text-align:left;font-size:12px;text-transform:uppercase;">Tarif</th>
                                    <th style="padding:10px 12px;text-align:right;font-size:12px;text-transform:uppercase;">Bez DPH</th>
                                    <th style="padding:10px 12px;text-align:right;font-size:12px;text-transform:uppercase;">S DPH</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoicePerson->lines as $line)
                                    @php
                                        $isHighlighted = in_array(trim(\Illuminate\Support\Str::lower($line->service_name)), $highlightedServices);
                                        $rowBg = $isHighlighted ? '#fee2e2' : '#ffffff'; // světle červené pozadí pro zvýrazněné služby
                                    @endphp
                                    <tr style="background-color:{{ $rowBg }};border-bottom:1px solid #e5e7eb;">
                                        <td style="padding:10px 12px;font-size:14px;color:#111827;font-weight:{{ $isHighlighted ? '700' : '500' }};">
                                            {{ $line->service_name }}
                                        </td>
                                        <td style="padding:10px 12px;font-size:14px;color:#374151;">{{ $line->tariff ?? '—' }}</td>
                                        <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_without_vat, 2, ',', ' ') }} Kč</td>
                                        <td style="padding:10px 12px;font-size:14px;color:#111827;text-align:right;">{{ number_format($line->price_with_vat, 2, ',', ' ') }} Kč</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr style="background-color:#f9fafb;font-weight:600;color:#111827;">
                                    <td colspan="3" style="padding:10px 12px;text-align:right;font-size:13px;text-transform:uppercase;">Součet</td>
                                    <td style="padding:10px 12px;text-align:right;font-size:14px;">{{ $personTotalWithVat }} Kč</td>
                                </tr>
                                <tr style="background-color:#eef2ff;font-weight:700;color:#4338ca;">
                                    <td colspan="3" style="padding:10px 12px;text-align:right;font-size:13px;text-transform:uppercase;">K úhradě</td>
                                    <td style="padding:10px 12px;text-align:right;font-size:16px;">{{ $personPayable }} Kč</td>
                                </tr>
                                </tfoot>
                            </table>
                        @endif

                        {{-- Aplikovaná pravidla --}}
                        @if(!empty($invoicePerson->applied_rules))
                            <div style="margin-top:14px;padding:14px 16px;border-radius:10px;background-color:#f3f4f6;">
                                <h4 style="margin:0 0 10px;font-size:14px;font-weight:600;color:#1f2937;">Aplikovaná pravidla</h4>
                                <ul style="margin:0;padding-left:18px;color:#374151;font-size:14px;line-height:1.6;">
                                    @foreach($invoicePerson->applied_rules as $rule)
                                        <li style="margin-bottom:6px;">
                                            <strong>{{ $rule['popis'] ?? 'Pravidlo' }}</strong>
                                            @if(!empty($rule['sluzba'])) – {{ $rule['sluzba'] }} @endif
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
                Částky k úhradě se vztahují ke konkrétním telefonním číslům a zohledňují individuální limity. V případě dotazů se prosím obraťte na oddělení audiovizuální podpory.
            </div>
        </div>
    </div>
</div>
</body>
</html>
