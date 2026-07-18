# Dashboard Time Type Fix

## Pwoblèm

Dashboard Agent la te rele `format()` sou rezilta `TimeService::todayStart()` ak `TimeService::todayEnd()`, alòske metòd sa yo deja retounen string SQL. Sa te lakòz:

```text
Call to a member function format() on string
```

## Koreksyon

- `mobile_dashboard_metrics.php` itilize string yo dirèkteman.
- `TimeService` konsève API string li yo pou konpatibilite.
- Nou ajoute metòd tipé `todayStartDateTime()` ak `todayEndDateTime()` pou kote ki bezwen `DateTimeImmutable`.
- Pa gen chanjman nan layout Flutter oswa nan kalkil finansye yo.

## Règ teknik

- `todayStart()` / `todayEnd()` => string SQL.
- `todayStartDateTime()` / `todayEndDateTime()` => `DateTimeImmutable`.
