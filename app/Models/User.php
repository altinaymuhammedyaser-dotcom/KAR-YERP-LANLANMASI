<?php
/**
 * Akilli Kariyer - Kullanici modeli
 */

namespace App\Models;

use App\Core\Database;

class User
{
    public int $id = 0;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $education_level = 'universite';
    public bool $is_active = true;
    public ?string $created_at = null;
    public ?string $last_login_at = null;

    /** @var string[] */
    public array $roles = [];

    /** @var array|null */
    public ?array $profile = null;

    public static function find(int $id): ?self
    {
        $row = Database::fetch('SELECT * FROM `users` WHERE `id` = ? LIMIT 1', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $row = Database::fetch('SELECT * FROM `users` WHERE `email` = ? LIMIT 1', [mb_strtolower(trim($email))]);
        return $row ? self::hydrate($row) : null;
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM `users` WHERE `email` = ?';
        $params = [mb_strtolower(trim($email))];
        if ($exceptId) {
            $sql .= ' AND `id` <> ?';
            $params[] = $exceptId;
        }
        return (int) Database::fetchColumn($sql, $params) > 0;
    }

    /**
     * Yeni kullanici olusturur ve profil + rol kayitlarini yazar.
     */
    public static function create(array $data): int
    {
        return Database::transaction(function () use ($data) {
            $id = Database::insert(
                'INSERT INTO `users` (`first_name`,`last_name`,`email`,`password`,`education_level`,`is_active`,`created_at`,`updated_at`)
                 VALUES (?,?,?,?,?,1,NOW(),NOW())',
                [
                    trim($data['first_name']),
                    trim($data['last_name']),
                    mb_strtolower(trim($data['email'])),
                    $data['password'],
                    $data['education_level'] ?? 'universite',
                ]
            );

            Database::query(
                'INSERT INTO `user_profiles` (`user_id`,`preferred_work_style`) VALUES (?,?)',
                [$id, $data['preferred_work_style'] ?? 'karma']
            );

            Database::query(
                'INSERT INTO `user_roles` (`user_id`,`role`) VALUES (?,?)',
                [$id, $data['role'] ?? 'user']
            );

            return $id;
        });
    }

    public static function update(int $id, array $data): int
    {
        $allowed = ['first_name', 'last_name', 'email', 'password', 'education_level', 'is_active', 'must_change_password', 'email_verified_at', 'last_login_at', 'last_login_ip', 'remember_token'];
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        if (!$sets) {
            return 0;
        }
        $params[] = $id;
        return Database::update('UPDATE `users` SET ' . implode(', ', $sets) . ' WHERE `id` = ?', $params);
    }

    public static function delete(int $id): int
    {
        return Database::update('DELETE FROM `users` WHERE `id` = ?', [$id]);
    }

    public static function updateProfile(int $userId, array $data): int
    {
        $allowed = ['phone', 'birth_date', 'city', 'school_name', 'study_field', 'grade_level', 'bio', 'avatar', 'preferred_work_style'];
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "`$key` = ?";
                $params[] = $value === '' ? null : $value;
            }
        }
        if (!$sets) {
            return 0;
        }
        $params[] = $userId;

        $exists = (int) Database::fetchColumn('SELECT COUNT(*) FROM `user_profiles` WHERE `user_id` = ?', [$userId]);
        if ($exists === 0) {
            Database::query('INSERT INTO `user_profiles` (`user_id`) VALUES (?)', [$userId]);
        }
        return Database::update('UPDATE `user_profiles` SET ' . implode(', ', $sets) . ' WHERE `user_id` = ?', $params);
    }

    public function hasRole(string $role): bool
    {
        if (!$this->roles) {
            $this->roles = Database::fetchAll(
                'SELECT `role` FROM `user_roles` WHERE `user_id` = ?',
                [$this->id]
            );
            $this->roles = array_column($this->roles, 'role');
        }
        return in_array($role, $this->roles, true);
    }

    public function addRole(string $role): void
    {
        Database::query(
            'INSERT IGNORE INTO `user_roles` (`user_id`,`role`) VALUES (?,?)',
            [$this->id, $role]
        );
        $this->roles[] = $role;
    }

    public function removeRole(string $role): void
    {
        Database::query('DELETE FROM `user_roles` WHERE `user_id` = ? AND `role` = ?', [$this->id, $role]);
        $this->roles = array_values(array_diff($this->roles, [$role]));
    }

    public function profile(): ?array
    {
        if ($this->profile === null) {
            $this->profile = Database::fetch('SELECT * FROM `user_profiles` WHERE `user_id` = ? LIMIT 1', [$this->id]);
        }
        return $this->profile;
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function initials(): string
    {
        $first = mb_substr($this->first_name, 0, 1);
        $last  = mb_substr($this->last_name, 0, 1);
        return mb_strtoupper($first . $last);
    }

    public function educationLabel(): string
    {
        $map = [
            'ortaokul' => 'Ortaokul',
            'lise'     => 'Lise',
            'universite' => 'Üniversite',
            'diger'    => 'Diğer',
        ];
        return $map[$this->education_level] ?? 'Belirtilmedi';
    }

    private static function hydrate(array $row): self
    {
        $u = new self();
        $u->id = (int) $row['id'];
        $u->first_name = $row['first_name'];
        $u->last_name = $row['last_name'];
        $u->email = $row['email'];
        $u->password = $row['password'];
        $u->education_level = $row['education_level'];
        $u->is_active = (bool) $row['is_active'];
        $u->created_at = $row['created_at'] ?? null;
        $u->last_login_at = $row['last_login_at'] ?? null;
        return $u;
    }
}
