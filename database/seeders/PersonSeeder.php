<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
        [
            'name' => 'Jiránek Radek',
            'department' => 'Vedoucí kanceláře senátu',
            'limit' => 10000,
            'phones' => ['724239119'],
        ],
        [
            'name' => 'Jiránek Radek',
            'department' => 'Vedoucí kanceláře senátu',
            'limit' => 0,
            'phones' => ['731399632'],
        ],
        [
            'name' => 'Hándlová Hana',
            'department' => 'sekretariát VKS',
            'limit' => 450,
            'phones' => ['731124373'],
        ],
        [
            'name' => 'Pánková Renata',
            'department' => 'sekretariát VKS',
            'limit' => 752,
            'phones' => ['775978757'],
        ],
        [
            'name' => 'Vystrčil Miloš',
            'department' => 'Předseda Senátu',
            'limit' => 10000,
            'phones' => ['606767544'],
        ],
        [
            'name' => 'data počítač',
            'department' => 'Předseda Senátu',
            'limit' => 0,
            'phones' => ['731124396'],
        ],
        [
            'name' => 'Data tablet',
            'department' => 'Předseda Senátu',
            'limit' => 0,
            'phones' => ['731124363'],
        ],
        [
            'name' => 'Nguyen Sue',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 1200,
            'phones' => ['723681975'],
        ],
        [
            'name' => 'Nguyen Sue - data tablet',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 303,
            'phones' => ['731124337'],
        ],
        [
            'name' => 'Nguyen Sue - data ntb',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 303,
            'phones' => ['603299965'],
        ],
        [
            'name' => 'Urbanová Eva (Pástor Monika)',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 450,
            'phones' => ['731124391'],
        ],
        [
            'name' => 'Adámková Anna',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 450,
            'phones' => ['731124358'],
        ],
        [
            'name' => 'Lebeda Petr',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 450,
            'phones' => ['799029912'],
        ],
        [
            'name' => 'Stejskalová Natálie',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 450,
            'phones' => ['731024104'],
        ],
        [
            'name' => 'Jelínková Věra',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 644,
            'phones' => ['731124326'],
        ],
        [
            'name' => 'Faldynová Lada',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 1200,
            'phones' => ['704644464'],
        ],
        [
            'name' => 'Kubová Andrea',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 644,
            'phones' => ['776159659'],
        ],
        [
            'name' => 'Netrvalová Sabina',
            'department' => 'sekretariát předsedy Senátu',
            'limit' => 450,
            'phones' => ['731124392'],
        ],
        [
            'name' => 'Menčík Jaroslav',
            'department' => 'sekretariát místopředsedy Obelfalzera',
            'limit' => 644,
            'phones' => ['731124394'],
        ],
        [
            'name' => 'Šedivá Dana',
            'department' => 'mandátový a imunitní výbor',
            'limit' => 800,
            'phones' => ['731124325'],
        ],
        [
            'name' => 'Vodičková Květa',
            'department' => 'ústavně - právní výbor',
            'limit' => 1200,
            'phones' => ['602232145'],
        ],
        [
            'name' => 'Štádlerová Markéta',
            'department' => 'ústavně - právní výbor',
            'limit' => 450,
            'phones' => ['731124307'],
        ],
        [
            'name' => 'Doležalová Alena',
            'department' => 'výbor pro zahraniční věci, obranu a bezpečnost',
            'limit' => 1300,
            'phones' => ['602288587'],
        ],
        [
            'name' => 'Langová Lenka',
            'department' => 'výbor pro zahraniční věci, obranu a bezpečnost',
            'limit' => 450,
            'phones' => ['731124302'],
        ],
        [
            'name' => 'Hauserová Jana',
            'department' => 'výbor pro vzdělávání, vědu, kulturu, lidská práva a petice',
            'limit' => 1200,
            'phones' => ['607060523'],
        ],
        [
            'name' => 'Poláková Aranka',
            'department' => 'výbor pro vzdělávání, vědu, kulturu, lidská práva a petice',
            'limit' => 450,
            'phones' => ['731124346'],
        ],
        [
            'name' => 'Šarmanová Kateřina',
            'department' => 'výbor pro územní rozvoj, veřejnou správu a životní prostředí',
            'limit' => 1200,
            'phones' => ['731124342'],
        ],
        [
            'name' => 'Jindřichová Iva',
            'department' => 'výbor pro územní rozvoj, veřejnou správu a životní prostředí',
            'limit' => 450,
            'phones' => ['731124367'],
        ],
        [
            'name' => 'Jůzová Andrea',
            'department' => 'výbor pro hospodářství,zemědělství a dopravu',
            'limit' => 1200,
            'phones' => ['731124344'],
        ],
        [
            'name' => 'Nováková Radka',
            'department' => 'výbor pro hospodářství,zemědělství a dopravu',
            'limit' => 450,
            'phones' => ['731124386'],
        ],
        [
            'name' => 'Brabcová Václava',
            'department' => 'výbor pro  sociální politiku',
            'limit' => 1200,
            'phones' => ['724060180'],
        ],
        [
            'name' => 'Vicari Kateřina',
            'department' => 'výbor pro  sociální politiku',
            'limit' => 450,
            'phones' => ['731124393'],
        ],
        [
            'name' => 'Mejdrechová Silvie',
            'department' => 'výbor pro zdravotnictví',
            'limit' => 450,
            'phones' => ['704644456'],
        ],
        [
            'name' => 'Götthansová Štěpánka',
            'department' => 'výbor pro záležitosti Evropské unie',
            'limit' => 1200,
            'phones' => ['602242660'],
        ],
        [
            'name' => 'Babčanová Gabriela',
            'department' => 'výbor pro záležitosti Evropské unie',
            'limit' => 450,
            'phones' => ['731124387'],
        ],
        [
            'name' => 'Vacková Bronislava',
            'department' => 'stálá komise Senátu pro krajany žijící v zahraničí',
            'limit' => 800,
            'phones' => ['602232129'],
        ],
        [
            'name' => 'Kysela Jan',
            'department' => 'stálá komise Senátu pro Ústavu České rep. a parlamentní procedury',
            'limit' => 872,
            'phones' => ['602339028'],
        ],
        [
            'name' => 'Pilařová Jitka',
            'department' => 'stálá komise Senátu pro sdělovací prostředky + stálá komise Senátu pro rozvoj venkova',
            'limit' => 900,
            'phones' => ['731124352'],
        ],
        [
            'name' => 'Eberle Milan',
            'department' => 'odbor legislativní',
            'limit' => 1200,
            'phones' => ['731124320'],
        ],
        [
            'name' => 'Lifka Jiří',
            'department' => 'odbor legislativní',
            'limit' => 450,
            'phones' => ['731124343'],
        ],
        [
            'name' => 'Sýkorová Eva',
            'department' => 'odbor legislativní',
            'limit' => 450,
            'phones' => ['731124361'],
        ],
        [
            'name' => 'Kubátová Gabriela',
            'department' => 'odbor legislativní',
            'limit' => 509,
            'phones' => ['602402855'],
        ],
        [
            'name' => 'Fiantová Marie',
            'department' => 'samostatné odd. personální',
            'limit' => 752,
            'phones' => ['604223185'],
        ],
        [
            'name' => 'Novotná Anna',
            'department' => 'samostatné odd. právní',
            'limit' => 450,
            'phones' => ['731542460'],
        ],
        [
            'name' => 'Šillíková Tereza',
            'department' => 'samostatné odd. právní',
            'limit' => 450,
            'phones' => ['731124309'],
        ],
        [
            'name' => 'Pavelcová Irena',
            'department' => 'samostatné odd. právní',
            'limit' => 450,
            'phones' => ['731124364'],
        ],
        [
            'name' => 'Malášková Tereza',
            'department' => 'samostatné odd. právní',
            'limit' => 450,
            'phones' => ['602232139'],
        ],
        [
            'name' => 'Ciprová Valérie',
            'department' => 'samostatné odd. protokolu',
            'limit' => 700,
            'phones' => ['731124340'],
        ],
        [
            'name' => 'Barvířová Barbora',
            'department' => 'samostatné odd. protokolu',
            'limit' => 700,
            'phones' => ['731124369'],
        ],
        [
            'name' => 'Pinterová Klára',
            'department' => 'samostatné odd. protokolu',
            'limit' => 800,
            'phones' => ['731124385'],
        ],
        [
            'name' => 'Plačková  Alena',
            'department' => 'samostatné odd. protokolu',
            'limit' => 800,
            'phones' => ['724685038'],
        ],
        [
            'name' => 'Hellová Eva',
            'department' => 'samostatné odd. protokolu',
            'limit' => 800,
            'phones' => ['731124341'],
        ],
        [
            'name' => 'Fišerová Marta',
            'department' => 'samostatné odd. protokolu',
            'limit' => 700,
            'phones' => ['731124399'],
        ],
        [
            'name' => 'Kefurtová Dominika',
            'department' => 'samostatné odd. protokolu',
            'limit' => 1200,
            'phones' => ['731124371'],
        ],
        [
            'name' => 'Kykalová Jana',
            'department' => 'samostatné odd. protokolu',
            'limit' => 800,
            'phones' => ['731124376'],
        ],
        [
            'name' => 'Vondruška Jana',
            'department' => 'samostatné odd. protokolu',
            'limit' => 700,
            'phones' => ['724521308'],
        ],
        [
            'name' => 'Markosianová Kristýna',
            'department' => 'samostatné odd. protokolu',
            'limit' => 700,
            'phones' => ['702358903'],
        ],
        [
            'name' => 'Kyselová Sylva',
            'department' => 'ředitel sekce senátní + odbor organizační',
            'limit' => 2000,
            'phones' => ['731124332'],
        ],
        [
            'name' => 'Knotek Jan',
            'department' => 'ředitel sekce senátní + odbor organizační',
            'limit' => 1210,
            'phones' => ['731124330'],
        ],
        [
            'name' => 'Ječmenová Kristýna',
            'department' => 'ředitel sekce senátní + odbor organizační',
            'limit' => 450,
            'phones' => ['704644459'],
        ],
        [
            'name' => 'Oberfalzerová Martina',
            'department' => 'ředitel sekce senátní + odbor organizační',
            'limit' => 0,
            'phones' => ['731124331'],
        ],
        [
            'name' => 'Krbec Jiří',
            'department' => 'odbor zahraniční',
            'limit' => 2000,
            'phones' => ['731124327'],
        ],
        [
            'name' => 'Vacík Lukáš',
            'department' => 'odbor zahraniční',
            'limit' => 800,
            'phones' => ['602232136'],
        ],
        [
            'name' => 'Tillová Jana',
            'department' => 'odbor zahraniční',
            'limit' => 501,
            'phones' => ['731124328'],
        ],
        [
            'name' => 'Šuchmanová Adéla',
            'department' => 'odbor zahraniční',
            'limit' => 450,
            'phones' => ['604295915'],
        ],
        [
            'name' => 'Zemanová Radka',
            'department' => 'odbor zahraniční',
            'limit' => 450,
            'phones' => ['731124339'],
        ],
        [
            'name' => 'Merkl Radek',
            'department' => 'oddělení zahraničních vztahů',
            'limit' => 900,
            'phones' => ['731124329'],
        ],
        [
            'name' => 'Košaříková Kateřina',
            'department' => 'oddělení zahraničních vztahů',
            'limit' => 501,
            'phones' => ['731124365'],
        ],
        [
            'name' => 'Grinc Jan - Lišková Raimonda',
            'department' => 'oddělení pro EU',
            'limit' => 900,
            'phones' => ['723247784'],
        ],
        [
            'name' => 'Turanová Iva',
            'department' => 'oddělení pro EU',
            'limit' => 450,
            'phones' => ['731124389'],
        ],
        [
            'name' => 'Petřík Milan',
            'department' => 'oddělení pro EU',
            'limit' => 450,
            'phones' => ['607747182'],
        ],
        [
            'name' => 'Vlčková Zuzana',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 872,
            'phones' => ['602152641'],
        ],
        [
            'name' => 'Pelantová Eva',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 450,
            'phones' => ['731124349'],
        ],
        [
            'name' => 'Davidová Kristýna',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 450,
            'phones' => ['604228237'],
        ],
        [
            'name' => 'Chmelíček Vojtěch',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 874,
            'phones' => ['731124372'],
        ],
        [
            'name' => 'Rajnochvá (Barnhart) Nora',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 450,
            'phones' => ['731124353'],
        ],
        [
            'name' => 'Koutová Svatava',
            'department' => 'odbor vnějších vztahů a služeb',
            'limit' => 450,
            'phones' => ['704644457'],
        ],
        [
            'name' => 'Kašpárková Lucie',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 872,
            'phones' => ['734550484'],
        ],
        [
            'name' => 'Michálková Irena',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 450,
            'phones' => ['731124390'],
        ],
        [
            'name' => 'odd. produkce lékař',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 450,
            'phones' => ['604228240'],
        ],
        [
            'name' => 'Krbcová Eliška',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 450,
            'phones' => ['774904346'],
        ],
        [
            'name' => 'Pavlík Michal',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 644,
            'phones' => ['731124377'],
        ],
        [
            'name' => 'Pavlík Michal data',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 303,
            'phones' => ['704644463'],
        ],
        [
            'name' => 'Růžička Jan',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 874,
            'phones' => ['603323378'],
        ],
        [
            'name' => 'Kučerová Anna',
            'department' => 'oddělení vztahů s veřejností',
            'limit' => 874,
            'phones' => ['731124378'],
        ],
        [
            'name' => 'Švecová Barbora',
            'department' => 'oddělení senátních služeb',
            'limit' => 450,
            'phones' => ['731124324'],
        ],
        [
            'name' => 'Owenová Zdenka',
            'department' => 'oddělení senátních služeb',
            'limit' => 450,
            'phones' => ['604228238'],
        ],
        [
            'name' => 'Pinkasová Kateřina',
            'department' => 'oddělení senátních služeb',
            'limit' => 450,
            'phones' => ['731124360'],
        ],
        [
            'name' => 'Vaculíková Jiřina',
            'department' => 'oddělení senátních služeb',
            'limit' => 450,
            'phones' => ['731124383'],
        ],
        [
            'name' => 'Dvořáková Lucie',
            'department' => 'oddělení senátních služeb',
            'limit' => 450,
            'phones' => ['731124370'],
        ],
        [
            'name' => 'Sussenmilchová Ivana',
            'department' => 'odbor ekonomický',
            'limit' => 1200,
            'phones' => ['731124366'],
        ],
        [
            'name' => 'Ondrišová Stanislava',
            'department' => 'oddělení rozpočtu',
            'limit' => 450,
            'phones' => ['739058107'],
        ],
        [
            'name' => 'Novosadová Zdena',
            'department' => 'oddělení rozpočtu',
            'limit' => 201,
            'phones' => ['731124357'],
        ],
        [
            'name' => 'Novotný Martin',
            'department' => 'oddělení účetnictví',
            'limit' => 450,
            'phones' => ['731124397'],
        ],
        [
            'name' => 'Viterová Jaromíra',
            'department' => 'oddělení účetnictví',
            'limit' => 50,
            'phones' => ['604228231'],
        ],
        [
            'name' => 'Štěpková Eva',
            'department' => 'oddělení účetnictví',
            'limit' => 50,
            'phones' => ['604228232'],
        ],
        [
            'name' => 'Vavříková Milena',
            'department' => 'oddělení účetnictví',
            'limit' => 50,
            'phones' => ['604228233'],
        ],
        [
            'name' => 'Stropnická Daniela',
            'department' => 'oddělení účetnictví',
            'limit' => 50,
            'phones' => ['604228234'],
        ],
        [
            'name' => 'Zatloukalová Gabriela',
            'department' => 'oddělení účetnictví',
            'limit' => 50,
            'phones' => ['731124321'],
        ],
        [
            'name' => 'Svoboda Miroslav - Krizové ředitel OTZ',
            'department' => 'odbor technického zajištění',
            'limit' => 752,
            'phones' => ['704644465'],
        ],
        [
            'name' => 'Fiřtová Eva',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['731124347'],
        ],
        [
            'name' => 'Klvaň Martin',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['731124382'],
        ],
        [
            'name' => 'Hokešová Markéta',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['604228239'],
        ],
        [
            'name' => 'Fajt Jiří',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['731124348'],
        ],
        [
            'name' => 'Outrata Jiří',
            'department' => 'oddělení správy majetku',
            'limit' => 644,
            'phones' => ['606674356'],
        ],
        [
            'name' => 'Stehnová Svobodová Václava',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['735191740'],
        ],
        [
            'name' => 'Míková Alexandra',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['734587143'],
        ],
        [
            'name' => 'Sedlmajerová Simona',
            'department' => 'oddělení správy majetku',
            'limit' => 450,
            'phones' => ['731124381'],
        ],
        [
            'name' => 'Pražan Tomáš',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['723523933'],
        ],
        [
            'name' => 'Karger Roman',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124314'],
        ],
        [
            'name' => 'Zeman Martin',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124313'],
        ],
        [
            'name' => 'Riedl Daniel',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124317'],
        ],
        [
            'name' => 'Rajtmajer Antonín',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124308'],
        ],
        [
            'name' => 'Panoch Daniel',
            'department' => 'oddělení autoprovozu',
            'limit' => 509,
            'phones' => ['602113980'],
        ],
        [
            'name' => 'Zima Martin',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124315'],
        ],
        [
            'name' => 'Kramný Otakar',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124395'],
        ],
        [
            'name' => 'Šterner Jan',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['731124312'],
        ],
        [
            'name' => 'Eiben Rudolf',
            'department' => 'oddělení autoprovozu',
            'limit' => 450,
            'phones' => ['602656404'],
        ],
        [
            'name' => 'Illek Petr',
            'department' => 'odbor gastronomických služeb',
            'limit' => 450,
            'phones' => ['731124335'],
        ],
        [
            'name' => 'Coufal Jan',
            'department' => 'odbor gastronomických služeb',
            'limit' => 450,
            'phones' => ['731124334'],
        ],
        [
            'name' => 'Svoboda Josef',
            'department' => 'odbor informačních technologií',
            'limit' => 1474,
            'phones' => ['731124388'],
        ],
        [
            'name' => 'Svoboda Josef',
            'department' => 'odbor informačních technologií',
            'limit' => 250,
            'phones' => ['607956847'],
        ],
        [
            'name' => 'Michaela Jeřichová',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['704644460'],
        ],
        [
            'name' => 'Polanka Vladimír',
            'department' => 'odbor informačních technologií',
            'limit' => 800,
            'phones' => ['731124333'],
        ],
        [
            'name' => 'Polanka Vladimír',
            'department' => 'odbor informačních technologií',
            'limit' => 545,
            'phones' => ['702115920'],
        ],
        [
            'name' => 'Jan Černohorský',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['607278861'],
        ],
        [
            'name' => 'Topinka Stanislav',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['731124362'],
        ],
        [
            'name' => 'Topinka Stanislav',
            'department' => 'odbor informačních technologií',
            'limit' => 423,
            'phones' => ['731124379'],
        ],
        [
            'name' => 'Pešek Vladimír',
            'department' => 'odbor informačních technologií',
            'limit' => 900,
            'phones' => ['603501139'],
        ],
        [
            'name' => 'Pešek Vladimír',
            'department' => 'odbor informačních technologií',
            'limit' => 423,
            'phones' => ['731124374'],
        ],
        [
            'name' => 'Pešek Vladimír',
            'department' => 'odbor informačních technologií',
            'limit' => 250,
            'phones' => ['731124375'],
        ],
        [
            'name' => 'Cinger Jan',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['731124323'],
        ],
        [
            'name' => 'Vokurka Petr',
            'department' => 'odbor informačních technologií',
            'limit' => 872,
            'phones' => ['724795089'],
        ],
        [
            'name' => 'Stejskal František',
            'department' => 'odbor informačních technologií',
            'limit' => 752,
            'phones' => ['724606919'],
        ],
        [
            'name' => 'Ječmen Jaroslav',
            'department' => 'odbor informačních technologií',
            'limit' => 509,
            'phones' => ['601553530'],
        ],
        [
            'name' => 'Blachowicz Jaroslav',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['731124305'],
        ],
        [
            'name' => 'Pinkas Roman',
            'department' => 'odbor informačních technologií',
            'limit' => 874,
            'phones' => ['774493482'],
        ],
        [
            'name' => 'Trnka Martin',
            'department' => 'odbor informačních technologií',
            'limit' => 752,
            'phones' => ['605266943'],
        ],
        [
            'name' => 'Rusnák Aleš',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['604228236'],
        ],
        [
            'name' => 'Weinfurt Filip',
            'department' => 'odbor informačních technologií',
            'limit' => 450,
            'phones' => ['704644458'],
        ],
        [
            'name' => 'Kubizňák Jiří',
            'department' => 'bezpečnostní ředitel',
            'limit' => 509,
            'phones' => ['725002610'],
        ],
        ];

        foreach ($people as $entry) {
            DB::transaction(function () use ($entry) {
                $phones = $entry['phones'];
                unset($entry['phones']);

                if (empty($phones)) {
                    return;
                }

                $primaryPhone = $phones[0];

                if (DB::table('person_phones')->where('phone', $primaryPhone)->exists()) {
                    return;
                }

                /** @var Person $person */
                $person = Person::create([
                    'name' => $entry['name'],
                    'department' => $entry['department'],
                    'limit' => $entry['limit'],
                ]);

                foreach ($phones as $phone) {
                    $person->phones()->create(['phone' => $phone]);
                }
            });
        }
    }
}
