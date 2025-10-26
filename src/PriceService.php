<?php

namespace App;

use PDO;

class PriceService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Adds a new market price entry to the database.
     *
     * @param string $marketName The name of the market (e.g., "Kabale", "Kampala").
     * @param float $price The price of Irish potatoes in that market.
     * @param string $priceDate The date for which the price is recorded (format YYYY-MM-DD).
     * @return bool True on success, false on failure.
     */
    public function addPrice(string $marketName, float $price, string $priceDate): bool
    {
        $sql = "INSERT INTO prices (market_name, price, price_date) VALUES (:market_name, :price, :price_date)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':market_name' => $marketName,
                ':price' => $price,
                ':price_date' => $priceDate
            ]);
            return true;
        } catch (\PDOException $e) {
            // Log the error for debugging purposes
            error_log("Error adding price: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the latest recorded price for each market.
     *
     * @return array An associative array where keys are market names and values are their latest prices.
     */
    public function getLatestPrices(): array
    {
        // Subquery to find the maximum price_date for each market
        $sql = "SELECT p1.market_name, p1.price, p1.price_date
                FROM prices p1
                JOIN (
                    SELECT market_name, MAX(price_date) AS max_price_date
                    FROM prices
                    GROUP BY market_name
                ) p2 ON p1.market_name = p2.market_name AND p1.price_date = p2.max_price_date";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all price entries, ordered by date.
     *
     * @return array An array of all price entries.
     */
    public function getAllPrices(): array
    {
        $sql = "SELECT id, market_name, price, price_date, created_at 
                FROM prices 
                ORDER BY price_date DESC, created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single price entry by its ID.
     *
     * @param int $id The ID of the price entry.
     * @return array|false The price data, or false if not found.
     */
    public function getPriceById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, market_name, price, price_date FROM prices WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Updates an existing price entry.
     *
     * @param int $id The ID of the price to update.
     * @param string $marketName The new market name.
     * @param float $price The new price.
     * @param string $priceDate The new date for the price.
     * @return bool True on success, false on failure.
     */
    public function updatePrice(int $id, string $marketName, float $price, string $priceDate): bool
    {
        $sql = "UPDATE prices 
                SET market_name = :market_name, price = :price, price_date = :price_date 
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':market_name' => $marketName,
                ':price' => $price,
                ':price_date' => $priceDate
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating price: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a price entry from the database.
     *
     * @param int $id The ID of the price to delete.
     * @return bool True on success, false on failure.
     */
    public function deletePrice(int $id): bool
    {
        $sql = "DELETE FROM prices WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error deleting price: " . $e->getMessage());
            return false;
        }
    }
}