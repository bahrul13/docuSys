<?php 
session_start(); 
require "../db/db_conn.php"; 
require "log_handler.php"; 

logTabCloseLogout($conn); 

$conn->close();