# swissZIP
Get village by Postleitzahl (ZIP-Code)
Last Update: 18. October 2022

## Query
`/zip.php?zip=3073&format=(json | xml | debug)` 

| category | type | value | description |
| ------ | ------ | ------ | ------ |
| zip | 4 digit number | 1000-9999 | Postleitzahl/ ZIP-Code |
| format | string | json; xml; debug | output format |

## Response
| category | type | value | description |
| ------ | ------ | ------ | ------ |
| status | count | number | how many results |
| status | distinct | 0 or 1 | there was exactly one result |
| status | status | 'ok' or 'error' | general information if request was successful |
| status | error['name'] | string | error |
| status | error['description'] | string | description of error |
| data | zip | 4 digit number | Postleitzahl/ ZIP-Code |
| data | bfs | 4 digit number | GemeindeNummer, Official Village ID |
| data | zip-share | number | percentage of area (currently missing, since this data was not published for 2022) |

```php
(
    [status] => Array
        (
            [count] => 3
            [distinct] => 0
            [status] => ok
        )
    [data] => Array
        (
            [0] => Array
                (
                    [plz] => 3073
                    [bfs] => 356
                    [canton] => BE
                    [village] => Muri bei Bern
                    [zip-share] => 99.7
                )
            [1] => Array
                (
                    [plz] => 3073
                    [bfs] => 351
                    [canton] => BE
                    [village] => Bern
                    [zip-share] => 0.2
                )
            [2] => Array
                (
                    [plz] => 3073
                    [bfs] => 363
                    [canton] => BE
                    [village] => Ostermundigen
                    [zip-share] => 0.1
                )
        )
)
```
## Datasource
from the official commune register, last update of data: October 2022
- https://www.cadastre.ch/de/services/service/registry/plz.html
- https://data.geo.admin.ch/ch.swisstopo-vd.ortschaftenverzeichnis_plz

Old Links until 2019:
- https://www.bfs.admin.ch/bfs/de/home/grundlagen/agvch.html
- https://www.bfs.admin.ch/bfs/de/home/grundlagen/agvch/gwr-korrespondenztabelle.assetdetail.7226419.html

## CC0 License
CC0-1.0  2022 github.com/ganti

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