<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/Controller.php';

class LoginController extends Controller
{
    public function login($email, $password)
    {
        $this->setStatement("SELECT user_id, password, role, is_first_login 
                     FROM student_account 
                     WHERE email = :email");
        $this->statement->execute(['email' => $email]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            // ✅ Block deactivated accounts
            if ($user['is_first_login'] == 2) {
                return ['error' => 'Your account has been deactivated.'];
            }

            return [
                'success' => true,
                'user_id' => $user['user_id'],
                'role'    => $user['role'],
                'is_first_login' => $user['is_first_login']
            ];
        } else {
            return ['error' => 'Invalid credentials'];
        }
    }


    public function verifyOldPassword($user_id, $oldPassword)
    {
        $this->setStatement("SELECT password FROM student_account WHERE user_id = :user_id");
        $this->statement->execute(['user_id' => $user_id]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($oldPassword, $user['password'])) {
                return ['match' => true];
            } else {
                return ['error' => 'Old password is incorrect'];
            }
        } else {
            return ['error' => 'User not found'];
        }
    }

    public function updatePassword($user_id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->setStatement("UPDATE student_account 
                         SET password = :password 
                         WHERE user_id = :user_id");

        return $this->statement->execute([
            'password' => $hashedPassword,
            'user_id'  => $user_id
        ]);
    }

    public function changeFirstLoginPassword($user_id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->setStatement("UPDATE student_account 
                         SET password = :password, is_first_login = 0 
                         WHERE user_id = :user_id");

        return $this->statement->execute([
            'password' => $hashedPassword,
            'user_id'  => $user_id
        ]);
    }

    public function deactivateStudent($user_id)
    {
        $this->setStatement("UPDATE student_account SET is_first_login = 2 WHERE user_id = :user_id");
        return $this->statement->execute(['user_id' => $user_id]);
    }
    public function reactivateStudent($user_id)
    {
        $this->setStatement("UPDATE student_account SET is_first_login = 0 WHERE user_id = :user_id");
        return $this->statement->execute(['user_id' => $user_id]);
    }
}
