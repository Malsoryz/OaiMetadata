# OAI
Dokumentasi mengenai __OAI__ selama Development

Struktur data `XML` untuk repositori __OAI__ diharuskan menggunakan nama `OAI-PMH` sebagai root element dari `XML` nya.

__OAI__ memerlukan data dari `datestamp` hasil response nya, sebagai informasi pada tanggal apa response di berikan, dan juga memerlukan `request URL` untuk memberitahu pada url apa request __OAI__ di minta.

__OAI__ menggunakan `XML` sebagai response dari request yang di minta dari client, request juga memerlukan parameter pentingnya yaitu __verb__ sebagai perintah atau aksi yang akan dikirim ke server, jika tanpa __verb__ maka permintaan ke server tidak bisa di proses karena tidak tau apa yang harus dilakukan.

Parameter __verb__ memiliki value yang telah di definisikan beberapa di antaranya:
- Identify
- ListRecords
- ListSets
- ListMetadataFormats
- ListIdentifiers

Parameter __verb__ diberikan value yang bukan dari list yang ada di atas maka akan mengembalikan error `badVerb`.

Setiap repositori __OAI__ harus menerapkan stylesheet khusus `XML` yaitu `XSLT` (eXtensible Stylesheet Language Transformation) agar ketika url dibuka melalui browser maka akan menampilkan `HTML`, dan jika melalui Harvester maka akan mengembalikan `XML` untuk di parser datanya.

`XSLT` adalah `XSL` yang ditambahkan fitur transformasi.

Format default untuk metadata dari __OAI__ biasanya adalah __DC__ (Dublin Core), dikarenakan sederhana, memiliki kesesuaian dengan `OAI-PMH` dan fleksible. __DC__ juga sudah menjadi standar internasional (ISO 15846, NISO Z39.85). yang dimana maksudnya adalah semua orang pasti tahu bahasa dasarnya.