<?php
namespace App;

use PDO;

class FarmerAuthService {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Authenticates a farmer using their phone number and password.
     */
    public function login(string $phoneNumber, string $password): bool {
        $stmt = $this->pdo->prepare("SELECT * FROM farmers WHERE phone_number = :phone");
        $stmt->execute([':phone' => $phoneNumber]);
        $farmer = $stmt->fetch();

        if ($farmer && $farmer['password_hash'] && password_verify($password, $farmer['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $_SESSION['farmer_id'] = $farmer['id'];
            $_SESSION['farmer_names'] = $farmer['names'];
            $_SESSION['role'] = 'farmer';
            return true;
        }
        return false;
    }

    /**
     * Checks if a farmer is currently authenticated.
     */
    public function isFarmerLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['farmer_id']) && $_SESSION['role'] === 'farmer';
    }

    /**
     * Logout the farmer.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['farmer_id']);
        unset($_SESSION['farmer_names']);
        unset($_SESSION['role']);
        session_destroy();
    }
}