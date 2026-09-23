<?php
/**
 * Favorites Helper Functions
 */

/**
 * Add a phone to user's favorites
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @param int $phone_id
 * @return bool True on success
 */
function add_favorite($pdo, $user_id, $phone_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, phone_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $phone_id]);
    } catch (PDOException $e) {
        // Ignore duplicate entry errors
        if ($e->getCode() == 23000) {
            return true;
        }
        return false;
    }
}

/**
 * Remove a phone from user's favorites
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @param int $phone_id
 * @return bool
 */
function remove_favorite($pdo, $user_id, $phone_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND phone_id = ?");
        return $stmt->execute([$user_id, $phone_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Check if a phone is favorited by the user
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @param int $phone_id
 * @return bool
 */
function is_favorite($pdo, $user_id, $phone_id) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND phone_id = ?");
        $stmt->execute([$user_id, $phone_id]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get array of phone IDs favorited by the user (useful for N+1 prevention)
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function get_user_favorites($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT phone_id FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all full phone records favorited by the user
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function get_favorite_phones($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, f.created_at as favorited_at, f.id as favorite_id 
            FROM favorites f 
            JOIN phones p ON f.phone_id = p.id 
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Remove all favorites for a user
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return bool
 */
function remove_all_favorites($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        return false;
    }
}
