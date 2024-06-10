<?php
require 'includes/db-connect.php';
require 'includes/functions.php';
$cat_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
if ( ! $cat_id) {
    include 'page_not_found.php';
}

$sql = "SELECT forename, surname, joined, profile_pic FROM user WHERE id = :id;";