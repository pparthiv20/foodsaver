<?php
/**
 * OAuth Handler for Google and Facebook Authentication
 * Handles OAuth token exchange and user account creation/login
 */

require_once 'config.php';

class OAuthHandler {
    
    /**
     * Generate OAuth authorization URL
     * @param string $provider 'google' or 'facebook'
     * @param string $userType 'user', 'restaurant', or 'ngo'
     * @return string Authorization URL
     */
    public static function getAuthURL($provider, $userType = 'user') {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_user_type'] = $userType;
        
        if ($provider === 'google') {
            return self::getGoogleAuthURL($state);
        } elseif ($provider === 'facebook') {
            return self::getFacebookAuthURL($state);
        }
        
        return null;
    }
    
    /**
     * Get Google OAuth authorization URL
     * @param string $state CSRF protection state
     * @return string Authorization URL
     */
    private static function getGoogleAuthURL($state) {
        $params = [
            'client_id' => GOOGLE_CLIENT_ID,
            'redirect_uri' => APP_URL . '/pages/oauth_callback.php?provider=google',
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Get Facebook OAuth authorization URL
     * @param string $state CSRF protection state
     * @return string Authorization URL
     */
    private static function getFacebookAuthURL($state) {
        $params = [
            'client_id' => FACEBOOK_APP_ID,
            'redirect_uri' => APP_URL . '/pages/oauth_callback.php?provider=facebook',
            'scope' => 'email,public_profile',
            'state' => $state,
            'response_type' => 'code'
        ];
        
        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     * @param string $code Authorization code
     * @param string $provider 'google' or 'facebook'
     * @return array Token response or error
     */
    public static function getAccessToken($code, $provider) {
        if ($provider === 'google') {
            return self::getGoogleAccessToken($code);
        } elseif ($provider === 'facebook') {
            return self::getFacebookAccessToken($code);
        }
        
        return ['error' => 'Invalid provider'];
    }
    
    /**
     * Get Google access token
     * @param string $code Authorization code
     * @return array Token response
     */
    private static function getGoogleAccessToken($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => APP_URL . '/pages/oauth_callback.php?provider=google'
        ];
        
        $response = self::makeRequest($url, $data);
        return $response;
    }
    
    /**
     * Get Facebook access token
     * @param string $code Authorization code
     * @return array Token response
     */
    private static function getFacebookAccessToken($code) {
        $url = 'https://graph.facebook.com/v18.0/oauth/access_token';
        $data = [
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'code' => $code,
            'redirect_uri' => APP_URL . '/pages/oauth_callback.php?provider=facebook'
        ];
        
        $response = self::makeRequest($url, $data);
        return $response;
    }
    
    /**
     * Get user profile from OAuth provider
     * @param string $accessToken Access token from provider
     * @param string $provider 'google' or 'facebook'
     * @return array User profile data
     */
    public static function getUserProfile($accessToken, $provider) {
        if ($provider === 'google') {
            return self::getGoogleUserProfile($accessToken);
        } elseif ($provider === 'facebook') {
            return self::getFacebookUserProfile($accessToken);
        }
        
        return null;
    }
    
    /**
     * Get Google user profile
     * @param string $accessToken Access token
     * @return array User profile
     */
    private static function getGoogleUserProfile($accessToken) {
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $headers = ['Authorization' => 'Bearer ' . $accessToken];
        
        $response = self::makeRequest($url, [], $headers, 'GET');
        return $response;
    }
    
    /**
     * Get Facebook user profile
     * @param string $accessToken Access token
     * @return array User profile
     */
    private static function getFacebookUserProfile($accessToken) {
        $url = 'https://graph.facebook.com/me?fields=id,email,name,picture&access_token=' . urlencode($accessToken);
        
        $response = self::makeRequest($url, [], [], 'GET');
        return $response;
    }
    
    /**
     * Make HTTP request to OAuth provider
     * @param string $url Request URL
     * @param array $data POST data
     * @param array $headers Custom headers
     * @param string $method HTTP method
     * @return array Response
     */
    private static function makeRequest($url, $data = [], $headers = [], $method = 'POST') {
        $options = [
            'http' => [
                'method' => $method,
                'timeout' => 30
            ]
        ];
        
        if ($method === 'POST' && !empty($data)) {
            $options['http']['header'] = 'Content-Type: application/x-www-form-urlencoded';
            $options['http']['content'] = http_build_query($data);
        }
        
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                if (!isset($options['http']['header'])) {
                    $options['http']['header'] = '';
                } else {
                    $options['http']['header'] .= "\r\n";
                }
                $options['http']['header'] .= "{$key}: {$value}";
            }
        }
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return ['error' => 'Failed to connect to OAuth provider'];
        }
        
