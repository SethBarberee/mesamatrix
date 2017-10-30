<?php

// Default configuration for MesaMatrix
// Copy to config/config.php for use

use \Monolog\Logger as Log;
/* available log levels:
 * Log::EMERGENCY
 * Log::ALERT
 * Log::CRITICAL
 * Log::ERROR
 * Log::WARNING (default)
 * Log::NOTICE
 * Log::INFO
 * Log::DEBUG
 */

$CONFIG = array(
    "info" => array(
        "log_level" => Log::WARNING,
        "version" => "1.0",
        "title" => "The OpenGL vs Mesa matrix",
        "description" => "Show Mesa progress for the OpenGL implementation into an easy to read HTML page.",
        "xml_file" => "http/features.xml",
        "project_url" => "https://github.com/MightyCreak/mesamatrix",
        "commitlog_length" => 10,
        "authors" => array(
            // Either '$author' or '$author => $website'
            "Romain 'Creak' Failliot",
            "Tobias Droste",
            "Robin McCorkell",
        ),
        "private_dir" => "private",
    ),

    "opengl_links" => array(
        "enabled" => TRUE,
        "url_gl" => "https://www.khronos.org/registry/OpenGL/extensions/",
        "cache_file" => "urlcache.json",
    ),

    "git" => array(
        "mesa_web" => "https://cgit.freedesktop.org/mesa/mesa",
        "mesa_url" => "https://anongit.freedesktop.org/git/mesa/mesa.git",
        "mesa_dir" => "mesa.git",
        "gl_filepaths" => ["docs/GL3.txt", "docs/features.txt"],
    ),
);
