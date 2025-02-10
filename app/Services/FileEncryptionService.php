<?php

namespace App\Services;

use gnupg;
use phpseclib3\Net\SFTP;
use App\Services\ResponseHandler;
use Illuminate\Http\JsonResponse;
use phpseclib3\Crypt\PublicKeyLoader;
use Exception;

/**
 * Class FileEncryptionService
 *
 * Gestisce la crittografia dei file e il loro caricamento su un server SFTP.
 * Tutti i log vengono scritti utilizzando il channel "muvi".
 *
 * @package App\Services
 */
class FileEncryptionService
{
    protected $sftpHost;
    protected $sftpUsername;
    protected $sftpPassword;
    protected $publicKeyPath;
    protected $gnupg;
    protected $publicKeyFingerprint;

    /**
     * FileEncryptionService constructor.
     *
     * Inizializza le variabili d'ambiente, il percorso della chiave pubblica e importa la chiave.
     *
     * @throws Exception Se l'estensione PHP gnupg non è installata.
     */
    public function __construct()
    {
        try {
            $this->sftpHost = env('SFTP_HOST');
            $this->sftpUsername = env('SFTP_USERNAME');
            $this->sftpPassword = env('SFTP_PASSWORD');
            $this->publicKeyPath = storage_path('app/keys/sap@nexi.it.key');

            putenv("GNUPGHOME=" . storage_path('app/keys'));

            if (!class_exists('gnupg')) {
                throw new Exception('L\'estensione PHP gnupg non è installata.');
            }

            $this->gnupg = new gnupg();
            $this->importPublicKey();
        } catch (Exception $e) {
            ResponseHandler::error('Exception in FileEncryptionService constructor', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ], 'muvi');
            throw $e;
        }
    }

    /**
     * importPublicKey
     *
     * Importa la chiave pubblica necessaria per la crittografia.
     *
     * @return void
     */
    private function importPublicKey()
    {
        try {
            ResponseHandler::info('Importing public key', ['path' => $this->publicKeyPath], 'muvi');

            $publicKey = file_get_contents($this->publicKeyPath);
            if ($publicKey === false) {
                throw new Exception("Unable to read public key file at {$this->publicKeyPath}");
            }

            $importResult = $this->gnupg->import($publicKey);
            if ($importResult === false) {
                ResponseHandler::error('Importazione della chiave pubblica fallita.', ['error' => $this->gnupg->geterror()], 'muvi');
                throw new Exception('Importazione della chiave pubblica fallita.');
            }

            $this->publicKeyFingerprint = $importResult['fingerprint'];
            ResponseHandler::success('Public key imported successfully', [], 'muvi');
        } catch (Exception $e) {
            ResponseHandler::error('Exception during public key import', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 'muvi');
            throw $e;
        }
    }

    /**
     * encrypt
     *
     * Esegue la crittografia dei dati utilizzando la chiave pubblica importata.
     *
     * @param mixed $data Dati da crittografare.
     * @return mixed I dati crittografati oppure false in caso di errore.
     */
    public function encrypt($data)
    {
        try {
            ResponseHandler::info('Starting file encryption', [], 'muvi');
            $this->gnupg->addencryptkey($this->publicKeyFingerprint);
            $encrypted = $this->gnupg->encrypt($data);

            if ($encrypted === false) {
                throw new Exception('Encryption failed: ' . $this->gnupg->geterror());
            }

            ResponseHandler::success('File encrypted successfully', [], 'muvi');
            return $encrypted;
        } catch (Exception $e) {
            ResponseHandler::error('Error during encryption', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 'muvi');
            return false;
        }
    }

    /**
     * upload
     *
     * Carica i dati crittografati su un server SFTP utilizzando l'autenticazione tramite chiave privata.
     *
     * @param mixed  $encryptedData Dati crittografati da caricare.
     * @param string $remoteFilePath Nome del file remoto.
     * @return bool True se il caricamento ha avuto successo, false altrimenti.
     */
    private function upload($encryptedData, $remoteFilePath)
    {
        try {
            ResponseHandler::info('Connecting to SFTP server using SSH key', [
                'host' => $this->sftpHost,
                'username' => $this->sftpUsername,
            ], 'muvi');

            $sftp = new SFTP($this->sftpHost);

            // Percorso alla chiave privata SSH
            $privateKeyPath = storage_path('app/keys/ftps');
            $privateKey = file_get_contents($privateKeyPath);

            if ($privateKey === false) {
                throw new Exception("Unable to read private key from: $privateKeyPath");
            }

            // Autenticazione con chiave privata
            $key = PublicKeyLoader::load($privateKey);
            ResponseHandler::info('Logging in to SFTP server using SSH key', ['private_key_loaded' => true], 'muvi');

            if (!$sftp->login($this->sftpUsername, $key)) {
                ResponseHandler::error('Login to SFTP server failed using SSH key.', [], 'muvi');
                return false;
            }

            if (env("CRYPT_DATA")) {
                $remoteFilePath .= '.gpg';
            }

            ResponseHandler::info('Uploading file to SFTP via SSH', ['remote_path' => $remoteFilePath], 'muvi');

            if (!$sftp->put("/upload/" . $remoteFilePath, $encryptedData)) {
                ResponseHandler::error('File upload failed', ['remote_path' => $remoteFilePath], 'muvi');
                return false;
            }

            ResponseHandler::success('File uploaded successfully using SSH key', ['remote_path' => $remoteFilePath], 'muvi');
            return true;
        } catch (Exception $e) {
            ResponseHandler::error('Error during file upload via SSH', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 'muvi');
            return false;
        }
    }

    /**
     * saveFile
     *
     * Legge il contenuto di un file, lo crittografa (se abilitato) e lo carica su un server SFTP.
     *
     * @param string $filePath Percorso completo del file da elaborare.
     * @return JsonResponse Risposta JSON con l'esito dell'operazione.
     */
    public function saveFile($filePath): JsonResponse
    {
        ResponseHandler::info('Starting file save process', ['file_path' => $filePath], 'muvi');

        try {
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                ResponseHandler::error("Cannot read file", ['file_path' => $filePath], 'muvi');
                return response()->json(['message' => 'Error reading file.'], 500);
            }

            $remoteFilePath = basename($filePath);

            if (env("CRYPT_DATA")) {
                ResponseHandler::info('Encrypting file before upload', ['file_path' => $filePath], 'muvi');
                $encryptedData = $this->encrypt($fileContent);

                if ($encryptedData === false) {
                    return response()->json(['message' => 'Error during encryption.'], 500);
                }
            } else {
                $encryptedData = $fileContent;
            }

            if (!$this->upload($encryptedData, $remoteFilePath)) {
                throw new Exception('File upload failed.');
            }

            ResponseHandler::success('File saved and uploaded successfully', ['file_path' => $filePath], 'muvi');
            return response()->json(['message' => 'File and semaphore uploaded successfully.']);
        } catch (Exception $e) {
            ResponseHandler::error('Exception in saveFile', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'muvi');

            return response()->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode()
            ], 500);
        }
    }
}
