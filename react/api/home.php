<?php
session_start();

echo "Home page";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'db_connect.php'; 

$user_id = $_GET['userId'];

if ($_SESSION['id'] = $user_id) {
    $Query = "SELECT * FROM users WHERE id = ?";
    $Stmt = $conn->prepare($Query);
    $Stmt->bind_param('s', $user_id);
    $Stmt->execute();
    $Result = $Stmt->get_result();

    if ($Result->num_rows > 0) {
        $userData = $Result->fetch_assoc();
        $username = $userData['username'];
        $pronounce =  $userData['pronounce'];
        $profession =  $userData['profession'];
        echo json_encode(['username' => $username, 'user_id' => $user_id , 'pronounce' => $pronounce , 'profession' => $profession]);
    } else {
        http_response_code(404); 
        echo json_encode(['error' => 'User not found']);
    }
} else {
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized']);
}
?>
