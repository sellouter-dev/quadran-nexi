<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\FlatfileVatInvoiceData;
use App\Services\FileEncryptionService;
use App\Services\ResponseHandler;
use Exception;
use Carbon\Carbon; // Importiamo Carbon per gestire le date

/**
 * Class CsvDataGeneratorService
 *
 * Questa classe si occupa di generare file CSV a partire dai dati presenti nei modelli InvoiceTrack,
 * FlatfileVatInvoiceData e DataCollection, successivamente esegue la crittografia del file generato.
 * Durante il processo, vengono utilizzati log consistenti sul channel "csv" per tracciare le operazioni.
 *
 * @package App\Services
 */
class CsvDataGeneratorService
{
    /**
     * @var FileEncryptionService Istanza del servizio di crittografia dei file.
     */
    protected $fileEncryptionService;

    /**
     * CsvDataGeneratorService constructor.
     *
     * Inizializza il servizio di crittografia.
     */
    public function __construct()
    {
        $this->fileEncryptionService = new FileEncryptionService();
    }

    /**
     * Genera il CSV per i dati delle InvoiceTrack.
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generateInvoiceCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per InvoiceTrack', [], 'csv-info');

        try {
            // Filtra i record di oggi
            $data = InvoiceTrack::whereDate('created_at', Carbon::today())->get()->toArray();
            $filePath = storage_path('app/temp/InvoiceTrack_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);
            ResponseHandler::success('CSV per InvoiceTrack generato con successo', ['file' => 'InvoiceTrack.csv'], 'csv-success');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Errore durante la generazione del CSV per InvoiceTrack', ['errore' => $e->getMessage()], 'csv-error');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Genera il CSV per i dati delle FlatfileVatInvoiceData.
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generateFlatfileVatCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per FlatfileVatInvoiceData', [], 'csv-info');

        try {
            // Filtra i record di oggi
            $data = FlatfileVatInvoiceData::whereDate('created_at', Carbon::today())->get()->toArray();
            $filePath = storage_path('app/temp/Flatfilevatinvoicedata_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success('CSV per FlatfileVatInvoiceData generato con successo', ['file' => 'Flatfilevatinvoicedata.csv'], 'csv-success');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Errore durante la generazione del CSV per FlatfileVatInvoiceData', ['errore' => $e->getMessage()], 'csv-error');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Genera il CSV per i dati delle DataCollection.
     *
     * Effettua il logging dell'inizio e del completamento dell'operazione, nonché degli eventuali errori.
     *
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    public function generateCollectionCSV()
    {
        ResponseHandler::info('Avvio della generazione del CSV per DataCollection', [], 'csv-info');

        try {
            // Filtra i record di oggi
            $data = DataCollection::whereDate('created_at', Carbon::today())->get()->toArray();
            $filePath = storage_path('app/temp/FlatFileSettlement_' . Carbon::today()->format('dmY') . '.csv');
            $result = $this->streamCSV($data, $filePath);

            ResponseHandler::success('CSV per DataCollection generato con successo', ['file' => 'FlatFileSettlement.csv'], 'csv-success');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Errore durante la generazione del CSV per DataCollection', ['errore' => $e->getMessage()], 'csv-error');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        }
    }

    /**
     * Crea e scrive il file CSV a partire dai dati forniti, successivamente esegue la crittografia del file.
     *
     * Vengono eseguiti controlli sul corretto accesso al file e sulla scrittura delle righe CSV.
     * In caso di errore, viene registrato l'evento e restituita una risposta JSON di errore.
     * Al termine dell'operazione, il file temporaneo viene eliminato.
     *
     * @param array  $data     Array di dati da scrivere nel CSV.
     * @param string $filePath Percorso completo del file CSV temporaneo.
     * @return mixed Risultato della crittografia del file CSV, oppure una risposta JSON in caso di errore.
     */
    private function streamCSV($data, $filePath)
    {
        try {
            ResponseHandler::info("Avvio del salvataggio del CSV sul file: $filePath", [], 'csv-info');

            $handle = fopen($filePath, 'w+');
            if (!$handle) {
                throw new Exception("Impossibile aprire il file: $filePath per la scrittura");
            }

            if (!empty($data)) {
                if (fputcsv($handle, array_keys($data[0])) === false) {
                    throw new Exception("Errore nella scrittura dell'header CSV sul file: $filePath");
                }
                ResponseHandler::info("Scrittura delle righe CSV sul file: $filePath", [], 'csv-info');
                foreach ($data as $row) {
                    if (fputcsv($handle, $row) === false) {
                        throw new Exception("Errore nella scrittura di una riga CSV sul file: $filePath");
                    }
                }
            }

            fclose($handle);

            $encryptedFile = $this->fileEncryptionService->saveFile($filePath);
            ResponseHandler::success("File CSV salvato e crittografato con successo", ['filePath' => $filePath], 'csv-success');
            return $encryptedFile;
        } catch (Exception $e) {
            ResponseHandler::error("Errore durante il salvataggio del CSV sul file: $filePath", ['errore' => $e->getMessage()], 'csv-error');
            return response()->json(['errore' => $e->getMessage(), 'codice' => $e->getCode()], 500);
        } finally {
            // Rimuove il file temporaneo se esiste
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
