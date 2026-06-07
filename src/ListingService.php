<?php
namespace App;

use PDO;

class ListingService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createListing($farmerId, $variety, $quantity, $unit, $price, $location, $imagePath = null) {
        $stmt = $this->db->prepare("INSERT INTO listings (farmer_id, variety, quantity_available, unit, price_per_unit, location, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$farmerId, $variety, $quantity, $unit, $price, $location, $imagePath]);
    }

    public function getActiveListings($variety = null, $location = null) {
        $sql = "SELECT l.*, f.names as farmer_name, f.phone_number as farmer_phone 
                FROM listings l 
                JOIN farmers f ON l.farmer_id = f.id 
                WHERE l.status = 'available'";
        $params = [];

        if ($variety) {
            $sql .= " AND l.variety LIKE ?";
            $params[] = "%$variety%";
        }
        if ($location) {
            $sql .= " AND l.location LIKE ?";
            $params[] = "%$location%";
        }

        $sql .= " ORDER BY l.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFarmerListings($farmerId) {
        $stmt = $this->db->prepare("SELECT * FROM listings WHERE farmer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE listings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}