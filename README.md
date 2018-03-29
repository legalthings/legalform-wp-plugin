# LegalThings LegalForm plugin
LegalThings kan documenten voor je creëren door een LegalDocx sjabloon te vullen met gegevens uit een LegalForm. Deze documenten worden op allerlei manier gebruikt door een WorkFlow, van het maken en versturen van contracten tot het starten van een bedrijf. Met de LegalForms plugin kunnen gebruikers op uw Wordpress website beginnen met het invullen van een LegalForm, zonder dat ze daarvoor een account nodig hebben van uw LegalThings install.

## Hoe het werkt
De plugin shortcode op uw pagina wordt vervangen door een LegalForm opgehaald uit uw LegalThings install. Een gebruiker vult deze in, waarna automatisch een extra inlog / registratie stap aan het formulier wordt toegevoegd door de plugin. Met die gegevens wordt, als nodig, een nieuwe gebruiker aangemaakt en een sessie gestart bij LegalThings. Dan wordt een WorkFlow begonnen waar de gegevens van het formulier naartoe worden gestuurd. Hierna wordt de gebruiker naar de pagina van de flow gebracht, of naar een in de shortcode opgegeven URL.

Als de gebruiker het invullen van het formulier wil uitstellen, klikt hij of zij op de knop ‘Bewaar voor later’, de ingevulde gegevens worden dan in de local storage opgeslagen. Wanneer de gebruiker terugkomt, wordt er een pop-up getoond met de vraag of er door moet worden gegaan met de ingevulde gegevens of dat er opnieuw moet worden begonnen.

## Benodigdheden
Een LegalThings install met publiekelijk toegankelijke URL is nodig en toegang tot een beheerdersaccount hiervan. De plugin is getest met de volgende software:

* Wordpress 4.9.4
* Bootstrap 3.3.5

Wij kunnen niet garanderen dat de plugin naar behoren werkt op andere versies.

## Installatie
Ga naar de *Plugin* pagina in uw Wordpress Dashboard. Klik op de knop ‘Nieuwe plugin’ en zoek naar ‘legalforms’. Als u ‘LegalForms’ door LegalThings heeft gevonden zit u goed. Installeer en activeer deze. Ga hierna naar de instellingen pagina.

## instellingen
Op de *Legalforms* pagina onder het kopje *Instellingen* dient u bij *LegalThings base URL* de URL van uw install in te vullen. Deze wordt gebruikt om sjablonen op te halen en om WorkFlows naartoe te sturen.

De opties *Standard login email* en *Standard login password* zijn optioneel en worden gebruikt als u wilt dat de WorkFlow niet begonnen wordt voor de gebruiker die de gegevens heeft ingevuld, maar voor een standaard gebruikersaccount. Dit is handig als u bijvoorbeeld een WorkFlow heeft die op basis van de gegevens in de LegalForm een advies maakt en opstuurt naar een e-mailadres gespecificeerd in het formulier. Hiervoor heeft de gebruiker zelf geen account nodig.

Met *Load plugin Bootstrap* kunt u kiezen of de plugin Bootstrap moet laden of niet. De plugin kijkt of uw Wordpress thema al Bootstrap heeft en laadt alleen als dat niet zo is, omdat het hebben van twee versies tegelijk vaak problemen geeft. Het checken gaat echter fout als u Bootstrap laadt via andere manieren dan via het gebruikelijke registeren / enqueuen, bijvoorbeeld als u een CDN gebruikt. In dit soort gevallen kunt u het laden van Bootstrap door de plugin handmatig uitschakelen.

## Shortcode
De volledige shortcode ziet er als volgt uit:

```
[legalforms template="template_ref" flow="flow_ref" material="false" standard_login="false" done_url="https://mywebsite.com/" alias_key="key" alias_value=“value"]
```

Opties:

- **Template:**
 De referentie van het LegalForm sjabloon dat laten zien moet worden.
- **Flow :**
De referentie van de WorkFlow scenario waar de de gegevens van het formulier heen gestuurd moeten worden.
- **Material :**
Of de plugin Bootstrap Material Design moet gebruiken of gewone Bootstrap. Is standaard ‘true’.
- **Standard login :**
Of de gebruiker inloggegevens moet invullen zodat de WorkFlow wordt aangemaakt voor zijn account aan of dat een standaard account wordt gebruikt. Is standaard ‘false’ .
- **Done URL :**
De URL waar de gebruiker heen wordt gestuurd als hij of zij klaar is met het invullen van het formulier. Als deze leeg is wordt de gebruiker naar de pagina van de aangemaakte WorkFlow gestuurd.
- **Alias key :**
Geef de alias key die wordt gebruikt door de WorkFlow. Optioneel.
- **Alias value:**
 Geef de alias waarde die wordt gebruikt door de WorkFlow. Optioneel.

Referenties (ook wel LegalThings Resource Identifiers, LTRI’s) kunnen worden gevonden in uw LegalThings Dashboard in de *Systeeminstellingen*, onder *Document bouwen* voor  de template en onder *Workflows beheren* voor de flow. De sjabloonreferentie van een document is de LTRI die onder het tandwieltje te vinden is en de referentie van een WorkFlow scenario is het ‘id’ veld van het WorkFlow object.

## Styling
De styling van de plugin is geïsoleerd van de rest van de pagina door voor alle CSS klassen van de plugin en zijn dependencies de id `legalforms-plugin` te zetten. Deze id kan ook gebruikt worden om het uiterlijk van het formulier aan te passen. Om bijvoorbeeld de achtergrondkleur van de knoppen groen te maken, gebruikt u de CSS code:

```
#legalforms-plugin button {
    background-color: #ff0000;
}
```

Als basis voor de styling gebruikt de plugin of Bootstrap Material Design of standaard Bootstrap.

## Updaten
Wij raden aan om altijd de meest up-to-date versie van de plugin te hebben. Dit doet u door op de *Plugin* pagina naar *LegalForms* te scrollen en in de oranje balk op ‘nu bijwerken’ te klikken. Als u geen oranje balk ziet, heeft u al de laatste versie.
