# swissZIPpro (DO NOT REDISTRIBUTE)
Public version: https://github.com/ganti/swissZIP

Get village by Postleitzahl (ZIP-Code)

## Query
`/zip.php?zip=3073&format=(json | xml | debug)` 

| category | type | value | description |
| ------ | ------ | ------ | ------ |
| zip | 4 digit number | 1000-9999 | Postleitzahl/ ZIP-Code |
| format | string | json; xml; debug | output format |
| showDetails | bool | 0; 1 or true; false| official name, district |


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
| data | zip-share | number | percentage of area |

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
from the official commune register, last update of data: October 2017
https://www.bfs.admin.ch/bfs/de/home/grundlagen/agvch.html

## License
Copyright (c) 2017 github.com/ganti

DO NOT REDISTRIBUTE!!!!
Public version : https://github.com/ganti/swissZIP
