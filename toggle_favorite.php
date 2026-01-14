<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not logged in');
}
$user_id = $_SESSION['user_id'];

if (!isset($_POST['recipe_id'])) {
    http_response_code(400);
    exit('No recipe ID');
}

$recipe_id = intval($_POST['recipe_id']);
$set_fav = intval($_POST['set_fav']); // 1 or 0

$host = 'localhost';
$username = "root";
$password = "";
$db_recipe_list = "bitebook_recipe_list";
$conn = mysqli_connect($host, $username, $password, $db_recipe_list);

if (!$conn) {
    http_response_code(500);
    exit('DB error');
}

// Only allow updating user's own recipes
$sql = "UPDATE recipes SET is_favorite = $set_fav WHERE recipe_id = $recipe_id AND user_id = $user_id";
mysqli_query($conn, $sql);

echo 'ok';
?>