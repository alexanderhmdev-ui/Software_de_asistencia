<?php
declare(strict_types=1);

final class Post
{
    public function __construct(private PDO $database)
    {
    }

    public function create(int $userId, string $title, string $description, string $type, ?string $fileUrl, ?string $imagePath): int
    {
        $statement = $this->database->prepare('INSERT INTO posts (user_id, title, descripcion, tye, file_url, image_patch) VALUES (:user_id, :title, :description, :type, :file_url, :image_path)');
        $statement->execute(['user_id' => $userId, 'title' => $title, 'description' => $description, 'type' => $type, 'file_url' => $fileUrl, 'image_path' => $imagePath]);
        return (int) $this->database->lastInsertId();
    }
}
