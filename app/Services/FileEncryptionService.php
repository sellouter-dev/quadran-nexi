<?php

namespace App\Services;

use gnupg;
use phpseclib3\Net\SFTP;
use App\Services\ResponseHandler;
use Illuminate\Http\JsonResponse;
use phpseclib3\Crypt\PublicKeyLoader;

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
     */
    public function __construct()
    {
        $this->sftpHost = env('SFTP_HOST');
        $this->sftpUsername = env('SFTP_USERNAME');
        $this->sftpPassword = env('SFTP_PASSWORD');
        $this->publicKeyPath = storage_path('app/keys/sap@nexi.it.key');

        putenv("GNUPGHOME=" . storage_path('app/keys'));

        if (!class_exists('gnupg')) {
            ResponseHandler::error('L\'estensione PHP gnupg non è installata.', [], 'error_log');
            throw new \Exception('L\'estensione PHP gnupg non è installata.');
        }

        $this->gnupg = new gnupg();
        $this->importPublicKey();
    }

    /**
     * importPublicKey
     */
    private function importPublicKey()
    {
        ResponseHandler::info('Importing public key', ['path' => $this->publicKeyPath], 'info_log');

        $publicKey = file_get_contents($this->publicKeyPath);
        $importResult = $this->gnupg->import($publicKey);

        if ($importResult === false) {
            ResponseHandler::error('Importazione della chiave pubblica fallita.', ['error' => $this->gnupg->geterror()], 'error_log');
        }

        $this->publicKeyFingerprint = $importResult['fingerprint'];
        ResponseHandler::success('Public key imported successfully', [], 'success_log');
    }

    /**
     * encrypt
     */
    public function encrypt($data)
    {
        try {
            ResponseHandler::info('Starting file encryption', [], 'info_log');
            $this->gnupg->addencryptkey($this->publicKeyFingerprint);
            $encrypted = $this->gnupg->encrypt($data);

            if ($encrypted === false) {
                throw new \Exception('Encryption failed: ' . $this->gnupg->geterror());
            }

            ResponseHandler::success('File encrypted successfully', [], 'success_log');
            return $encrypted;
        } catch (\Exception $e) {
            ResponseHandler::error('Error during encryption', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'error_log');

            return false;
        }
    }


    private function upload($encryptedData, $remoteFilePath)
    {
        try {
            ResponseHandler::info('Connecting to SFTP server using SSH key', [
                'host' => $this->sftpHost,
                'username' => $this->sftpUsername,
            ], 'info_log');

            $sftp = new SFTP($this->sftpHost);

            // Percorso alla chiave privata SSH
            $privateKeyPath = storage_path('app/keys/ftps');
            $privateKey = file_get_contents($privateKeyPath);

            // Autenticazione con chiave privata
            $key = PublicKeyLoader::load($privateKey);
            ResponseHandler::info('Logging in to SFTP server using SSH key', ["key" => $key], 'info_log');
            if (!$sftp->login($this->sftpUsername, $key)) {
                ResponseHandler::error('Login to SFTP server failed using SSH key.', [], 'error_log');
                return false;
            }

            if (env("CRYPT_DATA")) {
                $remoteFilePath .= '.gpg';
            }

            ResponseHandler::info('Uploading file to SFTP via SSH', ['remote_path' => $remoteFilePath], 'info_log');

            if (!$sftp->put("/upload/" . $remoteFilePath, $encryptedData)) {
                ResponseHandler::error('File upload failed', ['remote_path' => $remoteFilePath], 'error_log');
                return false;
            }

            ResponseHandler::success('File uploaded successfully using SSH key', ['remote_path' => $remoteFilePath], 'success_log');
            return true;
        } catch (\Exception $e) {
            ResponseHandler::error('Error during file upload via SSH', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'error_log');

            return false;
        }
    }

    /**
     * saveFile
     */
    public function saveFile($filePath): JsonResponse
    {
        ResponseHandler::info('Starting file save process', ['file_path' => $filePath], 'info_log');

        try {

            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                ResponseHandler::error("Cannot read file", ['file_path' => $filePath], 'error_log');
                return response()->json(['message' => 'Error reading file.'], 500);
            }

            $remoteFilePath = basename($filePath);

            if (env("CRYPT_DATA")) {
                ResponseHandler::info('Encrypting file before upload', ['file_path' => $filePath], 'info_log');
                $encryptedData = $this->encrypt($fileContent);

                if (!$encryptedData) {
                    return response()->json(['message' => 'Error during encryption.'], 500);
                }
            } else {
                $encryptedData = $fileContent;
            }

            if (!$this->upload($encryptedData, $remoteFilePath)) {
                throw new \Exception('File upload failed.');
            }

            ResponseHandler::success('File saved and uploaded successfully', ['file_path' => $filePath], 'success_log');

            return response()->json(['message' => 'File and semaphore uploaded successfully.']);
        } catch (\Exception $e) {
            ResponseHandler::error('Exception in saveFile', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'error_log');

            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }
}
