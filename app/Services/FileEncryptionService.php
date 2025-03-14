<?php

namespace App\Services;

use gnupg;
use phpseclib3\Net\SFTP;
use App\Services\ResponseHandler;
use Illuminate\Http\JsonResponse;
use phpseclib3\Crypt\PublicKeyLoader;
use Exception;

class FileEncryptionService
{
    protected $sftpHost;
    protected $sftpUsername;
    protected $publicKeyPath;
    protected $gnupg;
    protected $publicKeyFingerprint;

    /**
     * Flag che indica se la chiave pubblica è disponibile
     * (cioè è stata letta e importata correttamente).
     *
     * @var bool
     */
    protected $keyAvailable = false;

    public function __construct()
    {
        try {
            $this->sftpHost = env('SFTP_HOST');
            $this->sftpUsername = env('SFTP_USERNAME');
            $this->publicKeyPath = __DIR__ . '/../../storage/app/keys/sap@nexi.it.key';

            putenv("GNUPGHOME=" . storage_path('/app/keys'));

            if (!class_exists('gnupg')) {
                ResponseHandler::error(
                    'Estensione PHP gnupg non installata. Crittografia disabilitata.',
                    [],
                    'muvi-error'
                );
                return;
            }

            $this->gnupg = new gnupg();
            $this->importPublicKey();
        } catch (Exception $e) {
            ResponseHandler::error(
                'Eccezione nel costruttore di FileEncryptionService',
                [
                    'errore' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'linea'  => $e->getLine()
                ],
                'muvi-error'
            );
        }
    }

    /**
     * Importa la chiave pubblica necessaria per la crittografia.
     *
     * @return void
     */
    private function importPublicKey()
    {
        try {
            ResponseHandler::info('Importazione della chiave pubblica', ['percorso' => $this->publicKeyPath], 'muvi-info');

            if (!file_exists($this->publicKeyPath)) {
                ResponseHandler::error(
                    "File della chiave pubblica non trovato in {$this->publicKeyPath}. La crittografia verrà saltata.",
                    [],
                    'muvi-error'
                );
                $this->publicKeyFingerprint = null;
                return;
            }


            $publicKey = file_get_contents($this->publicKeyPath);
            if ($publicKey === false) {
                ResponseHandler::error(
                    "Impossibile leggere il file della chiave pubblica in {$this->publicKeyPath}. La crittografia verrà saltata.",
                    [],
                    'muvi-error'
                );
                $this->publicKeyFingerprint = null;
                return;
            }


            $importResult = $this->gnupg->import($publicKey);
            if ($importResult === false) {
                ResponseHandler::error(
                    'Importazione della chiave pubblica fallita.',
                    ['errore' => $this->gnupg->geterror()],
                    'muvi-error'
                );
                $this->publicKeyFingerprint = null;
                return;
            }

            $this->publicKeyFingerprint = $importResult['fingerprint'];
            ResponseHandler::success('Chiave pubblica importata con successo', [], 'muvi-success');

            ResponseHandler::info('Processo di importazione della chiave pubblica completato', ['fingerprint' => $this->publicKeyFingerprint], 'muvi-info');
            $this->keyAvailable = true;
        } catch (Exception $e) {
            ResponseHandler::error(
                'Eccezione durante l\'importazione della chiave pubblica',
                [
                    'errore' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'linea'  => $e->getLine(),
                ],
                'muvi-error'
            );
            throw $e;
        }
    }

    /**
     * Esegue la crittografia dei dati utilizzando la chiave pubblica importata.
     *
     * @param mixed $data Dati da crittografare.
     * @return mixed I dati crittografati oppure false in caso di errore.
     */
    public function encrypt($data)
    {
        if (!$this->keyAvailable) {
            ResponseHandler::warning(
                'Crittografia saltata perché la chiave pubblica non è disponibile.',
                [],
                'muvi-warning'
            );
            return $data;
        }

        try {
            ResponseHandler::info('Inizio della crittografia del file', [], 'muvi-info');
            $this->gnupg->addencryptkey($this->publicKeyFingerprint);
            $encrypted = $this->gnupg->encrypt($data);

            if ($encrypted === false) {
                throw new Exception('Crittografia fallita: ' . $this->gnupg->geterror());
            }

            ResponseHandler::success('File crittografato con successo', [], 'muvi-success');
            return $encrypted;
        } catch (Exception $e) {
            ResponseHandler::error(
                'Errore durante la crittografia',
                [
                    'messaggio' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'linea'    => $e->getLine()
                ],
                'muvi-error'
            );
            return false;
        }
    }

