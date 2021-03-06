<?php
require_once '_db.php';

$json = file_get_contents('php://input');
$params = json_decode($json);

$stmt = $db->prepare("SELECT * FROM reservations WHERE NOT ((end <= :start) OR (start >= :end)) AND id <> :id AND room_id = :resource");
$stmt->bindParam(':start', $params->newStart);
$stmt->bindParam(':end', $params->newEnd);
$stmt->bindParam(':id', $params->id);
$stmt->bindParam(':resource', $params->newResource);
$stmt->execute();
$overlaps = $stmt->rowCount() > 0;

if ($overlaps) {
    $response = new Result();
    $response->result = 'Error';
    $response->message = 'This reservation overlaps with an existing reservation.';

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$stmt = $db->prepare("UPDATE reservations SET start = :start, end = :end, room_id = :resource WHERE id = :id");
$stmt->bindParam(':start', $params->newStart);
$stmt->bindParam(':end', $params->newEnd);
$stmt->bindParam(':id', $params->id);
$stmt->bindParam(':resource', $params->newResource);
$stmt->execute();

class Result {}
$response = new Result();
$response->result = 'OK';
$response->message = 'Update successful';

header('Content-Type: application/json');
echo json_encode($response);

?>
