<?php
session_start();
require 'upload_item.php';

$session_id = session_id();
$item = $_POST['item'];
$image = $_POST['image'];
$price = $_POST['price'];

$query = $conn->prepare("SELECT * FROM cart_items WHERE session_id = ? AND item_name = ?");
$query->bind_param("ss", $session_id, $item);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $conn->query("UPDATE cart_items SET quantity = quantity + 1 WHERE session_id = '$session_id' AND item_name = '$item'");
} else {
    $stmt = $conn->prepare("INSERT INTO cart_items (session_id, item_name, image_url, price, quantity) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssd", $session_id, $item, $image, $price);
    $stmt->execute();
}
echo "success";
?>
