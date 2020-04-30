untermStrich REST Import tool
=============================

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
    * Import2.php ist ein Beispiel mit einer Zeile je Tag, mit Pausen Abzug
      * `Kennung	Datum	Von	Bis	Abzug`
      * `1	11.03.2019	07:11	16:30	0.5`
  * Im jeweiligen Controller, zu finden unter `application/controllers` kann am Anfang das Dateiformat konfiguriert werden,
    danach finden Sie den Programmcode, den Sie für das Einlesen der Datei anpassen können.

Start
-----

    php index.php
    php index.php daten1.txt
    php index.php daten1.txt config.ini