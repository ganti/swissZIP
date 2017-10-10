# swissZIP
Get village by Postleitzahl (ZIP-Code)

## Query
`/plz.php?plz=3073&format=(json|xml|debug)`
| category | type | description |
| plz | 4 digit number | Postleitzahl/ ZIP-Code |
| format | string | (json | xml | debug) |

## Response
| category | type | value | description |
| ------ | ------ | ------ | ------ |
| status | count | number | how many results |
| status | distinct | 0 or 1 | there was exactly one result |
| status | status | 'ok' or 'error' | general information if request was successful |
| status | error['name'] | string | error |
| status | error['description'] | string | description of error |
| data | plz | 4 digit number | Postleitzahl/ ZIP-Code |
| data | bfs | 4 digit number | GemeindeNummer, Official Village ID |
| data | share | number | percentage of area |
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
                    [share] => 99.7
                )
            [1] => Array
                (
                    [plz] => 3073
                    [bfs] => 351
                    [canton] => BE
                    [village] => Bern
                    [share] => 0.2
                )
            [2] => Array
                (
                    [plz] => 3073
                    [bfs] => 363
                    [canton] => BE
                    [village] => Ostermundigen
                    [share] => 0.1
                )
        )
)
```
## Datasource
from the official commune register, last update of data: October 2017
https://www.bfs.admin.ch/bfs/de/home/grundlagen/agvch.html

## MIT License
Copyright (c) 2017 github.com/ganti

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.