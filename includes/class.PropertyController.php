<?php
/**
 * Property records: listing, create/edit/delete, search, CSV export, and the
 * document attachments that hang off a record.
 *
 * Every action derives the owner from the session and passes it into the query,
 * so there is no path that reads another user's data. The legacy code built a
 * table name out of $_SESSION['id'] — or, in the admin pages, straight out of
 * $_GET, which meant `DROP TABLE user<whatever-you-typed>`.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class PropertyController
{
    /** Filter keys accepted from the encoded ?q= token. Anything else is dropped. */
    private const FILTER_KEYS = [
        'dag', 'dag_scope', 'khatian', 'khatian_scope', 'deed_no', 'mouja',
        'owner', 'owner_mode', 'area_min', 'area_max', 'date_from', 'date_to',
        'mode', 'sort', 'dir', 'page', 'per',
    ];

    // --- Listing and search --------------------------------------------------

    public function index(): void
    {
        $this->renderSearch('properties.index', 'properties');
    }

    public function search(): void
    {
        $this->renderSearch('properties.search', 'search');
    }

    /**
     * Shared by the plain listing and the search screen — the listing is simply
     * a search with no filters applied.
     */
    private function renderSearch(string $view, string $active): void
    {
        $user   = Auth::user();
        $userId = (int) $user['id'];

        $isSearch = ($active === 'search');
        $basePath = $isSearch ? 'properties/search' : 'properties';

        // The filter form posts to a distinct path — POST /properties is
        // already "create a record" — and the controller redirects to the
        // encoded GET equivalent, so the result page is linkable and the URL
        // carries one opaque ?q= token instead of readable key=value pairs.
        $formAction = $isSearch ? 'properties/search' : 'properties/filter';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $_GET = $_POST;
            redirect($basePath, $this->activeFilters($this->readFilters()));
        }

        $filters = $this->readFilters();

        $perSizes = array_map('intval', explode(',', PAGE_SIZES));
        $per      = in_array((int) $filters['per'], $perSizes, true)
            ? (int) $filters['per']
            : PAGE_SIZE_DEFAULT;

        $page   = clamp_int($filters['page'], 1, 100000, 1);
        $offset = ($page - 1) * $per;

        $result = Property::search($userId, $filters, $per, $offset, $filters['sort'], $filters['dir']);

        // A page beyond the end of the results is a dead end; send the visitor
        // to the last real page instead of an empty table.
        $lastPage = max(1, (int) ceil($result['total'] / $per));
        if ($page > $lastPage && $result['total'] > 0) {
            redirect($basePath, ['page' => $lastPage] + $this->activeFilters($filters));
        }

        View::render($view, [
            'title'    => $active === 'search' ? t('search.title') : t('property.title'),
            'active'   => $active,
            'user'     => $user,
            'usage'    => PlanLimit::usage($user),
            'rows'     => $result['rows'],
            'total'    => $result['total'],
            'page'     => $page,
            'per'      => $per,
            'perSizes' => $perSizes,
            'lastPage' => $lastPage,
            'filters'  => $filters,
            'active_f' => $this->activeFilters($filters),
            'moujas'     => Property::moujasForUser($userId),
            'basePath'   => $basePath,
            'formAction' => $formAction,
        ]);
    }

    /**
     * Pull filters out of the request, whitelisted and normalised.
     *
     * The ?q= token expands into $_GET before this runs, so a crafted token can
     * put anything here — which is exactly why only known keys are read and
     * every value is constrained to an expected shape.
     */
    private function readFilters(): array
    {
        $in = only($_GET, self::FILTER_KEYS);

        $scope = static fn(string $key): string =>
            in_array($in[$key] ?? '', ['current', 'previous'], true) ? $in[$key] : 'any';

        $number = static function (string $key) use ($in): string {
            $value = trim((string) ($in[$key] ?? ''));
            return is_numeric($value) ? $value : '';
        };

        $date = static function (string $key) use ($in): string {
            $value = trim((string) ($in[$key] ?? ''));
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
        };

        $token = static function (string $key) use ($in): string {
            $value = trim((string) ($in[$key] ?? ''));
            return valid_identifier_token($value) ? $value : '';
        };

        return [
            'dag'           => $token('dag'),
            'dag_scope'     => $scope('dag_scope'),
            'khatian'       => $token('khatian'),
            'khatian_scope' => $scope('khatian_scope'),
            'deed_no'       => mb_substr(trim((string) ($in['deed_no'] ?? '')), 0, 40),
            'mouja'         => mb_substr(trim((string) ($in['mouja'] ?? '')), 0, 100),
            'owner'         => mb_substr(trim((string) ($in['owner'] ?? '')), 0, 120),
            'owner_mode'    => (($in['owner_mode'] ?? '') === 'contains') ? 'contains' : 'starts',
            'area_min'      => $number('area_min'),
            'area_max'      => $number('area_max'),
            'date_from'     => $date('date_from'),
            'date_to'       => $date('date_to'),
            'mode'          => (($in['mode'] ?? '') === 'any') ? 'any' : 'all',
            'sort'          => in_array($in['sort'] ?? '', Property::SORTABLE, true) ? $in['sort'] : 'seq',
            'dir'           => (strtoupper((string) ($in['dir'] ?? '')) === 'DESC') ? 'DESC' : 'ASC',
            'page'          => $in['page'] ?? 1,
            'per'           => $in['per'] ?? PAGE_SIZE_DEFAULT,
        ];
    }

    /** Only the filters actually set — used to rebuild links and show chips. */
    private function activeFilters(array $filters): array
    {
        $out = [];
        foreach ($filters as $key => $value) {
            if (in_array($key, ['page'], true)) {
                continue;
            }
            $isDefault = ($value === '')
                || ($key === 'mode' && $value === 'all')
                || ($key === 'owner_mode' && $value === 'starts')
                || (in_array($key, ['dag_scope', 'khatian_scope'], true) && $value === 'any')
                || ($key === 'sort' && $value === 'seq')
                || ($key === 'dir' && $value === 'ASC')
                || ($key === 'per' && (int) $value === PAGE_SIZE_DEFAULT);

            if (!$isDefault) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    // --- Show / create / edit ------------------------------------------------

    public function show(string $id): void
    {
        $user     = Auth::user();
        $property = $this->mustFind((int) $id, (int) $user['id']);

        View::render('properties.show', [
            'title'     => t('property.one') . ' #' . $property['seq'],
            'active'    => 'properties',
            'property'  => $property,
            'documents' => Document::forProperty((int) $property['id']),
            'usage'     => PlanLimit::usage($user),
        ]);
    }

    public function create(): void
    {
        $user  = Auth::user();
        $usage = PlanLimit::usage($user);

        // Being at or over the limit blocks creation but nothing else, so the
        // account stays usable and can be brought back under the limit.
        if ($usage['limit'] !== null && $usage['used'] >= $usage['limit']) {
            flash('warning', t('plan.upgrade_prompt', [
                'limit' => $usage['limit'],
                'plan'  => $usage['plan']['name'] ?? '—',
            ]));
            redirect('properties');
        }

        View::render('properties.form', [
            'title'    => t('property.add'),
            'active'   => 'properties',
            'property' => null,
            'moujas'   => Property::moujasForUser((int) $user['id']),
        ]);
    }

    public function edit(string $id): void
    {
        $user     = Auth::user();
        $property = $this->mustFind((int) $id, (int) $user['id']);

        View::render('properties.form', [
            'title'    => t('property.edit'),
            'active'   => 'properties',
            'property' => $property,
            'moujas'   => Property::moujasForUser((int) $user['id']),
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        $data = $this->readForm();

        if ($errors = $this->validate($data)) {
            $this->bounceWithErrors($errors, $data, 'properties/create');
        }

        try {
            $id = Property::create($user, $data);
        } catch (PlanLimitException $e) {
            flash('warning', $e->getMessage());
            redirect('properties');
            return;
        }

        AuditLog::record('property.create', 'property', (string) $id, ['deed_no' => $data['deed_no']]);

        flash('success', t('property.created'));
        redirect('properties/' . $id);
    }

    public function update(string $id): void
    {
        $user     = Auth::user();
        $property = $this->mustFind((int) $id, (int) $user['id']);
        $data     = $this->readForm();

        if ($errors = $this->validate($data)) {
            $this->bounceWithErrors($errors, $data, 'properties/' . $property['id'] . '/edit');
        }

        Property::update((int) $property['id'], (int) $user['id'], $data);
        AuditLog::record('property.update', 'property', (string) $property['id']);

        flash('success', t('property.updated'));
        redirect('properties/' . $property['id']);
    }

    /** POST + CSRF. The legacy delete was `?key=N` on a GET link. */
    public function destroy(string $id): void
    {
        $user     = Auth::user();
        $property = $this->mustFind((int) $id, (int) $user['id']);

        Property::delete((int) $property['id'], (int) $user['id']);
        AuditLog::record('property.delete', 'property', (string) $property['id'], [
            'deed_no' => $property['deed_no'],
            'seq'     => $property['seq'],
        ]);

        flash('success', t('property.deleted'));
        redirect('properties');
    }

    // --- Export --------------------------------------------------------------

    public function export(): void
    {
        $user = Auth::user();

        try {
            PlanLimit::assertCanExport($user);
        } catch (PlanLimitException $e) {
            flash('warning', $e->getMessage());
            redirect('plan');
            return;
        }

        $filters = $this->readFilters();
        // Exports the FILTERED set, not the whole table.
        $rows    = Property::searchAll((int) $user['id'], $filters, $filters['sort'], $filters['dir']);

        AuditLog::record('property.export', 'property', null, ['rows' => count($rows)]);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="property-records-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'wb');

        // UTF-8 BOM so Excel opens Bengali text correctly instead of mojibake.
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            t('property.seq'), t('property.deed_no'), t('property.deed_date'),
            t('property.dag_current'), t('property.dag_previous'),
            t('property.khatian_current'), t('property.khatian_previous'),
            t('property.old_owner'), t('property.new_owner'),
            t('property.mouja'), t('property.area'), t('property.notes'),
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['seq'], $row['deed_no'], $row['deed_date'],
                $row['dag_current'], $row['dag_previous'],
                $row['khatian_current'], $row['khatian_previous'],
                $row['old_owner'], $row['new_owner'],
                $row['mouja'], $row['area_cent'], $row['notes'],
            ]);
        }

        fclose($out);
        exit;
    }

    // --- Documents -----------------------------------------------------------

    public function uploadDocument(string $id): void
    {
        $user     = Auth::user();
        $property = $this->mustFind((int) $id, (int) $user['id']);

        try {
            PlanLimit::assertCanUpload($user);
        } catch (PlanLimitException $e) {
            flash('warning', $e->getMessage());
            redirect('properties/' . $property['id']);
            return;
        }

        // Exceeding post_max_size empties $_POST and $_FILES with no error, so
        // the only way to report it is to notice the body was too big.
        $postMax = self::bytesFromIni((string) ini_get('post_max_size'));
        if ($postMax > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $postMax) {
            flash('danger', t('document.post_too_large'));
            redirect('properties/' . $property['id']);
        }

        try {
            $docId = Document::store($_FILES['document'] ?? [], (int) $property['id'], (int) $user['id']);
            AuditLog::record('document.upload', 'document', (string) $docId, [
                'property_id' => $property['id'],
            ]);
            flash('success', t('document.uploaded'));
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
        }

        redirect('properties/' . $property['id']);
    }

    /**
     * Serve a document.
     *
     * The file is outside any servable path, so this is the only way to read
     * it — and ownership is checked before a single byte is sent. Staff may
     * read any document; a customer only their own.
     */
    public function downloadDocument(string $id): void
    {
        $document = Document::find((int) $id);
        if ($document === null) {
            throw new HttpException(404);
        }

        if (!Auth::isStaff() && (int) $document['user_id'] !== Auth::id()) {
            throw new HttpException(403);
        }

        $path = Document::absolutePath($document);
        if (!is_file($path)) {
            throw new HttpException(404);
        }

        header('Content-Type: ' . $document['mime']);
        header('Content-Length: ' . (string) filesize($path));
        // "inline" would let a crafted file render in the site's origin, so
        // everything is served as a download.
        header('Content-Disposition: attachment; filename="'
            . str_replace('"', '', $document['original_name']) . '"');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function deleteDocument(string $id): void
    {
        $document = Document::find((int) $id);
        if ($document === null || (int) $document['user_id'] !== Auth::id()) {
            throw new HttpException(404);
        }

        $propertyId = (int) $document['property_id'];
        Document::delete($document);
        AuditLog::record('document.delete', 'document', $id, ['property_id' => $propertyId]);

        flash('success', t('document.deleted'));
        redirect('properties/' . $propertyId);
    }

    // --- Helpers -------------------------------------------------------------

    private function mustFind(int $id, int $userId): array
    {
        $property = Property::findForUser($id, $userId);
        if ($property === null) {
            throw new HttpException(404, t('property.not_found'));
        }
        return $property;
    }

    /** Read the record form. Values are stored exactly as typed. */
    private function readForm(): array
    {
        return [
            'deed_no'          => mb_substr(post('deed_no'), 0, 40),
            'deed_date'        => post('deed_date'),
            'area_cent'        => post('area_cent'),
            'old_owner'        => mb_substr(post('old_owner'), 0, 120),
            'new_owner'        => mb_substr(post('new_owner'), 0, 120),
            'mouja'            => mb_substr(post('mouja'), 0, 100),
            'dag_current'      => mb_substr(post('dag_current'), 0, 255),
            'dag_previous'     => mb_substr(post('dag_previous'), 0, 255),
            'khatian_current'  => mb_substr(post('khatian_current'), 0, 255),
            'khatian_previous' => mb_substr(post('khatian_previous'), 0, 255),
            'notes'            => mb_substr(post('notes'), 0, 2000),
        ];
    }

    /** @return string[] translated error messages */
    private function validate(array $data): array
    {
        $errors = [];

        if ($data['deed_no'] === '') {
            $errors[] = t('valid.required', ['field' => t('property.deed_no')]);
        }
        if ($data['mouja'] === '') {
            $errors[] = t('valid.required', ['field' => t('property.mouja')]);
        }

        // Owner names must accept Bengali script — the legacy rule was
        // ASCII-only and rejected them outright.
        foreach (['old_owner' => 'property.old_owner', 'new_owner' => 'property.new_owner'] as $key => $label) {
            if ($data[$key] !== '' && !valid_name($data[$key])) {
                $errors[] = t('valid.name') . ' (' . t($label) . ')';
            }
        }

        foreach (['dag_current', 'dag_previous', 'khatian_current', 'khatian_previous'] as $key) {
            foreach (split_tokens($data[$key]) as $tokenValue) {
                if (!valid_identifier_token($tokenValue)) {
                    $errors[] = t('valid.token') . ' (' . t('property.' . $key) . ')';
                    break;
                }
            }
        }

        if ($data['area_cent'] !== '' && !is_numeric($data['area_cent'])) {
            $errors[] = t('valid.number') . ' (' . t('property.area') . ')';
        }

        if ($data['deed_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['deed_date'])) {
            $errors[] = t('valid.date');
        }

        return $errors;
    }

    private function bounceWithErrors(array $errors, array $data, string $path): void
    {
        flash_old($data);
        foreach ($errors as $error) {
            flash('danger', $error);
        }
        redirect($path);
    }

    /** Convert a php.ini shorthand size ("40M") to bytes. */
    private static function bytesFromIni(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit   = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        switch ($unit) {
            case 'g': return $number * 1024 * 1024 * 1024;
            case 'm': return $number * 1024 * 1024;
            case 'k': return $number * 1024;
            default:  return $number;
        }
    }
}
