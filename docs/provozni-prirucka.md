# Provozní příručka

Tento dokument popisuje doporučené nastavení a provozní postupy pro aplikaci *Telefonní vyúčtování*.

## Systémové požadavky

- **PHP** 8.2 s rozšířeními `mbstring`, `openssl`, `pdo_sqlsrv`, `json`, `xml`, `ctype`, `fileinfo`.
- **Composer** 2.6+ pro správu PHP závislostí.
- **Node.js** 20 LTS a **npm** pro build front-endu (Vite + Vue 3).
- Databáze Microsoft **SQL Server** (doporučeno 2019 nebo novější) s povolenými transakcemi a podporou `READ_COMMITTED_SNAPSHOT`.
- Webový server (Apache/Nginx) s podporou PHP-FPM **nebo** Laravel Octane/Sail dle interních standardů.

## Nasazení

1. Naklonujte repozitář a přepněte se na požadovanou větev.
2. Zkopírujte soubor `.env.example` do `.env` a doplňte hodnoty pro databázi, SMTP a další proměnné (viz níže).
3. Spusťte `composer install --optimize-autoloader --no-dev`.
4. Vygenerujte aplikační klíč `php artisan key:generate`.
5. Spusťte migrace `php artisan migrate --force`.
6. Nainstalujte front-end závislosti `npm ci` a zkompilujte assets `npm run build`.
7. Nastavte správná oprávnění pro složky `storage/` a `bootstrap/cache/`.
8. Nasměrujte webový server na `public/index.php` a nakonfigurujte HTTPS.

### Doporučené proměnné prostředí

| Proměnná | Popis |
| --- | --- |
| `APP_ENV` | `production`, `staging` nebo `local`; řídí chování e-mailů a logování. |
| `APP_URL` | Základní URL aplikace (např. `https://vyuctovani.firma.cz`). |
| `DB_*` | Připojení k SQL Serveru. Použijte šifrované spojení podle interních zásad. |
| `SESSION_DRIVER` | Doporučeno `database` pro sdílené prostředí. Spusťte `php artisan session:table`. |
| `QUEUE_CONNECTION` | Výchozí `database`. Pokud používáte asynchronní odesílání e-mailů, spusťte `php artisan queue:table`. |
| `MAIL_*` | SMTP přístup. `MAIL_FROM_ADDRESS` je adresa odesílatele. |
| `TEST_EMAIL` | Povolená testovací adresa; mimo `production` prostředí aplikace neodešle e-maily na jiné adresy. |

### Plánovač a fronty

- V současné verzi se odesílání e-mailů provádí synchronně (`Mail::sendNow`). Pro větší objemy lze přepnout na fronty a spouštět `php artisan queue:work --tries=1`.
- Doporučujeme nastavit **cron** na `php artisan schedule:run` každou minutu, aby bylo možné v budoucnu snadno přidat plánované úlohy.

## Údržba

### Logy

- Aplikační logy se ukládají do `storage/logs/laravel.log`. Doporučuje se centralizované logování (např. syslog) pro produkci.
- Databázové chyby sledujte v SQL Server monitoringu.

### Zálohování

- Zálohujte databázi v pravidelných intervalech (minimálně denně). Obsahuje všechny importy, osoby i konfiguraci tarifů.
- Soubory generované pro stažení (CSV/PDF) se ukládají do `storage/app/invoices`. Tyto soubory lze re-generovat, přesto doporučujeme zahrnout složku `storage/app` do záloh.

### Monitorování

- Sledujte velikost tabulek `invoices`, `invoice_people`, `invoice_lines` a `person_phones`. Při velkých objemech je vhodné archivovat starší faktury.
- Kontrolujte, že fronty (pokud jsou používány) nemají nevyřízené úlohy (`jobs`/`failed_jobs`).

### Aktualizace

1. Vytvořte zálohu databáze a souborů.
2. Vypněte aplikaci do maintenance režimu `php artisan down`.
3. Aplikujte změny (`git pull`, `composer install`, `npm ci`, `npm run build`).
4. Spusťte migrace `php artisan migrate --force`.
5. Vyčistěte cache `php artisan config:clear` a `php artisan route:clear` podle potřeby.
6. Vraťte aplikaci online `php artisan up` a proveďte smoke test (přihlášení, import na testovacích datech, odeslání e-mailu).

## Incident management

- Při chybě importu zkontrolujte logy a zdrojový CSV soubor. Funkce `ImportController::processImport` validuje formát a může vracet chyby mapování.
- Pokud uživateli nedorazí e-mail, ověřte nastavení `TEST_EMAIL` a SMTP logy. V ne-produkčních prostředích se e-maily mimo testovací adresu neodesílají.
- Při podezření na nekonzistenci dat lze import zopakovat – aplikace fakturu se stejným obdobím a poskytovatelem přepíše.

## Přístupová práva

- Oprávnění k datům se odvozují z rolí uživatelů (`users.role`) a přiřazených skupin (`groups.value`).
- Administrátor má přístup ke všem funkcím; ostatní role vidí pouze osoby ve svých skupinách a mohou pracovat jen se svými vyúčtováními.

## Kontakty

- Primární kontaktní osoba: vlastník aplikace / IT oddělení.
- Pro změny konfigurace databáze nebo SMTP kontaktujte příslušné správce infrastruktury.
