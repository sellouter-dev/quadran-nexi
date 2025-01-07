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
