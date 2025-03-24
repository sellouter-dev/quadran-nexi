# Nexi APP

questo branch permette di richiamare la macchina sellouter per fornire i dati finanziari.

## Comandi Utili

**Generazione Swagger:**

nome da dare: quadran-nexi

> php artisan l5-swagger:generate

**Test Specifico**:
./vendor/bin/pest --filter testDownloadDataOfFlatfilevatinvoicedata

**Esecuzione JOB:**

> php artisan schedule:work
> php artisan queue:work
> php artisan schedule:run

## Comandi

# 📄 Generazione dei file CSV

I comandi devono essere eseguiti nell'ordine riportato per garantire una corretta raccolta ed elaborazione dei dati.

---

## 📌 1. Download dati per AmazonSpReportAmazonVatTransaction

Questo comando riempie la tabella `AmazonSpReportAmazonVatTransaction`, che verrà utilizzata come join nei comandi successivi. **Non genera alcun file CSV**, ma serve solo a popolare i dati necessari.

```sh
php artisan app:download-flatfile-vat-data
```

---

## 📌 2. Download dati per AmazonSpReportFlatfilev2settlement e generazione CSV

Questo comando popola la tabella `AmazonSpReportFlatfilev2settlement` e genera il file CSV denominato:

**`FlatFileSettlement_timestamp.csv`**

Il comando utilizza anche la tabella `AmazonSpReportAmazonVatTransaction` tramite una `JOIN`.

```sh
php artisan app:download-collections-data-command
```

---

## 📌 3. Download dati per AmazonSpReportFlatfilevatinvoicedatavidr e generazione CSV

Questo comando riempie la tabella `AmazonSpReportFlatfilevatinvoicedatavidr` e genera il file CSV denominato:

**`Flatfilevatinvoicedata_timestamp.csv`**

```sh
php artisan app:download-flatfile-vat-invoice-data-command
```

---

## 📌 4. Generazione del file CSV InvoiceTrack

Questo comando genera il file CSV denominato:

**`InvoiceTrack_timestamp.csv`**

I dati vengono estratti dalla tabella `AmazonSpReportFlatfilevatinvoicedatavidr`.

```sh
php artisan app:download-data-calculation-computed-command
```

---

## ✅ Ordine di esecuzione consigliato

Per garantire il corretto funzionamento, eseguire i comandi in questo ordine:

1. **Download dati AmazonSpReportAmazonVatTransaction**
    ```sh
    php artisan app:download-flatfile-vat-data
    ```
2. **Download dati AmazonSpReportFlatfilev2settlement + generazione `FlatFileSettlement.csv`**
    ```sh
    php artisan app:download-collections-data-command
    ```
3. **Download dati AmazonSpReportFlatfilevatinvoicedatavidr + generazione `Flatfilevatinvoicedata.csv`**
    ```sh
    php artisan app:download-flatfile-vat-invoice-data-command
    ```
4. **Generazione del file `InvoiceTrack.csv`**
    ```sh
    php artisan app:download-data-calculation-computed-command
    ```

---

🔹 **Nota:** Assicurarsi che ogni comando venga completato con successo prima di eseguire il successivo, per evitare errori nei dati generati. 🚀

I comandi devono essere eseguiti nell'ordine riportato, per garantire una corretta presa dei dati
**Comando che permette di riempire la tabella AmazonSpReportAmazonVatTransaction che viene utilizzata come join nel prossimo comando, il comando in questione non genererà nessun file csv, ma riempirà solo la tabella**

> php artisan app:download-flatfile-vat-data

**Comando per riempire la tabella AmazonSpReportFlatfilev2settlement, e permette di generare il file csv chiamato: _FlatFileSettlement_timestamp_ utilizzando anche la tabella AmazonSpReportAmazonVatTransaction trmite Join**

> php artisan app:download-collections-data-command

**GENERAZIONE FILE PAGAMENTI**
**Comando utilizzato per riempire la tabella AmazonSpReportFlatfilevatinvoicedatavidr e generare il file csv chiamato Payment_timestamp**.

> php artisan app:download-flatfile-vat-invoice-data-command

**GENERAZIONE FILE ANAGRAFICHE**
**Comando che permette di generare il file csv chiamato Personal_Data_timestamp prendendo i dati dalla tabella AmazonSpReportFlatfilevatinvoicedatavidr**

> php artisan app:download-data-calculation-computed-command

**GENERAZIONE FILE TRANSAZIONI**
**Comando che permette di generare il file csv chiamato Transaction_timestamp i dati dalla tabella AmazonSpReportFlatfilevatinvoicedatavidr**

## 📌 Comando `app:download-collections-data-command`

Scarica i dati della collezione AmazonSpReportFlatfilev2settlement e genera il file CSV `FlatFileSettlement_timestamp.csv`.

**Parametri disponibili:**

| Valore | Descrizione                               |
| ------ | ----------------------------------------- |
| `0`    | (Default) Scarica i dati e genera il CSV  |
| `1`    | Genera solo il CSV senza scaricare i dati |
| `2`    | Scarica solo i dati senza generare il CSV |

**Esempi:**

```sh
# Scarica i dati e genera il CSV (default)
php artisan app:download-collections-data-command

# Genera solo il CSV
php artisan app:download-collections-data-command 1

# Scarica solo i dati
php artisan app:download-collections-data-command 2
```

---

## 📌 Comando `app:download-flatfile-vat-invoice-data-command`

Scarica i dati della collezione AmazonSpReportFlatfilevatinvoicedatavidr e genera il file CSV `Flatfilevatinvoicedata_timestamp.csv`.

**Parametri disponibili:**

| Valore | Descrizione                               |
| ------ | ----------------------------------------- |
| `0`    | (Default) Scarica i dati e genera il CSV  |
| `1`    | Genera solo il CSV senza scaricare i dati |
| `2`    | Scarica solo i dati senza generare il CSV |

**Esempi:**

```sh
# Scarica i dati e genera il CSV (default)
php artisan app:download-flatfile-vat-invoice-data-command

# Genera solo il CSV
php artisan app:download-flatfile-vat-invoice-data-command 1

# Scarica solo i dati
php artisan app:download-flatfile-vat-invoice-data-command 2
```

---

## API INVENTORY

> php artisan app:save-seller-inventory-items-command

## Tabelle

amazon_sp_report_amazonvattransactions -> mensile
AMAZON_SP_REPORT_AMAZONVATCALCULATION -> giornaliera