        return json_decode($response, true) ?? ['error' => 'Invalid response'];
    }
    
    /**
     * Find or create user account from OAuth profile
     * @param array $profile User profile from OAuth provider
     * @param string $provider 'google' or 'facebook'
     * @param string $userType User type ('user', 'restaurant', 'ngo')
     * @return array User data and auto-login status
     */
    public static function findOrCreateUser($profile, $provider, $userType) {
        if (empty($profile['email'])) {
            return ['error' => 'Email not available from provider'];
        }
        
        $db = getDB();
        $email = sanitizeInput($profile['email']);
        $name = $provider === 'google' 
            ? ($profile['name'] ?? 'User')
            : ($profile['name'] ?? 'User');
        
        // Check all user tables for existing email
        $tables = ['users', 'restaurants', 'ngos', 'admins'];
        $existingUser = null;
        $existingTable = null;
        
        foreach ($tables as $table) {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $existingUser = $user;
                $existingTable = $table;
                break;
            }
        }
        
        // If user exists and matches the requested type, log them in
        if ($existingUser && $existingTable === ($userType . 's') || ($userType === 'user' && $existingTable === 'users')) {
            // Update OAuth provider info
            $stmt = $db->prepare("UPDATE {$existingTable} SET {$provider}_id = ?, {$provider}_token = ?, last_login = NOW() WHERE id = ?");
            $stmt->execute([$profile['sub'] ?? $profile['id'], json_encode($profile), $existingUser['id']]);
            
            return [
                'success' => true,
                'user_id' => $existingUser['id'],
                'user_type' => $userType,
                'user_name' => $existingUser['full_name'] ?? $existingUser['restaurant_name'] ?? $existingUser['ngo_name'] ?? $name,
                'user_email' => $email,
                'is_new' => false
            ];
        }
        
        // If user exists but with different type, show error
        if ($existingUser) {
            return ['error' => 'Email already registered. Please log in or use a different email address.'];
        }
        
        // Create new user account
        try {
            $username = self::generateUsername($email, $userType);
            $tempPassword = bin2hex(random_bytes(16));
            
            if ($userType === 'restaurant') {
                $stmt = $db->prepare(
                    "INSERT INTO restaurants (username, email, password, restaurant_name, owner_name, {$provider}_id, {$provider}_token, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())"
                );
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($tempPassword, PASSWORD_DEFAULT),
                    $name,
                    $name,
                    $profile['sub'] ?? $profile['id'],
                    json_encode($profile)
                ]);
            } elseif ($userType === 'ngo') {
                $stmt = $db->prepare(
                    "INSERT INTO ngos (username, email, password, ngo_name, contact_person, {$provider}_id, {$provider}_token, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())"
                );
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($tempPassword, PASSWORD_DEFAULT),
                    $name,
                    $name,
                    $profile['sub'] ?? $profile['id'],
                    json_encode($profile)
                ]);
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO users (username, email, password, full_name, {$provider}_id, {$provider}_token, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())"
                );
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($tempPassword, PASSWORD_DEFAULT),
                    $name,
                    $profile['sub'] ?? $profile['id'],
                    json_encode($profile)
                ]);
            }
            
            $userId = $db->lastInsertId();
            
            return [
                'success' => true,
                'user_id' => $userId,
                'user_type' => $userType,
                'user_name' => $name,
                'user_email' => $email,
                'is_new' => true,
                'provider' => $provider
            ];
            
        } catch (PDOException $e) {
            return ['error' => 'Failed to create account. Please try again or register manually.'];
        }
    }
    
    /**
     * Generate unique username from email
     * @param string $email Email address
     * @param string $userType User type
     * @return string Generated username
     */
    private static function generateUsername($email, $userType) {
        $baseUsername = explode('@', $email)[0];
        $db = getDB();
        
        // Check all tables for username existence
        $username = $baseUsername;
        $counter = 1;
        
        $tables = ['users', 'restaurants', 'ngos', 'admins'];
        
        while (true) {
            $exists = false;
            foreach ($tables as $table) {
                $stmt = $db->prepare("SELECT id FROM {$table} WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                return $username;
            }
            
            $username = $baseUsername . $counter;
            $counter++;
        }
    }
}
?>
