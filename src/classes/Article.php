<?php

class Article
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }


    public function fetch(int $id, bool $published = true): array
    {
        $sql = "SELECT a.id, a.title, a.summary, a.content, a.created, a.category_id, a.user_id, a.published, c.name AS category,
        CONCAT(u.forename, ' ', u.surname) as author,
        i.id as image_id, i.filename as image_file, i.alttext as image_alt
        FROM articles as a
        JOIN category as c ON a.category_id = c.id
        JOIN user as u ON a.user_id =  u.id
        LEFT JOIN images as i ON a.images_id = i.id
        WHERE a.id = :id";
        if ($published) {
            $sql .= "AND a.published = 1";
        }
        $sql .= ";";

        return $this->db->sql_execute($sql, ['id' => $id])->fetch();
    }

    public function getAll(int $cat_id = null, bool $published = true, int $user_id = null, int $limit = 1000): array
    {
        $sql = "SELECT a.id, a.title, a.summary, a.category_id, a.user_id, a.published, c.name AS category,
        CONCAT(u.forename, ' ', u.surname) as author,
        i.filename as Image_file, i.alttext as image_alt
        FROM articles as a
        JOIN category as c ON a.category_id = c.id
        JOIN user as u ON a.user_id = u.id
        LEFT JOIN images as i ON a.images_id = i.id
        WHERE (a.category_id = :cat_id OR :cat_id IS NULL)
        AND (a.user_id = :user_id OR :user_id IS NULL)";

        if ($published) {
            $sql .= " AND a.published = 1";
        }
        $sql .= " ORDER BY a.id DESC LIMIT :limit";

        return $this->db->sql_execute($sql, ['cat_id' => $cat_id, 'user_id' => $user_id, 'limit' => $limit])->fetchAll();
    }
}
