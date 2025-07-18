<?php
// Simple test file to check if PHP is working
echo "PHP is working!";
echo "<br>Current time: " . date('Y-m-d H:i:s');
echo "<br>PHP version: " . phpversion();
echo "<br>Document root: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Request URI: " . $_SERVER['REQUEST_URI'];
echo "<br>Script name: " . $_SERVER['SCRIPT_NAME'];
?> 