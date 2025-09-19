<?php
require_once __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT user_id, password FROM student_account");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    if (!empty($user['password']) && !password_get_info($user['password'])['algo']) {
        // not hashed yet (still plain text)
        $hashed = password_hash($user['password'], PASSWORD_BCRYPT);

        $update = $pdo->prepare("UPDATE student_account SET password = :password WHERE user_id = :id");
        $update->execute([
            'password' => $hashed,
            'id' => $user['user_id']
        ]);

        echo "Updated user_id {$user['user_id']} from {$user['password']} → {$hashed}\n";
    }
}
