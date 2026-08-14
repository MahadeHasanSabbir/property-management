<?php
/**
 * Deed document attachments — storage and metadata.
 *
 * Entirely new: the legacy app had no file handling at all (profile/upload.php
 * was misleadingly named — it only inserted form text).
 *
 * Storage rules:
 *   - Files live in storage/uploads/, which is refused by two independent
 *     .htaccess rules and has the PHP engine switched off.
 *   - The name on disk is random with a .bin extension, so a smuggled .php
 *     cannot execute even if those rules are ever removed.
 *   - The real name and MIME type live in the database and are only used when
 *     serving the file back through an authorised controller.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Document
{
    public static function forProperty(int $propertyId): array
    {
        return Database::all(
            'SELECT * FROM property_documents WHERE property_id = ? ORDER BY created_at DESC',
            [$propertyId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM property_documents WHERE id = ?', [$id]);
    }

    public static function countForUser(int $userId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM property_documents WHERE user_id = ?',
            [$userId]
        );
    }

    public static function absolutePath(array $document): string
    {
        return UPLOAD_PATH . '/' . $document['stored_name'];
    }

    /**
     * Validate and store an uploaded file.
     *
     * @param array $file one entry from $_FILES
     * @throws \RuntimeException with a translated message on any rejection
     */
    public static function store(array $file, int $propertyId, int $userId): int
    {
        self::assertUploadOk($file);

        if ($file['size'] > UPLOAD_MAX_BYTES) {
            throw new \RuntimeException(t('document.too_large', [
                'max' => self::formatBytes(UPLOAD_MAX_BYTES),
            ]));
        }

        // Trust the file's contents, never the browser-supplied type or the
        // extension. finfo is available on this PHP build; gd and imagick are
        // not, so no re-encoding is possible or attempted.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($file['tmp_name']);

        if (!in_array($mime, explode(',', UPLOAD_ALLOWED_MIME), true)) {
            throw new \RuntimeException(t('document.bad_type'));
        }

        if (!is_dir(UPLOAD_PATH) && !@mkdir(UPLOAD_PATH, 0775, true) && !is_dir(UPLOAD_PATH)) {
            throw new \RuntimeException(t('document.upload_failed'));
        }

        $stored = bin2hex(random_bytes(16)) . '.bin';
        $target = UPLOAD_PATH . '/' . $stored;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException(t('document.upload_failed'));
        }
        @chmod($target, 0640);

        try {
            Database::run(
                'INSERT INTO property_documents
                    (property_id, user_id, original_name, stored_name, mime, size_bytes, sha256)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $propertyId,
                    $userId,
                    mb_substr(self::sanitiseName($file['name']), 0, 255),
                    $stored,
                    $mime,
                    (int) $file['size'],
                    hash_file('sha256', $target),
                ]
            );
        } catch (\Throwable $e) {
            // Never leave an orphan file behind if the row fails to insert.
            @unlink($target);
            throw $e;
        }

        return (int) Database::lastId();
    }

    public static function delete(array $document): void
    {
        Database::run('DELETE FROM property_documents WHERE id = ?', [$document['id']]);
        @unlink(self::absolutePath($document));
    }

    /** Remove every file belonging to a user, then their rows. */
    public static function deleteAllForUser(int $userId): void
    {
        foreach (Database::all('SELECT * FROM property_documents WHERE user_id = ?', [$userId]) as $doc) {
            @unlink(self::absolutePath($doc));
        }
        Database::run('DELETE FROM property_documents WHERE user_id = ?', [$userId]);
    }

    // --- Helpers -------------------------------------------------------------

    /**
     * Turn PHP's upload error codes into a usable message.
     *
     * The awkward case is exceeding post_max_size (40M here): PHP discards the
     * request body, so $_POST and $_FILES both arrive EMPTY with no error code
     * at all. The controller checks CONTENT_LENGTH before reaching this point.
     */
    private static function assertUploadOk(array $file): void
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'] ?? '')) {
            return;
        }

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new \RuntimeException(t('document.too_large', [
                'max' => self::formatBytes(UPLOAD_MAX_BYTES),
            ]));
        }

        throw new \RuntimeException(t('document.upload_failed'));
    }

    /** Keep a readable original name without letting it become a path. */
    private static function sanitiseName(string $name): string
    {
        $name = str_replace(["\0", '/', '\\'], '', $name);
        $name = preg_replace('/\s+/u', ' ', trim($name));
        return $name === '' ? 'document' : $name;
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }
        return $bytes . ' B';
    }
}
