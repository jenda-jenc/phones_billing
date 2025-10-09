# Telefonní vyúčtování

Interní webová aplikace pro zpracování a kontrolu vyúčtování firemních telefonních služeb. Systém umožňuje importovat CSV výpisy od operátorů, párovat je na evidované osoby, uplatňovat tarifní pravidla a rozesílat výsledky e-mailem.

## Dostupná dokumentace

Kompletní dokumentaci najdete ve složce [`docs/`](docs/):

- [Uživatelská příručka](docs/uzivatelska-prirucka.md) – průvodce prací s aplikací pro běžné uživatele.
- [Provozní příručka](docs/provozni-prirucka.md) – instrukce pro administrátory prostředí a provozní dohled.
- [Technická dokumentace](docs/technicka-dokumentace.md) – přehled architektury, datového modelu a integračních bodů.

### Publikace do GitLab Wiki

GitLab Wiki je samostatný Git repozitář. Markdown soubory ze složky `docs/` se proto v GitLabu automaticky
nepublikují – je potřeba je do wiki zkopírovat. Nejjednodušší je nastavit CI job, který obsah `docs/`
přesune do wiki repozitáře při každém commitu na hlavní větev. Následující příklad můžete vložit do
souboru `.gitlab-ci.yml` v kořenovém adresáři projektu:

```yaml
pages:docs-to-wiki:
  stage: deploy
  image: alpine:3.18
  rules:
    - if: '$CI_COMMIT_BRANCH == "main"'
  before_script:
    - apk add --no-cache git
    - git config --global user.email "$GITLAB_USER_EMAIL"
    - git config --global user.name "$GITLAB_USER_NAME"
  script:
    - git clone --depth 1 "$CI_SERVER_PROTOCOL://$CI_SERVER_HOST/$CI_PROJECT_PATH.wiki.git" wiki
    - rm -rf wiki/*
    - cp -r docs/* wiki/
    - cd wiki
    - git add .
    - |
      if ! git diff --cached --quiet; then
        git commit -m "docs: sync from repo"
        git push origin HEAD:master
      fi
  artifacts:
    paths: []

```

Po prvním spuštění CI jobu budou dokumenty dostupné ve wiki projektu (`Projekt` → `Wiki`). Pokud wiki
zatím není povolena, aktivujte ji v `Settings` → `General` → `Visibility, project features, permissions`.

Alternativně lze soubory do wiki kopírovat ručně pomocí klonování repozitáře `projekt.wiki.git` a
přidání Markdown souborů do požadované struktury.

## Licencování

Licenční podmínky stanovuje vlastník repozitáře. Pokud není uvedeno jinak, doporučujeme zachovat kompatibilitu s licencí MIT, kterou používá základní framework Laravel.
