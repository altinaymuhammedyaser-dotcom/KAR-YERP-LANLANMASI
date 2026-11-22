<?php
/**
 * Akilli Kariyer - Meslek modeli
 */

namespace App\Models;

use App\Core\Database;

class Career
{
    /* ------------------------------------------------------------------ */
    /* Listeleme ve filtreleme                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Meslekleri filtreler ile birlikte listeler.
     *
     * @param array{
     *   search?:string, category?:string, education?:string, interest?:string,
     *   sort?:string, featured?:bool, limit?:int, offset?:int, count?:bool
     * } $filters
     */
    public static function list(array $filters = []): array
    {
        $where  = ['c.is_active = 1'];
        $params = [];
        $joins  = '';

        if (!empty($filters['search'])) {
            $where[] = '(c.name LIKE ? OR c.short_description LIKE ? OR c.description LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term);
        }

        if (!empty($filters['category'])) {
            $where[] = 'cat.slug = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['education'])) {
            $where[] = 'c.education_level = ?';
            $params[] = $filters['education'];
        }

        if (!empty($filters['interest'])) {
            $joins .= ' INNER JOIN `career_interests` ci ON ci.career_id = c.id AND ci.interest_key = ?';
            array_unshift($params, $filters['interest']);
        }

        if (!empty($filters['featured'])) {
            $where[] = 'c.is_featured = 1';
        }

        $orderBy = match ($filters['sort'] ?? 'popular') {
            'name_asc'   => 'c.name ASC',
            'name_desc'  => 'c.name DESC',
            'newest'     => 'c.created_at DESC',
            'oldest'     => 'c.created_at ASC',
            'category'   => 'cat.name ASC, c.name ASC',
            'popular'    => 'c.view_count DESC, c.is_featured DESC, c.name ASC',
            default      => 'c.view_count DESC, c.is_featured DESC, c.name ASC',
        };

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color,
                       cat.icon AS category_icon
                FROM `careers` c
                INNER JOIN `career_categories` cat ON cat.id = c.category_id
                $joins
                WHERE $whereSql
                ORDER BY $orderBy";

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
        $where  = ['c.is_active = 1'];
        $params = [];
        $joins  = '';

        if (!empty($filters['search'])) {
            $where[] = '(c.name LIKE ? OR c.short_description LIKE ? OR c.description LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term);
        }
        if (!empty($filters['category'])) {
            $where[] = 'cat.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['education'])) {
            $where[] = 'c.education_level = ?';
            $params[] = $filters['education'];
        }
        if (!empty($filters['interest'])) {
            $joins .= ' INNER JOIN `career_interests` ci ON ci.career_id = c.id AND ci.interest_key = ?';
            array_unshift($params, $filters['interest']);
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(DISTINCT c.id)
                FROM `careers` c
                INNER JOIN `career_categories` cat ON cat.id = c.category_id
                $joins
                WHERE $whereSql";

        return (int) Database::fetchColumn($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color,
                    cat.icon AS category_icon
             FROM `careers` c
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.id = ? AND c.is_active = 1 LIMIT 1',
            [$id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color,
                    cat.icon AS category_icon
             FROM `careers` c
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.slug = ? AND c.is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    /* ------------------------------------------------------------------ */
    /* Kategoriler                                                          */
    /* ------------------------------------------------------------------ */

    public static function categories(bool $onlyActive = true): array
    {
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM `careers` cr WHERE cr.category_id = c.id AND cr.is_active = 1) AS career_count
                FROM `career_categories` c';
        if ($onlyActive) {
            $sql .= ' WHERE c.is_active = 1';
        }
        $sql .= ' ORDER BY c.sort_order ASC, c.name ASC';
        return Database::fetchAll($sql);
    }

    public static function categoryBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM `career_categories` WHERE `slug` = ? LIMIT 1', [$slug]);
    }

    /* ------------------------------------------------------------------ */
    /* Kriterler ve gereksinimler                                           */
    /* ------------------------------------------------------------------ */

    public static function interests(int $careerId): array
    {
        return Database::fetchAll('SELECT `interest_key`, `weight` FROM `career_interests` WHERE `career_id` = ? ORDER BY `weight` DESC', [$careerId]);
    }

    public static function abilities(int $careerId): array
    {
        return Database::fetchAll('SELECT `ability_key`, `weight` FROM `career_skills` WHERE `career_id` = ? ORDER BY `weight` DESC', [$careerId]);
    }

    public static function preferences(int $careerId): array
    {
        return Database::fetchAll('SELECT `preference_key`, `weight` FROM `career_preferences` WHERE `career_id` = ? ORDER BY `weight` DESC', [$careerId]);
    }

    public static function environments(int $careerId): array
    {
        return Database::fetchAll('SELECT `environment_key`, `weight` FROM `career_environments` WHERE `career_id` = ? ORDER BY `weight` DESC', [$careerId]);
    }

    public static function competencies(int $careerId): array
    {
        return Database::fetchAll('SELECT * FROM `career_competencies` WHERE `career_id` = ? ORDER BY `sort_order` ASC', [$careerId]);
    }

    public static function requirements(int $careerId, ?string $type = null): array
    {
        $sql = 'SELECT * FROM `career_requirements` WHERE `career_id` = ?';
        $params = [$careerId];
        if ($type !== null) {
            $sql .= ' AND `requirement_type` = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY `sort_order` ASC';
        return Database::fetchAll($sql, $params);
    }

    /**
     * Benzer meslekler (ayni kategori veya ortak ilgi alanlari).
     */
    public static function related(int $careerId, int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color
             FROM `careers` c
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.is_active = 1 AND c.id <> ?
               AND (c.category_id = (SELECT category_id FROM careers WHERE id = ?)
                    OR c.id IN (SELECT career_id FROM career_interests WHERE interest_key IN
                        (SELECT interest_key FROM career_interests WHERE career_id = ?)))
             ORDER BY (c.category_id = (SELECT category_id FROM careers WHERE id = ?)) DESC, c.view_count DESC
             LIMIT ' . (int) $limit,
            [$careerId, $careerId, $careerId, $careerId]
        );
    }

    /* ------------------------------------------------------------------ */
    /* Gosterim / sayfalama                                                 */
    /* ------------------------------------------------------------------ */

    public static function incrementView(int $careerId): void
    {
        Database::query('UPDATE `careers` SET `view_count` = `view_count` + 1 WHERE `id` = ?', [$careerId]);
    }

    public static function mostViewed(int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color
             FROM `careers` c INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.is_active = 1 ORDER BY c.view_count DESC LIMIT ' . (int) $limit
        );
    }

    public static function featured(int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color
             FROM `careers` c INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.is_active = 1 AND c.is_featured = 1 ORDER BY c.sort_order ASC LIMIT ' . (int) $limit
        );
    }

    public static function random(int $limit = 4): array
    {
        return Database::fetchAll(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color
             FROM `careers` c INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE c.is_active = 1 ORDER BY RAND() LIMIT ' . (int) $limit
        );
    }

    public static function totalCount(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM `careers` WHERE `is_active` = 1');
    }

    /* ------------------------------------------------------------------ */
    /* Filtre secenekleri                                                   */
    /* ------------------------------------------------------------------ */

    public static function educationLevels(): array
    {
        return [
            'ortaokul'  => 'Ortaokul',
            'lise'      => 'Lise',
            'universite' => 'Üniversite',
            'diger'     => 'Diğer',
        ];
    }

    public static function availableInterests(): array
    {
        return Database::fetchAll(
            'SELECT interest_key, COUNT(*) AS total FROM `career_interests` GROUP BY interest_key ORDER BY total DESC'
        );
    }
}
