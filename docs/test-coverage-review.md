# Revize testů

## Přehled aktuálního pokrytí

- **Autentizace a profil uživatele** – testy pokrývají přihlašování, registraci a základní správu profilu (zobrazení, aktualizace, smazání účtu). 【F:tests/Feature/Auth/AuthenticationTest.php†L5-L32】【F:tests/Feature/Auth/RegistrationTest.php†L3-L18】【F:tests/Feature/ProfileTest.php†L5-L85】
- **Správa osob a telefonních čísel** – existují integrační testy, které ověřují vytvoření osoby s více telefony, aktualizaci seznamu čísel, zobrazení seznamu a úspěšný import z `ImportService`. 【F:tests/Feature/PersonPhoneTest.php†L14-L156】
- **Odebrání skupiny z osoby** – testy zajišťují, že k akci má přístup pouze přihlášený uživatel a že vazba je skutečně odebrána. 【F:tests/Feature/PersonDetachGroupTest.php†L7-L64】
- **Validace skupin/tarifů** – existují dílčí testy pro vytvoření/aktualizaci skupiny a hromadné zakládání tarifů. 【F:tests/Feature/GroupControllerTest.php†L12-L71】

## Identifikované mezery

### PersonController

- Chybí testy pro akce `destroy` a `assignGroup`, které mazají osobu nebo jí přiřazují skupinu, přestože jsou vystaveny v routách. 【F:app/Http/Controllers/PersonController.php†L61-L83】【F:routes/web.php†L56-L61】
- Validace vstupů při ukládání/aktualizaci osob zahrnuje řadu pravidel (unikátnost čísla, povinný limit, rozsahy), ale testy pokrývají pouze úspěšné scénáře. 【F:app/Http/Controllers/PersonController.php†L24-L196】【F:tests/Feature/PersonPhoneTest.php†L14-L73】
  - Chybí například ověření, že duplicitní číslo vrátí chybu (`Rule::unique`), že limit musí být numerický a v daném rozsahu, nebo že prázdné či nevalidní záznamy v poli telefonů jsou odfiltrovány funkcí `preparePhones`. 【F:app/Http/Controllers/PersonController.php†L98-L148】【F:app/Http/Controllers/PersonController.php†L175-L215】
  - Metoda `normalizeLimit` podporuje číselné řetězce s čárkou/tečkou a má vracet `null` pro nečíselný vstup, ale žádný test neověřuje tyto hraniční případy. 【F:app/Http/Controllers/PersonController.php†L152-L173】
- `syncPhones` zaokrouhluje limity na dvě desetinná místa a maže čísla, která v požadavku chybí; jediný test sice ověřuje nahrazení seznamu, ale neověřuje zaokrouhlování a mazání více čísel najednou. 【F:app/Http/Controllers/PersonController.php†L128-L150】【F:tests/Feature/PersonPhoneTest.php†L39-L73】

### GroupController

- V testech chybí pozitivní scénáře pro `store` a `update`, které by ověřily úspěšné vytvoření/aktualizaci skupiny. 【F:app/Http/Controllers/GroupController.php†L25-L39】【F:tests/Feature/GroupControllerTest.php†L12-L49】
- Nebyl pokryt žádný z koncových bodů pro přiřazování/odebírání tarifů, mazání skupiny nebo výpis tarifů, přestože obsahují vlastní validace a manipulaci s vazbami. 【F:app/Http/Controllers/GroupController.php†L41-L95】【F:routes/web.php†L63-L72】
- Testy `bulkStoreTariffs` kontrolují pouze základní vytvoření, ale neověřují např. idempotentnost (`firstOrCreate`) a validaci vstupu `names`. 【F:app/Http/Controllers/GroupController.php†L73-L80】【F:tests/Feature/GroupControllerTest.php†L51-L71】

### Import a faktury

- `ImportController` implementuje více kroků (validace souboru, dekódování mapování, čtení CSV, přesměrování na detail), avšak není pokryt žádným testem. 【F:app/Http/Controllers/ImportController.php†L12-L78】
  - Zvláštní pozornost by zasloužila pomocná metoda `stripDiacritics`, která generuje e-mailové adresy bez diakritiky. 【F:app/Http/Controllers/ImportController.php†L40-L55】
- `InvoiceController` poskytuje rozsáhlou funkcionalitu – stránkovaný dashboard, generování CSV/PDF, filtrování podle role, odesílání e-mailů jednotlivcům/dlužníkům – která není pokryta testy. 【F:app/Http/Controllers/InvoiceController.php†L23-L204】【F:app/Http/Controllers/InvoiceController.php†L200-L330】【F:app/Http/Controllers/InvoiceController.php†L330-L512】
  - Nejsou ověřeny hraniční stavy, jako např. neplatný formát stažení, chybějící příjemce e-mailu, prázdný seznam dlužníků či generování názvu souboru.
- `ImportService` je testována pouze na úspěšný import se dvěma čísly, ale v implementaci jsou větve pro služby s nulovou cenou, různé akce tarifů (`plati_sam`, `ignorovat`) a aktualizaci existující faktury, které testy nepokrývají. 【F:app/Services/ImportService.php†L55-L187】【F:tests/Feature/PersonPhoneTest.php†L100-L156】

### Další oblasti

- Neexistují unit testy pro datové třídy (`ImportedPersonData`, `ServiceEntry`) nebo pomocné metody, které by ověřily např. formátování návratových hodnot. 【F:app/Data/ImportedPersonData.php†L1-L74】
- Route `/invoices/{invoice}/debtors/email` je přístupná bez middleware `auth`, což může být záměr, ale testy by měly ověřit očekávané chování pro nepřihlášeného uživatele. 【F:routes/web.php†L33-L37】

## Doporučení

1. **Doplnit kritické scénáře pro PersonController** – zejména validační chyby (duplicitní číslo, nečíselný limit), mazání osoby a přiřazování skupin. U validací lze využít parametrizované testy Pestu.
2. **Pokrytí správy skupin a tarifů** – přidat testy pro úspěšné vytvoření/aktualizaci, přiřazení a odebrání tarifů a smazání skupiny. Zajistí se tím, že pivot tabulky i validační pravidla fungují podle očekávání.
3. **Integrační testy importu a faktur** – simulovat `processImport` se skutečným souborem, ověřit `stripDiacritics` a pokrýt stavy v `InvoiceController` (např. stažení CSV, ochrana před neautorizovaným přístupem, e-mailové scénáře).
4. **Unit testy pro služby a pomocné třídy** – testovat `ImportService` na hraniční stavy (nulové ceny, různé akce tarifů, aktualizace již existující faktury) a datové objekty na správné formátování.
5. **Bezpečnostní testy pro e-mailové endpointy** – doplnit testy ověřující nutnost autentizace a správné zacházení s chybnými vstupy v `InvoiceController::email` a `InvoiceController::emailDebtors`.

Rozšíření testů v uvedených oblastech zvýší jistotu při refaktoringu a pomůže zachytit regresní chyby v klíčových procesech (import dat, generování vyúčtování a správa entit).
