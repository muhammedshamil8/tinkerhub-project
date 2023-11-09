<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'db_connect.php';
$user_id = $_GET['userId'];
try {
    if ($conn) {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case "POST":
                $task = json_decode(file_get_contents('php://input'));

                if (empty($task->title) || empty($task->startDate) || empty($task->endDate)) {
                    echo json_encode(['status' => 0, 'message' => 'All fields must be filled']);
                } elseif (strlen($task->title) < 5 &&  strlen($task->title) > 10) {
                    echo json_encode(['status' => 0, 'message' => 'Task title must contain at least 5 characters']);
                } else {
                    $checkTaskQuery = "SELECT user_id FROM event WHERE user_id = ? AND task_name = ?";
                    $checkTaskStmt = $conn->prepare($checkTaskQuery);
                    $checkTaskStmt->bind_param('is', $user_id, $task->title);
                    $checkTaskStmt->execute();
                    $taskResult = $checkTaskStmt->get_result();

                    if ($taskResult->num_rows > 0) {
                        echo json_encode(['status' => 0, 'message' => 'Task with the same title already exists for this user']);
                    } else {
                        $sql = "INSERT INTO event (user_id, task_name, start_date, end_date, task_type, priority, description, task_progress, task_done, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, NOW(), NOW())";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('issssss', $user_id, $task->title, $task->startDate, $task->endDate, $task->taskType, $task->priority, $task->description);

                        if ($stmt->execute()) {
                            echo json_encode(['status' => 1, 'message' => 'Task created successfully']);
                        } else {
                            echo json_encode(['status' => 0, 'message' => 'Failed to create the task: ' . $stmt->error]);
                        }
                    }
                }
                break;
        }
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to connect to the database']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>