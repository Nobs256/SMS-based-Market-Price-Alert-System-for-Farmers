<?php

namespace App;

use PDO;

class LogService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Logs the result of an SMS broadcast to the database.
     *
     * @param string $message The content of the SMS message or an error message.
     * @param string $status The status of the broadcast ('success' or 'failure').
     * @return bool True on success, false on failure.
     */
    public function logBroadcast(string $message, string $status): bool
    {
        $sql = "INSERT INTO sms_logs (message, status) VALUES (:message, :status)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':message' => $message, ':status' => $status]);
        } catch (\PDOException $e) {
            // Log this critical error to the system's main error log, as we can't write to the DB.
            error_log("CRITICAL: Failed to write to sms_logs table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves recent broadcast logs from the database.
     *
     * @param int $limit The maximum number of logs to retrieve.
     * @return array An array of log entries.
     */
    public function getLogs(int $limit = 15): array
    {
        $sql = "SELECT message, status, created_at 
                FROM sms_logs 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}