<?php

namespace App\Services;

use App\Models\InvoiceTrack;
use App\Models\DataCollection;
use App\Models\FlatfileVatInvoiceData;
use App\Services\FileEncryptionService;
use App\Services\ResponseHandler;
use Exception;

/**
 * Class CsvDataGeneratorService
 *
 * Questa classe si occupa di generare file CSV a partire dai dati presenti nei modelli InvoiceTrack,
 * FlatfileVatInvoiceData e DataCollection, successivamente esegue la crittografia del file generato.
 * Durante il processo, vengono utilizzati log consistenti sul channel "sellouter" per tracciare le operazioni.
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
        ResponseHandler::info('Starting generation of Invoice CSV', [], 'sellouter');

        try {
            $data = InvoiceTrack::all()->toArray();
            $result = $this->streamCSV($data, storage_path('app/temp/InvoiceTrack.csv'));
            ResponseHandler::success('Invoice CSV generated successfully', ['file' => 'InvoiceTrack.csv'], 'sellouter');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Error generating Invoice CSV', ['error' => $e->getMessage()], 'sellouter');
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode()], 500);
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
        ResponseHandler::info('Starting generation of Flatfile VAT CSV', [], 'sellouter');

        try {
            $data = FlatfileVatInvoiceData::all()->toArray();
            $result = $this->streamCSV($data, storage_path('app/temp/Flatfilevatinvoicedata.csv'));
            ResponseHandler::success('Flatfile VAT CSV generated successfully', ['file' => 'Flatfilevatinvoicedata.csv'], 'sellouter');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Error generating Flatfile VAT CSV', ['error' => $e->getMessage()], 'sellouter');
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode()], 500);
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
        ResponseHandler::info('Starting generation of Collection CSV', [], 'sellouter');

        try {
            $data = DataCollection::all()->toArray();
            $result = $this->streamCSV($data, storage_path('app/temp/FlatFileSettlement.csv'));
            ResponseHandler::success('Collection CSV generated successfully', ['file' => 'FlatFileSettlement.csv'], 'sellouter');
            return $result;
        } catch (Exception $e) {
            ResponseHandler::error('Error generating Collection CSV', ['error' => $e->getMessage()], 'sellouter');
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode()], 500);
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
            ResponseHandler::info("Starting CSV stream to file: $filePath", [], 'muvi');

            $handle = fopen($filePath, 'w+');
            if (!$handle) {
                throw new Exception("Unable to open file: $filePath for writing");
            }

            if (!empty($data)) {
                if (fputcsv($handle, array_keys($data[0])) === false) {
                    throw new Exception("Error writing CSV header to file: $filePath");
                }
                ResponseHandler::info("Writing CSV rows to file: $filePath", [], 'muvi');
                foreach ($data as $row) {
                    if (fputcsv($handle, $row) === false) {
                        throw new Exception("Error writing CSV row to file: $filePath");
                    }
                }
            }

            fclose($handle);

            $encryptedFile = $this->fileEncryptionService->saveFile($filePath);
            ResponseHandler::success("CSV file saved and encrypted successfully", ['filePath' => $filePath], 'muvi');
            return $encryptedFile;
        } catch (Exception $e) {
            ResponseHandler::error("Error in streaming CSV to file: $filePath", ['error' => $e->getMessage()], 'muvi');
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode()], 500);
        } finally {
            // Rimuove il file temporaneo se esiste
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
