# Flatsome Motion

WordPress-plugin som gjør Flatsome-animasjoner raskere, og legger til et eget scroll-basert reveal-system med valg direkte på UX-elementer.

**Versjon:** 2.0.1  
**Forfatter:** Benjamin Pedersen

Tidligere navn: Flatsome Better Animations. Slug og innstillingsnøkkel er uendret (`flatsome-better-animations` / `fba_settings`).

## Krav

- WordPress
- Flatsome-tema (for `data-animate`-override og UX Builder-felt; `.reveal` fungerer uavhengig)

## Installasjon

1. Last ned release-zip fra [Releases](https://github.com/bli-synlig-plugins/flatsome-better-animations/releases), eller klon dette repoet.
2. Legg mappen `flatsome-better-animations` i `wp-content/plugins/`.
3. Aktiver **Flatsome Motion** under **Plugins**.

## Innstillinger

**Innstillinger → Flatsome Motion**

- Standardanimasjon for `.reveal` uten variant, med live forhåndsvisning.
- Timing, easing, stagger og synlighetsterskel.
- Tekstanimasjon (stil, oppdeling, stagger).
- Overstyring av Flatsomes `data-animate`.
- Tilgjengelighet: respekter «reduser bevegelse», last skript forsinket.

## UX Builder

På kolonne, rad, seksjon, tittel, tekst, knapp, bilde og flere andre elementer ligger gruppen **Motion**.

- **Rad / seksjon:** stagger-beholder. Sett **Stagger barn** til På og velg animasjon fritt på kolonne, tekst eller andre barn — de kommer inn etter hverandre. Valgfri animasjon her arves av kolonner som ikke har sin egen.
- **Kolonne / tekst / …:** velg animasjonen der den skal skje.
- Delay, tekstoppdeling (tittel/tekst) og hover-forhåndsvisning i velgeren.

## `.reveal`-klasser

Manuelle klasser under **Avansert → Klasse** virker fortsatt.

| Klasse | Effekt |
|--------|--------|
| `.reveal` | Standard fra innstillingene (fade + opp som utgangspunkt) |
| `.reveal.reveal--fade-up` | Fade + opp |
| `.reveal.reveal--down` / `--fade-down` | Fade + ned |
| `.reveal.reveal--left` / `--fade-left` | Fra venstre |
| `.reveal.reveal--right` / `--fade-right` | Fra høyre |
| `.reveal.reveal--fade` | Kun fade |
| `.reveal.reveal--zoom` / `--zoom-in` | Zoom inn |
| `.reveal.reveal--zoom-out` | Zoom ut |
| `.reveal.reveal--blur` | Uskarp |
| `.reveal.reveal--wipe` | Maske opp |
| `.reveal.reveal--wipe-l` | Maske fra venstre |
| `.reveal.reveal--tilt` | 3D-tilt |
| `.reveal.reveal--flip` | Snu |

**Stagger:** pakk elementer i `.reveal-stagger`.  
**Delay:** `data-reveal-delay="300"` (ms).  
**Tekst:** `.reveal-text` med `--rise`, `--fade`, `--blur` eller `--rot`, og `--tegn` / `--ord` / `--linje`.

Respekterer `prefers-reduced-motion` når innstillingen er på. Uten JavaScript vises innhold umiddelbart.

## Lisens

Ingen lisens er angitt i repoet ennå.
