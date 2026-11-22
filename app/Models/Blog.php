<?php
/**
 * Akilli Kariyer - Blog modeli
 */

namespace App\Models;

use App\Core\Database;

class Blog
{
    public static function posts(array $filters = []): array
    {
        $where  = ["p.status = 'published'"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'bc.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term);
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT p.*, bc.name AS category_name, bc.slug AS category_slug, bc.color AS category_color, bc.icon AS category_icon,
                       u.first_name AS author_first_name, u.last_name AS author_last_name
                FROM `blog_posts` p
                INNER JOIN `blog_categories` bc ON bc.id = p.category_id
                LEFT JOIN `users` u ON u.id = p.author_id
                WHERE $whereSql
                ORDER BY p.published_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= ' OFFSET ' . (int) $filters['offset'];
            }
        }
        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where  = ["p.status = 'published'"];
        $params = [];
        if (!empty($filters['category'])) {
            $where[] = 'bc.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term);
        }
        $whereSql = implode(' AND ', $where);
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM `blog_posts` p INNER JOIN `blog_categories` bc ON bc.id = p.category_id WHERE $whereSql",
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT p.*, bc.name AS category_name, bc.slug AS category_slug, bc.color AS category_color, bc.icon AS category_icon,
                    u.first_name AS author_first_name, u.last_name AS author_last_name
             FROM `blog_posts` p
             INNER JOIN `blog_categories` bc ON bc.id = p.category_id
             LEFT JOIN `users` u ON u.id = p.author_id
             WHERE p.id = ? AND p.status = 'published' LIMIT 1",
            [$id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch(
            "SELECT p.*, bc.name AS category_name, bc.slug AS category_slug, bc.color AS category_color, bc.icon AS category_icon,
                    u.first_name AS author_first_name, u.last_name AS author_last_name
             FROM `blog_posts` p
             INNER JOIN `blog_categories` bc ON bc.id = p.category_id
             LEFT JOIN `users` u ON u.id = p.author_id
             WHERE p.slug = ? AND p.status = 'published' LIMIT 1",
            [$slug]
        );
    }

    public static function categories(): array
    {
        return Database::fetchAll(
            'SELECT bc.*, (SELECT COUNT(*) FROM `blog_posts` p WHERE p.category_id = bc.id AND p.status = "published") AS post_count
             FROM `blog_categories` bc ORDER BY bc.sort_order ASC'
        );
    }

    public static function related(int $postId, int $categoryId, int $limit = 3): array
    {
        return Database::fetchAll(
            "SELECT p.*, bc.name AS category_name, bc.slug AS category_slug, bc.color AS category_color
             FROM `blog_posts` p INNER JOIN `blog_categories` bc ON bc.id = p.category_id
             WHERE p.status = 'published' AND p.id <> ? AND p.category_id = ?
             ORDER BY p.published_at DESC LIMIT " . (int) $limit,
            [$postId, $categoryId]
        );
    }

    public static function incrementView(int $id): void
    {
        Database::query('UPDATE `blog_posts` SET `view_count` = `view_count` + 1 WHERE `id` = ?', [$id]);
    }

    public static function totalCount(): int
    {
        return (int) Database::fetchColumn("SELECT COUNT(*) FROM `blog_posts` WHERE `status` = 'published'");
    }
}
