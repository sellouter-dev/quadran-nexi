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

## 📌 1. Download dati per AmazonSpReportAmazonvatcalculation

Questo comando riempie la tabella `AmazonSpReportAmazonvatcalculation`, che verrà utilizzata come join nei comandi successivi. **Non genera alcun file CSV**, ma serve solo a popolare i dati necessari.

```sh
php artisan app:download-flatfile-vat-data
```

---

## 📌 2. Download dati per AmazonSpReportFlatfilev2settlement e generazione CSV

Questo comando popola la tabella `AmazonSpReportFlatfilev2settlement` e genera il file CSV denominato:

**`FlatFileSettlement_timestamp.csv`**

Il comando utilizza anche la tabella `AmazonSpReportAmazonvatcalculation` tramite una `JOIN`.

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

1. **Download dati AmazonSpReportAmazonvatcalculation**
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
**Comando che permette di riempire la tabella AmazonSpReportAmazonvatcalculation che viene utilizzata come join nel prossimo comando, il comando in questione non genererà nessun file csv, ma riempirà solo la tabella**

> php artisan app:download-flatfile-vat-data

**Comando per riempire la tabella AmazonSpReportFlatfilev2settlement, e permette di generare il file csv chiamato: _FlatFileSettlement_timestamp_ utilizzando anche la tabella AmazonSpReportAmazonvatcalculation trmite Join**

> php artisan app:download-collections-data-command

**Comando utilizzato per riempire la tabella AmazonSpReportFlatfilevatinvoicedatavidr e generare il file csv chiamato Flatfilevatinvoicedata_timestamp**.

> php artisan app:download-flatfile-vat-invoice-data-command

**Comando che permette di generare il file csv chiamato _InvoiceTrack_timestamp_ prendendo i dati dalla tabella AmazonSpReportFlatfilevatinvoicedatavidr**

> php artisan app:download-data-calculation-computed-command

## API INVENTORY

> php artisan app:save-seller-inventory-items-command
