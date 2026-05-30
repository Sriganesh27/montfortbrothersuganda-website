<?php
// weblangu/includes/language_manager.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Handle Language Switcher Request SAFELY
if (isset($_GET['lang'])) {
    $allowed_langs = ['en', 'fr', 'es', 'it', 'de'];
    $new_lang = strtolower(trim($_GET['lang']));
    
    if (in_array($new_lang, $allowed_langs)) {
        $_SESSION['language'] = $new_lang;
        setcookie('site_lang', $new_lang, time() + (86400 * 30), "/"); 
    }
    
    // Safely redirect back without the ?lang= parameter
    $url = preg_replace('/([?&])lang=[^&]+(&|$)/', '$1', $_SERVER['REQUEST_URI']);
    $url = rtrim($url, '?&');
    if (empty($url)) { $url = '/'; }
    
    header("Location: $url");
    exit;
}

// 2. The Bulletproof Translation Function
function t($key) {
    static $translations = null;
    static $fallback_translations = null; 
    
    $lang = $_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en';

    if ($translations === null) {
        $file_path = __DIR__ . "/../storage/{$lang}.json";
        if (file_exists($file_path)) {
            $translations = json_decode(file_get_contents($file_path), true);
            // ALERTS YOU IF YOUR JSON IS BROKEN
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<script>console.error('JSON ERROR in {$lang}.json: " . json_last_error_msg() . "'); alert('JSON ERROR in {$lang}.json: " . json_last_error_msg() . "');</script>";
            }
        }
        if (!is_array($translations)) {
            $translations = [];
        }

        if ($lang !== 'en') {
            $fallback_path = __DIR__ . "/../storage/en.json";
            if (file_exists($fallback_path)) {
                $fallback_translations = json_decode(file_get_contents($fallback_path), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo "<script>console.error('JSON ERROR in en.json: " . json_last_error_msg() . "'); alert('JSON ERROR in en.json: " . json_last_error_msg() . "');</script>";
                }
            }
            if (!is_array($fallback_translations)) {
                $fallback_translations = [];
            }
        } else {
            $fallback_translations = $translations; 
        }
    }

    if (isset($translations[$key])) {
        $value = $translations[$key];                 
    } elseif (isset($fallback_translations[$key])) {
        $value = $fallback_translations[$key];        
    } else {
        $value = $key;                                
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>