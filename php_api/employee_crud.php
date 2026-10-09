<?php
include 'condb.php';
header("Content-Type: application/json; charset=UTF-8");

function respond($success, $message, $data = null, $status = 200)
{
    http_response_code($status);
    $response = ["success" => $success, "message" => $message];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_encode($response);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "GET") {
        // Use the existing employee table and emp_id primary key.
        $stmt = $conn->query("SELECT * FROM employee ORDER BY emp_id DESC");
        echo json_encode([
            "success" => true,
            "message" => "ดึงข้อมูลพนักงานเรียบร้อย",
            "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        respond(false, "ข้อมูล JSON ไม่ถูกต้อง", null, 400);
    }

    if ($method === "POST") {
        foreach (["firstName", "lastName", "phone", "username", "password"] as $field) {
            if (empty($data[$field])) {
                respond(false, "กรุณากรอกข้อมูลให้ครบ", null, 400);
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO employee (firstName, lastName, phone, username, password)
             VALUES (:firstName, :lastName, :phone, :username, :password)"
        );
        $stmt->execute([
            ":firstName" => $data["firstName"],
            ":lastName" => $data["lastName"],
            ":phone" => $data["phone"],
            ":username" => $data["username"],
            ":password" => password_hash($data["password"], PASSWORD_DEFAULT)
        ]);
        respond(true, "เพิ่มข้อมูลพนักงานเรียบร้อย");
    }

    if ($method === "PUT") {
        $emp_id = filter_var($data["emp_id"] ?? null, FILTER_VALIDATE_INT);
        if (!$emp_id || $emp_id < 1) {
            respond(false, "ไม่พบค่า emp_id", null, 400);
        }
        foreach (["firstName", "lastName", "phone", "username"] as $field) {
            if (empty($data[$field])) {
                respond(false, "กรุณากรอกข้อมูลให้ครบ", null, 400);
            }
        }

        if (!empty($data["password"])) {
            $stmt = $conn->prepare(
                "UPDATE employee SET firstName = :firstName, lastName = :lastName,
                 phone = :phone, username = :username, password = :password WHERE emp_id = :id"
            );
            $stmt->bindValue(":password", password_hash($data["password"], PASSWORD_DEFAULT));
        } else {
            $stmt = $conn->prepare(
                "UPDATE employee SET firstName = :firstName, lastName = :lastName,
                 phone = :phone, username = :username WHERE emp_id = :id"
            );
        }
        $stmt->bindValue(":firstName", $data["firstName"]);
        $stmt->bindValue(":lastName", $data["lastName"]);
        $stmt->bindValue(":phone", $data["phone"]);
        $stmt->bindValue(":username", $data["username"]);
        $stmt->bindValue(":id", $emp_id, PDO::PARAM_INT);
        $stmt->execute();
        respond(true, "แก้ไขข้อมูลพนักงานเรียบร้อย");
    }

    if ($method === "DELETE") {
        $emp_id = filter_var($data["emp_id"] ?? null, FILTER_VALIDATE_INT);
        if (!$emp_id || $emp_id < 1) {
            respond(false, "ไม่พบค่า emp_id", null, 400);
        }

        $stmt = $conn->prepare("DELETE FROM employee WHERE emp_id = :id");
        $stmt->bindValue(":id", $emp_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            respond(false, "ไม่พบข้อมูลพนักงาน", null, 404);
        }
        respond(true, "ลบข้อมูลพนักงานเรียบร้อย");
    }

    respond(false, "Method ไม่ถูกต้อง", null, 405);
} catch (PDOException $e) {
    respond(false, "เกิดข้อผิดพลาดในการจัดการข้อมูลพนักงาน", null, 500);
}
?>
