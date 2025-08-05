<?php

namespace App\Helpers;

class PdfHelper
{
    /**
     * Safely clean UTF-8 strings for PDF generation
     */
    public static function cleanUtf8String($string)
    {
        if ($string === null) {
            return '-';
        }
        
        $string = (string) $string;
        
        // Detect encoding and convert to UTF-8
        $encoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding ?: 'UTF-8');
        }
        
        // Remove malformed sequences
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // Replace problematic characters
        $string = str_replace([
            chr(226).chr(128).chr(156), chr(226).chr(128).chr(157), // Smart quotes
            chr(226).chr(128).chr(152), chr(226).chr(128).chr(153), // Smart apostrophes
            chr(226).chr(128).chr(147), chr(226).chr(128).chr(148), // Dashes
        ], ['"', '"', "'", "'", '-', '-'], $string);
        
        // Remove control characters
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
        
        return trim($string) ?: '-';
    }
    
    /**
     * Format currency safely
     */
    public static function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount ?? 0, 0, ',', '.');
    }
    
    /**
     * Format date safely
     */
    public static function formatDate($date)
    {
        if (!$date) {
            return '-';
        }
        
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return '-';
        }
    }
}
