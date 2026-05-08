<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'support';
$_SESSION['full_name'] = 'Support Demo';
require 'incoming-requests.php';

