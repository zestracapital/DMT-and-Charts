<?php
/**
 * Centralized error logging for DMT plugin
 */
if (!defined('ABSPATH')) exit;

class ZC_DMT_Error_Logger {
    public static function init() {
        set_error_handler([__CLASS__, 'handle_error']);
        register_shutdown_function([__CLASS__, 'handle_shutdown']);
    }

    public static function handle_error($severity, $message, $file, $line) {
        $log = sprintf("[PHP Error] Severity: %s | Message: %s | File: %s | Line: %s", 
            $severity, $message, $file, $line);
        error_log($log);
        // Don't execute PHP internal error handler
        return true;
    }

    public static function handle_shutdown() {
        $error = error_get_last();
        if ($error) {
            $log = sprintf("[Shutdown Error] Type: %s | Message: %s | File: %s | Line: %s", 
                $error['type'], $error['message'], $error['file'], $error['line']);
            error_log($log);
        }
    }
}
