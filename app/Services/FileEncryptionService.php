<?php

namespace App\Services;

use phpseclib3\Net\SFTP;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use gnupg;

class FileEncryptionService
{
    protected $sftpHost;
    protected $sftpUsername;
    protected $sftpPassword;
    protected $publicKeyPath;
    protected $gnupg;
    protected $publicKeyFingerprint;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->sftpHost = env('SFTP_HOST');
        $this->sftpUsername = env('SFTP_USERNAME');
        $this->sftpPassword = env('SFTP_PASSWORD');
        $this->publicKeyPath = storage_path('app/keys/public.asc');

        putenv("GNUPGHOME=" . storage_path('app/keys'));
        if (!class_exists('gnupg')) {
            Log::error('L\'estensione PHP gnupg non è installata.');
            throw new \Exception('L\'estensione PHP gnupg non è installata.');
        }
        $this->gnupg = new gnupg();
        
        $this->importPublicKey();
    }
    
    /**
     * importPublicKey
     *
     * @return void
     */
    private function importPublicKey()
    {
        $publicKey = file_get_contents($this->publicKeyPath);
        $importResult = $this->gnupg->import($publicKey);

        if ($importResult === false) {
            Log::error('Importazione della chiave pubblica fallita: ' . $this->gnupg->geterror());
        }

        $this->publicKeyFingerprint = $importResult['fingerprint'];
    }
    
    /**
     * encrypt
     *
     * @param  mixed $data
     * @return void
     */
    public function encrypt($data)
    {
        try {
            $this->gnupg->addencryptkey($this->publicKeyFingerprint);
            
            $encrypted = $this->gnupg->encrypt($data);
            
            if ($encrypted === false) {
                throw new \Exception('La crittografia è fallita: ' . $this->gnupg->geterror());
            }

            return $encrypted;
        } catch (\Exception $e) {
            Log::error('Errore durante la crittografia del file.', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    
    /**
     * upload
     *
     * @param  mixed $encryptedData
     * @param  mixed $remoteFilePath
     * @return void
     */
    public function upload($encryptedData, $remoteFilePath)
    {
        try {
            Log::info('Tentativo di connessione al server SFTP.', [
                'host' => $this->sftpHost,
                'username' => $this->sftpUsername,
            ]);

            $sftp = new SFTP($this->sftpHost);

            if (!$sftp->login($this->sftpUsername, $this->sftpPassword)) {
                Log::error('Login al server SFTP fallito.');
                return false;
            }

            if(env("CRYPT_DATA")){
                $remoteFilePath .= '.enc';
            }
            
            Log::info('Connessione al server SFTP riuscita, tentativo di caricamento del file: ' . $remoteFilePath);
            if (!$sftp->put("/upload/" . $remoteFilePath, $encryptedData)) {
                Log::error('Errore durante il caricamento del file criptato.');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Errore durante il caricamento del file criptato.', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    
    /**
     * saveFile
     *
     * @param  mixed $filePath
     * @return JsonResponse
     */
    public function saveFile($filePath): JsonResponse
    {
        try {
            ini_set('max_execution_time', 300);
            
            $fileContent = file_get_contents($filePath);
            $remoteFilePath = basename($filePath);

            if ($fileContent === false) {
                Log::error("Impossibile leggere il file: " . $filePath);
                return response()->json(['message' => 'Errore durante la lettura del file.'], 500);
            }

            
            if(env("CRYPT_DATA")){
                $encryptedData = $this->encrypt($fileContent);
                if (!$encryptedData) {
                    return response()->json(['message' => 'Errore durante la crittografia del file.'], 500);
                }
    
                
                $result = $this->upload($encryptedData, $remoteFilePath);
            }
            else{
                $result = $this->upload($fileContent, $remoteFilePath);
            }
            

            if (!$result) {
                Log::error('Errore durante il caricamento del file criptato.');
                return response()->json(['message' => 'Errore durante il caricamento del file criptato.'], 500);
            }

            return response()->json(['message' => 'File criptato e caricato con successo.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}