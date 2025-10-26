<?php

namespace App;

use PDO;

class FarmerService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Adds a new farmer to the database.
     *
     * @param string $names The full name of the farmer.
     * @param string $phoneNumber The farmer's phone number.
     * @param string $language The farmer's preferred language for SMS.
     * @return bool True on success, false on failure.
     */
    public function addFarmer(string $names, string $phoneNumber, string $language = 'en'): bool
    {
        $sql = "INSERT INTO farmers (names, phone_number, preferred_language) VALUES (:names, :phone_number, :language)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':names' => $names,
                ':phone_number' => $phoneNumber,
                ':language' => $language
            ]);
            return true;
        } catch (\PDOException $e) {
            // You might want to log the error message here, e.g., error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves all farmers from the database.
     *
     * @return array An array of all farmers.
     */
    public function getAllFarmers(): array
    {
        $stmt = $this->pdo->query("SELECT id, names, phone_number, preferred_language FROM farmers ORDER BY names ASC");
        return $stmt->fetchAll();
    }

    /**
     * Gets the total count of registered farmers.
     *
     * @return int The total number of farmers.
     */
    public function getFarmerCount(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(id) FROM farmers");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retrieves a paginated list of farmers.
     *
     * @param int $page The current page number.
     * @param int $perPage The number of items per page.
     * @return array An array of farmers for the current page.
     */
    public function getFarmersPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT id, names, phone_number, preferred_language 
                FROM farmers 
                ORDER BY names ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the count of farmers grouped by their preferred language.
     *
     * @return array An array where keys are language codes and values are the counts.
     */
    public function getFarmerCountByLanguage(): array
    {
        $sql = "SELECT preferred_language, COUNT(id) as count FROM farmers GROUP BY preferred_language";
        $stmt = $this->pdo->query($sql);
        // Use PDO::FETCH_KEY_PAIR to get a nice 'lang' => 'count' array
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Retrieves a single farmer by their ID.
     *
     * @param int $id The ID of the farmer.
     * @return array|false The farmer's data as an associative array, or false if not found.
     */
    public function getFarmerById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, names, phone_number, preferred_language FROM farmers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Updates an existing farmer's details in the database.
     *
     * @param int $id The ID of the farmer to update.
     * @param string $names The new name for the farmer.
     * @param string $phoneNumber The new phone number for the farmer.
     * @param string $language The new preferred language for the farmer.
     * @return bool True on success, false on failure.
     */
    public function updateFarmer(int $id, string $names, string $phoneNumber, string $language): bool
    {
        $sql = "UPDATE farmers 
                SET names = :names, phone_number = :phone_number, preferred_language = :language 
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':names' => $names,
                ':phone_number' => $phoneNumber,
                ':language' => $language
            ]);
        } catch (\PDOException $e) {
            // This can happen if the phone number is changed to one that already exists.
            error_log("Error updating farmer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a farmer from the database by their ID.
     *
     * @param int $id The ID of the farmer to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteFarmer(int $id): bool
    {
        $sql = "DELETE FROM farmers WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error deleting farmer: " . $e->getMessage());
            return false;
        }
    }
}