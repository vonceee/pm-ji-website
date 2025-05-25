<?php
// src/Services/UserService.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';

use Config\Database;

class UserService
{
    private $pdo;
    private $userModel;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->userModel = new User();
    }

    public function getUserByEmail($email)
    {
        try {
            return $this->userModel->getByEmail($email);
        } catch (Exception $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }

    public function updateProfile($email, $data)
    {
        try {
            // Validate input data
            $validationResult = $this->validateProfileData($data);
            if (!$validationResult['valid']) {
                return [
                    'status' => 'error',
                    'message' => $validationResult['message']
                ];
            }

            // Check if user exists
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Handle single field update (AJAX)
            if (isset($data['field']) && isset($data['value'])) {
                return $this->updateSingleField($user['id'], $data['field'], $data['value']);
            }

            // Handle full profile update
            return $this->updateFullProfile($user['id'], $data);

        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred while updating profile'
            ];
        }
    }

    private function validateProfileData($data)
    {
        $required = ['first_name', 'last_name', 'email', 'contact_no'];
        
        // For single field updates
        if (isset($data['field']) && isset($data['value'])) {
            $field = $data['field'];
            $value = trim($data['value']);
            
            if (empty($value)) {
                return ['valid' => false, 'message' => ucfirst($field) . ' is required'];
            }
            
            if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Invalid email format'];
            }
            
            if ($field === 'contact_no' && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
                return ['valid' => false, 'message' => 'Invalid contact number format'];
            }
            
            return ['valid' => true];
        }

        // For full profile updates
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return ['valid' => false, 'message' => ucfirst($field) . ' is required'];
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        return ['valid' => true];
    }

    private function updateSingleField($userId, $field, $value)
    {
        $allowedFields = ['first_name', 'middle_name', 'last_name', 'email', 'contact_no'];
        
        if (!in_array($field, $allowedFields)) {
            return [
                'status' => 'error',
                'message' => 'Invalid field'
            ];
        }

        $result = $this->userModel->updateField($userId, $field, trim($value));
        
        if ($result) {
            return [
                'status' => 'success',
                'message' => ucfirst($field) . ' updated successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to update ' . $field
            ];
        }
    }

    private function updateFullProfile($userId, $data)
    {
        $updateData = [
            'first_name' => trim($data['first_name']),
            'middle_name' => trim($data['middle_name'] ?? ''),
            'last_name' => trim($data['last_name']),
            'email' => trim($data['email']),
            'contact_no' => trim($data['contact_no'])
        ];

        $result = $this->userModel->update($userId, $updateData);
        
        if ($result) {
            // Update session email if it changed
            if ($_SESSION['user_email'] !== $updateData['email']) {
                $_SESSION['user_email'] = $updateData['email'];
            }
            
            return [
                'status' => 'success',
                'message' => 'Profile updated successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to update profile'
            ];
        }
    }

    public function getUserById($userId)
    {
        try {
            return $this->userModel->getById($userId);
        } catch (Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }

    public function changePassword($email, $currentPassword, $newPassword)
    {
        // Implementation for password change
        // This would typically involve password hashing and verification
        // Left as placeholder for future implementation
    }
}