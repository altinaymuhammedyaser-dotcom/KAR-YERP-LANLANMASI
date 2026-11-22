<?php
// serve.php

// Set the directory to serve
$directory = __DIR__;

// Set the file to serve
$file = "inject3.js";

// Set the path to the file
$path = "$directory/$file";

// Check if the file exists
if (file_exists($path)) {
    // Set the content type
    header('Content-Type: application/javascript');

    // Set the content length
    header('Content-Length: ' . filesize($path));

    // Set the content disposition
    header('Content-Disposition: inline; filename="' . basename($path) . '"');

    // Read the file and output it
    readfile($path);
} else {
    // Return a 404 error
    http_response_code(404);
    echo "File not found";
}