<?php
declare(strict_types=1);

final class User
{
    public function __construct(private PDO $database)
    {
    }

    public function findByDni(string $dni): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id_user AS id, dni, email, passwd AS password_hash, role, name AS full_name, 1 AS active FROM users WHERE dni = :dni LIMIT 1'
        );
        $statement->execute(['dni' => $dni]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function exists(string $dni, string $email): bool
    {
        $statement = $this->database->prepare(
            'SELECT id_user FROM users WHERE dni = :dni OR email = :email LIMIT 1'
        );
        $statement->execute(['dni' => $dni, 'email' => $email]);

        return (bool) $statement->fetch();
    }

    public function create(string $dni, string $email, string $fullName): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (dni, name, email, passwd, role) VALUES (:dni, :full_name, :email, :password_hash, :role)'
        );
        $statement->execute([
            'dni' => $dni,
            'email' => $email,
            'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'role' => 'padre',
        ]);

        return (int) $this->database->lastInsertId();
    }
}
