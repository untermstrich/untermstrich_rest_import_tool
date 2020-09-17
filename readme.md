untermStrich REST Import Tool
=============================

Ein Import Tool um die REST Schnittstelle von untermStich für Beginn/Ende Zeiten anzusprechen.
Sie benötigen ein installiertes und aktiviertes untermStrich ab Version X2.
http://www.untermstrich.com/

Die Schnittstelle ist hier im Detail beschrieben: 
https://webservices.untermstrich.com/h3/rest:start_end_time

Um die Zeiten zu sehen, müssen Sie in untermStrich die Beginn- und Endezeiten aktivieren:
https://webservices.untermstrich.com/h3/de:setup:setup:sideboard:hours:module_hours#beginn-_und_endezeiten

Installation
------------

Benötigt:

  * PHP ab 7.0

Konfiguration

  * Die config_example.ini in config.ini kopieren und anpassen (Direkt im root)
  * Der Start-Controller wird in `application/config/routes.php` über den Wert `$route['default_controller']` festgelegt
    * Import1.php ist ein Beispiel mit mehreren kommen und gehen Zeiten je Tag
      * `Kennung;Datum;Zeit;Richtung`
      * `1;01.03.2019;07:11;Kommt`
      * Für diesen Controller nutzen Sie bitte die daten1_example.txt als Beispieldatei.
    * Import2.php ist ein Beispiel mit einer Zeile je Tag, mit Pausen Abzug
      * `Kennung	Datum	Von	Bis	Abzug`
      * `1	11.03.2019	07:11	16:30	0.5`
      * Für diesen Controller nutzen Sie bitte die daten2_example.txt als Beispieldatei.
  * Im jeweiligen Controller, zu finden unter `application/controllers` kann am Anfang das Dateiformat konfiguriert werden,
    danach finden Sie den Programmcode, den Sie für das Einlesen der Datei anpassen können.

Start
-----

    php index.php