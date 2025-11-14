# Template Migration: mod_watchlist_share_list

## Übersicht

Die `mod_watchlist_share_list` Templates wurden von HTML5 zu Twig migriert.

## Neue Twig-Templates

### Standard-Template
- **Datei**: `templates/watchlist_share_list.html.twig`
- **Zweck**: Basis-Template ohne Framework-spezifische Klassen
- **Basis**: `@Contao/module/module.html.twig`

### Bootstrap 5 Template
- **Datei**: `templates/watchlist_share_list_bs5.html.twig`
- **Zweck**: Template mit Bootstrap 5 Klassen
- **Basis**: `@HeimrichHannotWatchlist/watchlist_share_list.html.twig`

## Übersetzungen

Die Übersetzungsschlüssel wurden in YAML-Format migriert:

### Deutsche Übersetzungen
- **Datei**: `translations/messages.de.yml`
- **Namespace**: `huh.watchlist.misc.*`

### Englische Übersetzungen
- **Datei**: `translations/messages.en.yml`
- **Namespace**: `huh.watchlist.misc.*`

## Verwendete Übersetzungsschlüssel

- `huh.watchlist.misc.watchlistNotFound` - Merkliste nicht gefunden
- `huh.watchlist.misc.noItemsInCurrentWatchlist` - Keine Einträge in der Merkliste
- `huh.watchlist.misc.linkedFileMissing` - Verknüpfte Datei fehlt
- `huh.watchlist.misc.linkedEntityMissing` - Verknüpfter Beitrag fehlt
- `huh.watchlist.misc.download` - Herunterladen
- `huh.watchlist.misc.show` - Anzeigen
- `huh.watchlist.misc.downloadAll` - Alle herunterladen

## Template-Variablen

Die folgenden Variablen werden an die Templates übergeben:

- `watchlistNotFound` (boolean) - Ob die Merkliste gefunden wurde
- `title` (string) - Titel der Merkliste
- `items` (array) - Array von Merklisten-Einträgen
  - `type` - Typ des Eintrags (FILE oder ENTITY)
  - `title` - Titel des Eintrags
  - `existing` - Ob der verknüpfte Inhalt existiert
  - `figure` - Figure-HTML für Dateien (nur in BS5)
  - `imageData_file` - Bild-Daten für Dateien
  - `imageData_entityFile` - Bild-Daten für Entities
  - `downloadUrl` - Download-URL für Dateien
  - `entityUrl` - URL für Entities
- `hasDownloadableFiles` (boolean) - Ob herunterladbare Dateien vorhanden sind
- `watchlistDownloadAllUrl` (string) - URL zum Herunterladen aller Dateien

## Controller

Der `ShareListModuleController` verwendet bereits das moderne Contao 5 Attribute-System:

```php
#[AsFrontendModule(ShareListModuleController::TYPE, category: 'miscellaneous')]
class ShareListModuleController extends AbstractFrontendModuleController
```

Dies ermöglicht automatische Template-Auflösung für sowohl HTML5- als auch Twig-Templates.

## Kompatibilität

- ✅ Die alten HTML5-Templates bleiben erhalten und funktionieren weiterhin
- ✅ Neue Twig-Templates können parallel verwendet werden
- ✅ Das `customTpl` Feld im Backend erlaubt die Auswahl beider Template-Typen
- ✅ Rückwärtskompatibilität ist vollständig gewährleistet

## Template-Auswahl im Backend

Im Contao Backend können Redakteure jetzt zwischen folgenden Templates wählen:

- `mod_watchlist_share_list` (HTML5)
- `mod_watchlist_share_list_bs5` (HTML5)
- `watchlist_share_list` (Twig)
- `watchlist_share_list_bs5` (Twig)

## Moderne Template-Struktur

Die Twig-Templates verwenden moderne Contao 5 Best Practices:

1. **Vererbung**: BS5-Template erweitert das Basis-Template
2. **Übersetzungen**: Symfony Translation-System (`|trans` Filter)
3. **Type Safety**: Verwendung von `constant()` für Klassenkonstanten
4. **Namespaces**: `@HeimrichHannotWatchlist` für Bundle-Templates
5. **Includes**: `@Contao/image.html.twig` für Standard-Contao-Templates

## Testing

Nach der Migration sollten folgende Tests durchgeführt werden:

1. ✅ Merkliste mit Einträgen anzeigen
2. ✅ Leere Merkliste anzeigen
3. ✅ Nicht existierende Merkliste (watchlistNotFound)
4. ✅ Einträge mit Bildern
5. ✅ Einträge mit fehlenden verknüpften Inhalten
6. ✅ Download-Funktionalität
7. ✅ Download-All-Funktionalität
8. ✅ Responsive Darstellung (BS5)

## Nächste Schritte

Folgende weitere Templates könnten ebenfalls migriert werden:

- `mod_watchlist.html5` → `watchlist.html.twig`
- `mod_watchlist_bs4.html5` → `watchlist_bs4.html.twig`
- `mod_watchlist_bs5.html5` → `watchlist_bs5.html.twig`

