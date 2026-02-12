# RT-Linky 3.0

Ein moderner, professioneller Link-in-Bio Generator für WordPress von Rettoro (www.rettoro.de).

## Autor

**Haibki** für Rettoro
- Website: https://www.rettoro.de

## Features

### Kernfeatures
- **Custom Post Type** - Profile als native WordPress Posts
- **Moderne React Admin** - React-basierte Oberfläche
- **REST API** - Vollständige REST API
- **Gutenberg Block** - Nativer WordPress Block
- **Echtzeit-Vorschau** - Live-Preview beim Bearbeiten
- **Statistik-Tracking** - Detaillierte Analytics
- **SEO optimiert** - Open Graph und Twitter Cards

### Design-Features
- Verlaufs-Hintergründe
- Einfarbige Hintergründe
- Bild-Hintergründe
- Benutzerdefinierte Farben
- Border-Radius-Anpassung
- Verifiziert-Badge
- Avatar-Unterstützung

## Anforderungen

- WordPress 6.0+
- PHP 7.4+
- Node.js 18+ (für Entwicklung)

## Installation

1. ZIP-Datei in `/wp-content/plugins/` entpacken
2. Plugin im WordPress-Backend aktivieren
3. Zu "RT-Linky" im Admin-Menü navigieren

## Verwendung

### Profil erstellen
1. RT-Linky → Profil erstellen
2. Name und Slug eingeben
3. Bio und Avatar hinzufügen
4. Design anpassen
5. Links hinzufügen
6. Speichern

### Mit Shortcode einbetten
```
[rt-linky id="123"]
[rt-linky slug="mein-profil"]
```

### Gutenberg Block verwenden
1. "RT-Linky Profile" Block hinzufügen
2. Profil aus Dropdown wählen

### URL-Zugriff
Profile erreichbar unter: `deineseite.de/link/profil-slug/`

## Für Entwickler

### Build-Prozess
```bash
cd wp-content/plugins/rt-linky

# PHP Abhängigkeiten
composer install

# Node Abhängigkeiten
npm install

# Development
npm run dev

# Production
npm run build
```

### API Endpunkte
```
GET    /wp-json/rt-linky/v1/profiles
POST   /wp-json/rt-linky/v1/profiles
GET    /wp-json/rt-linky/v1/profiles/{id}
PUT    /wp-json/rt-linky/v1/profiles/{id}
DELETE /wp-json/rt-linky/v1/profiles/{id}
GET    /wp-json/rt-linky/v1/stats
```

## Lizenz

GPL-2.0-or-later

## Changelog

### 3.0.0
- Komplette Neuentwicklung
- Custom Post Type statt JSON
- React Admin Interface
- REST API
- Gutenberg Block
- Erweiterte Statistiken
