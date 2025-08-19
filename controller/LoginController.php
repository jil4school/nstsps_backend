<?php
require_once __DIR__ . '/Controller.php';

class LoginController extends Controller
{
    public function login($email, $password)
    {
        $this->setStatement("SELECT user_id, password, role FROM student_account WHERE email = :email");
        $this->statement->execute(['email' => $email]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // For now, assuming plain text password
            if ($user['password'] === $password) {
                return [
                    'success' => true,
                    'user_id' => $user['user_id'],
                    'role'    => $user['role'] // ✅ include role
                ];
            } else {
                return ['error' => 'Incorrect password'];
            }
        } else {
            return ['error' => 'Email not found'];
        }
    }

    public function verifyOldPassword($user_id, $oldPassword)
    {
        $this->setStatement("SELECT password FROM student_account WHERE user_id = :user_id");
        $this->statement->execute(['user_id' => $user_id]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['password'] === $oldPassword) {
                return ['match' => true];
            } else {
                return ['match' => false];
            }
        } else {
            return ['error' => 'User not found'];
        }
    }

    public function updatePassword($user_id, $newPassword)
    {
        $this->setStatement("UPDATE student_account SET password = :password WHERE user_id = :user_id");
        return $this->statement->execute([
            'password' => $newPassword,
            'user_id'  => $user_id
        ]);
    }
}
