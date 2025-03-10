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

> php artisan app:download-flatfile-vat-invoice-data-command
> php artisan app:download-collections-data-command
> php artisan app:save-seller-inventory-items-command
> php artisan app:download-data-calculation-computed-command
