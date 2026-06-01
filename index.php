<?php

/**
 * Bootstrap for shared hosting when document root is the project folder (not /public).
 * cPanel addon domains often point to public_html/guhan.in/ — this file loads Laravel.
 */
require __DIR__ . '/public/index.php';
