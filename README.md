# swissZIP (Schweizer Postleitzahlen API)
Get town by Postleitzahl (ZIP-Code)

Test it here: https://swisszip.api.ganti.dev/zip/3073


[![UpdateZIP](https://github.com/ganti/swissZIP/actions/workflows/updateZIP.yml/badge.svg?branch=main)](https://github.com/ganti/swissZIP/actions/workflows/updateZIP.yml)

## Query
`/zip.php?zip=3073&format=(json|xml|debug)&canton=BE` 

| category | type | value              | description                                                                                                                                                                                                                                                            |
|----------| ------ |--------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| zip      | 4 digit number | 1000-9999          | Postleitzahl/ ZIP-Code                                                                                                                                                                                                                                                 |
| format   | string | json; xml; debug   | output format                                                                                                                                                                                                                                                          |
| canton   | string | e.g AG, GR, ZH     | filter by canton                                                                                                                                                                                                                                                       |
| scope    | string | municipality, town | default: municipality; If "town" (Ortschaft) is set, note that there can be multiple towns within one municipality (Gemeinde) or multiple municipalities within a town. As example try both params with zip [7415](https://swisszip.api.ganti.dev/zip/7415?scope=town) |

## Response
| category | type | value | description                                       |
| ------ | ------ | ------ |---------------------------------------------------|
| status | count | number | how many results                                  |
| status | distinct | 0 or 1 | there was exactly one result                      |
| status | status | 'ok' or 'error' | general information if request was successful     |
| status | error['name'] | string | error                                             |
| status | error['description'] | string | description of error                              |
| data | zip | 4 digit number | Postleitzahl/ ZIP-Code                            |
| data | bfs | 4 digit number | GemeindeNummer, Official municipality ID               |
| data | town | string | Name of town (Ortschaft)                          |
| data | municipality | string | Name of municipality (official municipality name) |
| data | zip-share | number | percentage of area                                |
| data | locale | string | de, fr, it, rm                                    |

```php
(
    [status] => Array
        (
            [count] => 4
            [distinct] => 0
            [status] => ok
        )

    [data] => Array
        (
            [0] => Array
                (
                    [zip] => 3073
                    [bfs] => 356
                    [town] => Muri bei Bern
                    [municipality] => Muri bei Bern
                    [canton] => BE
                    [zip-share] => 95.58
                    [locale] => de
                )

            [1] => Array
                (
                    [zip] => 3073
                    [bfs] => 351
                    [town] => Bern
                    [municipality] => Bern
                    [canton] => BE
                    [zip-share] => 4.24
                    [locale] => de
                )

            [2] => Array
                (
                    [zip] => 3073
                    [bfs] => 363
                    [town] => Ostermundigen
                    [municipality] => Ostermundigen
                    [canton] => BE
                    [zip-share] => 0.15
                    [locale] => de
                )

            [3] => Array
                (
                    [zip] => 3073
                    [bfs] => 630
                    [town] => Allmendingen
                    [municipality] => Allmendingen
                    [canton] => BE
                    [zip-share] => 0.02
                    [locale] => de
                )

        )

)
```
## Datasource
from the official commune register
- https://data.geo.admin.ch/ch.swisstopo-vd.ortschaftenverzeichnis_plz
- https://data.geo.admin.ch/ch.swisstopo.amtliches-gebaeudeadressverzeichnis

## CC0 License
CC0-1.0  2019-2024 github.com/ganti

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIEDi
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
