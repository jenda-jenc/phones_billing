# Uživatelská příručka

Tato příručka popisuje běžnou práci s aplikací *Telefonní vyúčtování* z pohledu koncového uživatele. Vychází z aktuální funkcionality uvedené ve verzích Laravelu 12 a Vue 3.

## Přihlášení a role

- Přístup je chráněn uživatelským účtem (Laravel Breeze). Přihlašovací údaje přiděluje správce.
- Po přihlášení se na horní liště zobrazuje aktivní role. Role určuje, které osoby a faktury může uživatel vidět.
  - **Administrátor (`admin`)** vidí kompletní data a může přecházet na detaily všech osob.
  - **Ostatní role** odpovídají hodnotám přiřazeným osobám ve skupinách (např. `kancl`, `vedeni`). Uživatel vidí pouze osoby ve stejné skupině.

## Přehled faktur (Dashboard)

1. Po přihlášení je uživatel přesměrován na stránku **Přehled faktur**.
2. V tabulce jsou uvedeny jednotlivé importy (faktury) se základními metadaty: období, operátor, název zdrojového souboru a datum importu.
3. Sloupec **Přehled** obsahuje akce:
   - **Zobrazit** otevře souhrnnou tabulku importu s rozpisem položek pro jednotlivé osoby.
   - Administrátor může z rozbalovací nabídky **Osoby** přejít na detail vyúčtování konkrétní osoby.
   - Pokud má uživatel vazbu na některou osobu v importu, zobrazí se tlačítko **Detail**, které otevře přehled jen pro danou osobu.

## Import vyúčtování

Stránka **Import** slouží k nahrání CSV výpisů od operátora.

1. Nahrajte soubor se službami ve formátu CSV (oddělovač `;`). Aplikace automaticky rozpozná kódování `UTF-8` nebo `Windows-1250`.
2. V části **Mapování sloupců** vyberte, které sloupce obsahují telefonní číslo, tarif, název služby, cenu a DPH. Volitelně můžete zvolit i sloupec se skupinou.
3. Zadejte **účtované období** (ve formátu `YYYY-MM`) a **poskytovatele** (T-Mobile, O2 nebo jiné).
4. Panel upozornění zobrazuje:
   - seznam nových služeb, které v databázi tarifů ještě neexistují,
   - seznam služeb, jež nejsou přiřazeny žádné skupině.
5. Nové tarify lze uložit hromadně tlačítkem **Přidat do tarifů**; přiřazení služby ke skupině provedete přes dialog **Přiřadit službu**.
6. Po potvrzení tlačítkem **Zpracovat import** proběhne parsování souboru, výpočet částek a vytvoření/aktualizace faktury.
7. Po úspěchu jste přesměrováni na tabulku importu, odkud lze odeslat přehled dlužníků e-mailem.

## Tabulka importu

Souhrnná tabulka zobrazuje pro každou osobu:
- jméno a e-mailový prefix,
- telefonní číslo, limit a vypočtené částky bez/ s DPH,
- výslednou částku k úhradě,
- přehled služeb s přiřazenými pravidly.

Z tabulky lze odeslat:
- individuální e-mail s vyúčtováním konkrétní osobě (po vyplnění adresy nebo využití výchozí adresy `prijmeniJ@senat.cz`),
- hromadný e-mail správcům s přehledem dlužníků.

## Správa osob

Na stránce **Osoby** lze evidovat uživatele firemních telefonů.

- Tlačítko **Přidat osobu** otevře formulář s políčky *Jméno*, *Pracoviště* a seznamem telefonních čísel (včetně limitu).
- Limit je povinný a zadává se v Kč. Formulář umožňuje přidat více čísel pro jednu osobu.
- Po uložení se záznam objeví v tabulce. Z každého řádku lze:
  - otevřít formulář pro **editaci** (tlačítko tužky),
  - **smazat** osobu (ikona koše),
  - **přiřadit skupinu** – otevře se dialog s výběrem existujících skupin.

## Správa tarifních skupin

Stránka **Tarify** zobrazuje seznam skupin a přiřazených tarifů.

- Tlačítkem **Nová skupina** založíte skupinu s názvem (zobrazuje se uživatelům) a hodnotou (slouží jako interní identifikátor/role).
- Každé skupině lze přiřazovat tarify, přičemž je nutné zvolit akci:
  - `ignorovat` – položka se v importu odečte,
  - `do_limitu` – položka se započítá do limitu zaměstnance,
  - `plati_sam` – částka jde celá k úhradě zaměstnanci.
- Tarify lze od skupiny kdykoli odebrat.

## Profil uživatele

V pravém horním rohu je menu profilu. Uživatel si může změnit jméno, e-mail, heslo nebo zrušit účet (pokud má oprávnění).

## Doporučený pracovní postup

1. Zkontrolujte seznam osob a jejich limity, případně doplňte nové tarify/skupiny.
2. Nahrajte nový CSV výpis na stránce **Import** a nastavte mapování.
3. Projděte upozornění na nové/neetiketované služby a doplňte tarifní pravidla.
4. Zpracujte import a zkontrolujte výsledky v tabulce.
5. Odešlete vyúčtování jednotlivcům a dlužníky nadřízeným podle interních pravidel.
