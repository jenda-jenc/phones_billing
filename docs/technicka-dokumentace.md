# Technická dokumentace

Tento dokument slouží pro vývojáře a integrátory aplikace *Telefonní vyúčtování*.

## Architektura

- **Backend**: Laravel 12 (PHP 8.2) s Inertia.js. Autentizace přes Laravel Breeze.
- **Frontend**: Vue 3, Vite, Tailwind CSS.
- **Databáze**: SQL Server. ORM Eloquent.
- **Komunikace klient-server**: Inertia renderuje Vue komponenty, směrování probíhá přes Laravel routy v `routes/web.php`.
- **E-mail**: SMTP, odesílá se přes `Illuminate\Support\Facades\Mail`.

### Modulární přehled

| Oblast | Klíčové třídy / soubory |
| --- | --- |
| Import faktur | `ImportController`, `App\Services\ImportService`, datové objekty `App\Data\ImportedPersonData` a `ServiceEntry`. |
| Vyúčtování | `InvoiceController`, modely `Invoice`, `InvoicePerson`, `InvoiceLine`. |
| Evidence osob | `PersonController`, `Person`, `PersonPhone`, vazby `groups`, `tariffs`. |
| Správa skupin | `GroupController`, `Group`, `Tariff`, pivot `group_tariff`. |
| Frontend | `resources/js/Pages/*.vue`, layout `resources/js/Layouts/AuthenticatedLayout.vue`, sdílené komponenty v `resources/js/Components/`. |
| E-maily | `App\Mail\InvoiceBreakdownMail`, `InvoiceDebtorsSummaryMail` a Blade šablony v `resources/views/emails/`. |

## Datový model

Hlavní tabulky (viz `database/migrations`):

- `people` – osoby s kolonkami `name`, `department`.
- `person_phones` – telefonní čísla, limity a vazba na osobu.
- `groups` – organizační jednotky/role.
- `group_person` – pivot pro přiřazení osob do skupin.
- `tariffs` – názvy služeb. Pivot `group_tariff` obsahuje sloupec `action` (`ignorovat`, `do_limitu`, `plati_sam`).
- `invoices` – metadata importu (období, poskytovatel, název zdrojového souboru, mapování).
- `invoice_people` – agregace za osobu (částky, limit, aplikovaná pravidla, `payable`).
- `invoice_lines` – jednotlivé služby s cenami bez/ s DPH.
- `users`, `person_user` – vazba na osoby (pro posílání e-mailů a přístupová práva).
- `jobs`, `failed_jobs`, `sessions` – pomocné tabulky pro queue/session (pokud jsou aktivní).

## Průběh importu

1. Uživatelská stránka `Import.vue` odešle CSV soubor a JSON mapování na `POST /import/process`.
2. `ImportController::processImport` načte CSV, převede kódování na UTF-8, vytvoří `ImportService` a spustí `process()`.
3. `ImportService`:
   - `mapServices()` převádí CSV do pole `phone => ImportedPersonData` a počítá DPH.
   - `calculatePersons()` páruje telefonní čísla na osoby, uplatňuje tarifní pravidla podle skupin a počítá částky.
   - `storeInvoice()` uloží data do tabulek `invoices`, `invoice_people`, `invoice_lines`. Existující faktura se stejným obdobím/provozovatelem se přepíše.
4. Výsledek vrací ID faktury a strukturu pro zobrazení tabulky.

## Přístupová práva

- Middleware `auth` a `verified` chrání většinu rout.
- `InvoiceController` filtruje záznamy podle role uživatele (`users.role`). Pokud není role `admin`, uživatel vidí pouze osoby, které patří do skupin s hodnotou shodnou s jeho rolí.
- Vlastníci osob jsou definováni vazbou `person_user`. Pokud existuje, uživatel má odkaz na detail své osoby ve faktuře.

## E-mailové notifikace

- Individuální e-mail: `POST /invoices/{invoicePerson}/email`. Pokud není zadána adresa, použije se první přidružený uživatel nebo fallback ve formátu `prijmeniJ@senat.cz`.
- Souhrn dlužníků: `POST /invoices/{invoice}/debtors/email`. E-mail obsahuje osoby s `payable > 0`.
- V neprodukčním prostředí se e-maily odešlou pouze na adresu uvedenou v `TEST_EMAIL`.

## Frontendové komponenty

- **Dashboard.vue** – seznam faktur s odkazy na detail.
- **Import.vue** – nahrávání souboru, mapování sloupců, správa tarifů.
- **ImportTable.vue** – tabulka výsledků importu, modální okna pro odesílání e-mailů.
- **Osoby.vue** – CRUD nad osobami a přiřazování skupin.
- **TariffGroups.vue** – správa skupin a tarifů.

Komunikace probíhá přes Inertia router, tj. `Link` komponenty a `router.visit()`.

## Testování

- Backend: PHPUnit/Pest (`php artisan test` nebo `./vendor/bin/pest`).
- Frontend: eslint pro statickou analýzu (`npm run lint`).
- Doporučené integrační testy:
  - import CSV souboru s validním mappingem,
  - kontrola, že invoice s duplicitním obdobím/provozovatelem se přepíše,
  - kontrola generování CSV/PDF (`InvoiceController::download`).

## Lokální vývoj

1. `cp .env.example .env` a nastavte `DB_CONNECTION=sqlite` nebo `sqlsrv` podle dostupnosti.
2. `composer install`, `npm install`.
3. `php artisan key:generate`.
4. `php artisan migrate` (včetně `session:table` a `queue:table` dle potřeby).
5. `npm run dev` a `php artisan serve` (nebo použijte skript `composer dev`, který spustí server, queue listener a Vite současně).
6. Pro odesílání e-mailů nastavte `MAIL_MAILER=log` v lokálním prostředí.

## Integrace

- Exporty: Soubor CSV/PDF je možné stáhnout z `GET /invoices/{invoice}/download/{format}`. V případě potřeby lze endpoint využít pro napojení do DMS.
- Autentizace: pro externí systémy lze využít Laravel Sanctum (závislost je přítomna, není však zatím nakonfigurována).

## Rozšiřitelnost

- Nové typy poskytovatelů přidáte rozšířením konstanty `Invoice::PROVIDERS` a úpravou formuláře na straně frontendu (`Import.vue`).
- Další pravidla účtování lze doplnit v `ImportService::calculatePersons()` (switch nad `pivot->action`).
- Pokud bude potřeba audit, doporučujeme přidat logování do `ImportService` a modelů (`created_by`, `updated_by`).
