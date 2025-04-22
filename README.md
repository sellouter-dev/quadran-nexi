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

## 📄 Comandi di gestione dati

I comandi seguenti devono essere eseguiti **nell'ordine riportato** per garantire una corretta raccolta ed elaborazione dei dati.  
I vecchi comandi con parametri sono stati sostituiti da **6 comandi dedicati**, ciascuno con un compito specifico.

---

### 📌 1. Fetch dati Amazon VAT Transaction

Popola la tabella `AmazonSpReportAmazonVatTransaction`.  
**Non genera file CSV**, ma serve come base per i comandi successivi.

```bash
php artisan app:fetch-vat-transaction-data
```

---

### 📌 2. Fetch dati Flatfile v2 Settlement

Popola la tabella `AmazonSpReportFlatfilev2settlement`.  
**Non genera file CSV**, ma è usato nel comando successivo.

```bash
php artisan app:fetch-flatfilev2
```

---

### 📌 3. Fetch dati Invoice VAT VIDR

Popola la tabella `AmazonSpReportFlatfilevatinvoicedatavidr`.  
**Non genera file CSV**, ma è necessario per i comandi successivi.

```bash
php artisan app:fetch-invoice-data-vidr
```

---

### 📌 4. Generazione CSV Pagamenti

Genera il file CSV **`Payment_TIMESTAMP.csv`** utilizzando:

-   `AmazonSpReportFlatfilev2settlement`
-   `AmazonSpReportAmazonVatTransaction`

```bash
php artisan app:generate-csv-payment
```

---

### 📌 5. Generazione CSV Anagrafica

Genera il file CSV **`Personal_Data_TIMESTAMP.csv`** utilizzando:

-   `AmazonSpReportAmazonVatTransaction`

```bash
php artisan app:generate-csv-personaldata
```

---

### 📌 6. Generazione CSV Transazioni

Genera il file CSV **`Transaction_TIMESTAMP.csv`** utilizzando:

-   `AmazonSpReportFlatfilevatinvoicedatavidr`

```bash
php artisan app:generate-csv-transaction
```

---

### 📌 7. Comando Inventory (Invariato)

Popola la tabella relativa all’inventory seller.

```bash
php artisan app:save-seller-inventory-items-command
```

---

## ✅ Ordine consigliato di esecuzione

1. `php artisan app:fetch-vat-transaction-data`
2. `php artisan app:fetch-flatfilev2`
3. `php artisan app:fetch-invoice-data-vidr`
4. `php artisan app:generate-csv-payment`
5. `php artisan app:generate-csv-personaldata`
6. `php artisan app:generate-csv-transaction`

---

## API INVENTORY

> php artisan app:save-seller-inventory-items-command

## Tabelle

amazon_sp_report_amazonvattransactions -> mensile
AMAZON_SP_REPORT_AMAZONVATCALCULATION -> giornaliera
