# Flatsome Better Animations

WordPress-plugin som gjør Flatsome-animasjoner raskere og mer moderne, og legger til et eget scroll-basert `.reveal`-system.

**Versjon:** 1.0.2  
**Forfatter:** Benjamin Pedersen

## Krav

- WordPress
- Flatsome-tema (for `data-animate`-override; `.reveal` fungerer uavhengig av animasjonstype)

## Installasjon

1. Last ned release-zip fra [Releases](https://github.com/bli-synlig-plugins/flatsome-better-animations/releases), eller klon dette repoet.
2. Legg mappen `flatsome-better-animations` i `wp-content/plugins/`.
3. Aktiver **Flatsome Better Animations** under **Plugins**.

## Innstillinger

**Innstillinger → Better Animations**

### Flatsome `data-animate` override

- Overstyrer Flatsomes trege standard-tider og waypoint-offset for `[data-animate]`.
- Juster transform-varighet, opacity-varighet, forflytningsdistanse og waypoint-offset (%).

### `.reveal` scroll-system

- Aktiverer CSS/JS for egne scroll-animasjoner.
- Stagger mellom barn i `.reveal-stagger` og synlighetsterskel (%) kan justeres.

## `.reveal`-klasser

| Klasse | Effekt |
|--------|--------|
| `.reveal` | Fade + opp (standard) |
| `.reveal.reveal--down` | Fade + ned |
| `.reveal.reveal--left` | Fra venstre |
| `.reveal.reveal--right` | Fra høyre |
| `.reveal.reveal--fade` | Kun fade |
| `.reveal.reveal--zoom` | Zoom inn |
| `.reveal.reveal--zoom-out` | Zoom ut |
| `.reveal.reveal--tilt` | 3D-tilt |

**Stagger:** pakk elementer i `.reveal-stagger`.  
**Delay:** `data-reveal-delay="300"` (ms).

Respekterer `prefers-reduced-motion`. Uten JavaScript vises innhold umiddelbart (`noscript` + `no-js`-fallback).

## Lisens

Ingen lisens er angitt i repoet ennå.