    /**
     * Carica i dati crittografati su un server SFTP utilizzando l'autenticazione tramite chiave privata.
     *
     * @param mixed  $encryptedData Dati (eventualmente crittografati) da caricare.
     * @param string $remoteFilePath Nome del file remoto.
     * @return bool True se il caricamento ha avuto successo, false altrimenti.
     */
    private function upload($encryptedData, $remoteFilePath)
    {
        try {
            ResponseHandler::info('Connessione al server SFTP utilizzando la chiave SSH', [
                'host' => $this->sftpHost,
                'username' => $this->sftpUsername,
            ], 'muvi-info');

            $sftp = new SFTP($this->sftpHost);


            $privateKeyPath = storage_path('/app/keys/ftps');
            $privateKey = file_get_contents($privateKeyPath);

            if ($privateKey === false) {
                throw new Exception("Impossibile leggere la chiave privata da: $privateKeyPath");
            }

            $key = PublicKeyLoader::load($privateKey);
            ResponseHandler::info('Accesso al server SFTP con chiave SSH', [
                'chiave_privata_caricata' => true
            ], 'muvi-info');

            if (!$sftp->login($this->sftpUsername, $key)) {
                ResponseHandler::error('Accesso al server SFTP fallito utilizzando la chiave SSH.', [], 'muvi-error');
                return false;
            }


            if (env("CRYPT_DATA")) {
                $remoteFilePath .= '.gpg';
            }

            ResponseHandler::info('Caricamento del file su SFTP tramite SSH', ['percorso_remoto' => $remoteFilePath], 'muvi-info');

            if (!$sftp->put("/upload/" . $remoteFilePath, $encryptedData)) {
                ResponseHandler::error('Caricamento del file fallito', ['percorso_remoto' => $remoteFilePath], 'muvi-error');
                return false;
            }

            ResponseHandler::success(
                'File caricato con successo utilizzando la chiave SSH',
                ['percorso_remoto' => $remoteFilePath],
                'muvi-success'
            );

            return true;
        } catch (Exception $e) {
            ResponseHandler::error(
                'Errore durante il caricamento del file tramite SSH',
                [
                    'messaggio' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'linea'    => $e->getLine()
                ],
                'muvi-error'
            );
            return false;
        }
    }

    /**
     * Legge il contenuto di un file, lo crittografa (se abilitato e se la chiave è disponibile)
     * e lo carica su un server SFTP.
     *
     * @param string $filePath Percorso completo del file da elaborare.
     * @return JsonResponse Risposta JSON con l'esito dell'operazione.
     */
    public function saveFile($filePath): JsonResponse
    {
        ResponseHandler::info('Inizio del processo di salvataggio del file', ['percorso_file' => $filePath], 'muvi-info');

        try {
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                ResponseHandler::error("Impossibile leggere il file", ['percorso_file' => $filePath], 'muvi-error');
                return response()->json(['messaggio' => 'Errore nella lettura del file.'], 500);
            }

            $remoteFilePath = basename($filePath);

            if (env("CRYPT_DATA")) {
                ResponseHandler::info('Crittografia del file prima del caricamento', ['percorso_file' => $filePath], 'muvi-info');
                $encryptedData = $this->encrypt($fileContent);

                if ($encryptedData === false) {
                    ResponseHandler::error(
                        "Crittografia fallita (o saltata) per il file: $filePath",
                        [],
                        'muvi-error'
                    );
                    return response()->json(['messaggio' => 'Errore durante la crittografia.'], 500);
                }
            } else {
                $encryptedData = $fileContent;
            }

            if (!$this->upload($encryptedData, $remoteFilePath)) {
                throw new Exception('Caricamento del file fallito.');
            }

            ResponseHandler::success(
                'File salvato e caricato con successo',
                ['percorso_file' => $filePath],
                'muvi-success'
            );

            return response()->json([
                'messaggio' => 'File e semaphore caricati con successo.'
            ]);
        } catch (Exception $e) {
            ResponseHandler::error(
                'Eccezione in saveFile',
                [
                    'errore' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'linea'  => $e->getLine(),
                    'traccia' => $e->getTraceAsString(),
                ],
                'muvi-error'
            );

            return response()->json([
                'errore' => $e->getMessage(),
                'codice'  => $e->getCode()
            ], 500);
        }
    }
}
