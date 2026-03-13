<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\SpreadsheetMemberImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    private const IMPORT_HEADERS = [
        'member_id',
        'name',
        'ktp_number',
        'address',
        'phone_number',
        'email',
        'join_date',
        'status',
    ];

    public function index(Request $request): View
    {
        $query = Member::query()->latest();
        $user = $request->user();

        if ($user->role?->slug === 'member' && $user->member_id) {
            $query->whereKey($user->member_id);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(fn ($q) => $q
                ->where('member_id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('ktp_number', 'like', "%{$search}%"));
        }

        return view('members.index', ['members' => $query->paginate(10)->withQueryString()]);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()->role?->slug === 'member', 403);

        $data = $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,csv,txt'],
        ]);

        $file = $data['import_file'];
        try {
            $rows = SpreadsheetMemberImport::read($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (RuntimeException $exception) {
            return redirect()->route('members.index')->withErrors([
                'import_file' => $exception->getMessage(),
            ]);
        }

        if ($rows === [] || count($rows) < 2) {
            return redirect()->route('members.index')->withErrors([
                'import_file' => __('app.member_import_empty'),
            ]);
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $rows[0]);

        if ($headers !== self::IMPORT_HEADERS) {
            return redirect()->route('members.index')->withErrors([
                'import_file' => __('app.member_import_invalid_header'),
            ]);
        }

        $successCount = 0;
        $failedRows = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            if ($this->rowIsBlank($row)) {
                continue;
            }

            $values = array_slice(array_pad($row, count(self::IMPORT_HEADERS), ''), 0, count(self::IMPORT_HEADERS));
            $payload = array_combine(self::IMPORT_HEADERS, $values);
            $payload['email'] = $payload['email'] !== '' ? $payload['email'] : null;
            $payload['status'] = strtolower(trim((string) $payload['status']));

            $validator = Validator::make($payload, [
                'member_id' => ['required', 'string', 'max:50', Rule::unique('members', 'member_id')],
                'name' => ['required', 'string', 'max:255'],
                'ktp_number' => ['required', 'string', 'max:30', Rule::unique('members', 'ktp_number')],
                'address' => ['required', 'string'],
                'phone_number' => ['required', 'string', 'max:30'],
                'email' => ['nullable', 'email'],
                'join_date' => ['required', 'date'],
                'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            ]);

            if ($validator->fails()) {
                $failedRows[] = [
                    'row' => $index + 2,
                    'message' => implode('; ', $validator->errors()->all()),
                ];

                continue;
            }

            Member::create($validator->validated());
            $successCount++;
        }

        return redirect()->route('members.index')->with('success', __('app.member_imported', ['count' => $successCount]))->with('import_errors', $failedRows);
    }

    public function importTemplate(): StreamedResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $headers = self::IMPORT_HEADERS;
        $sample = ['MBR-1001', 'Budi Santoso', '3174010101010001', 'Jl. Mawar No. 1', '081234567890', 'budi@example.com', now()->format('Y-m-d'), 'active'];

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 'member-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        return view('members.create', [
            'generatedMemberId' => Member::generateNextMemberId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->role?->slug === 'member', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ktp_number' => ['required', 'string', 'max:30', 'unique:members,ktp_number'],
            'address' => ['required', 'string'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'string'],
        ]);

        $data['member_id'] = $this->generateUniqueMemberId();
        Member::create($data);

        return redirect()->route('members.index')->with('success', __('app.member_created'));
    }

    public function show(Member $member): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $member->id, 403);

        return view('members.edit', compact('member'));
    }

    public function edit(Member $member): View
    {
        abort_if(auth()->user()->role?->slug === 'member' && auth()->user()->member_id !== $member->id, 403);

        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        abort_if($request->user()->role?->slug === 'member' && $request->user()->member_id !== $member->id, 403);

        $data = $request->validate([
            'member_id' => ['required', 'string', 'max:50', 'unique:members,member_id,'.$member->id],
            'name' => ['required', 'string', 'max:255'],
            'ktp_number' => ['required', 'string', 'max:30', 'unique:members,ktp_number,'.$member->id],
            'address' => ['required', 'string'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'string'],
        ]);

        $member->update($data);

        return redirect()->route('members.index')->with('success', __('app.member_updated'));
    }

    public function destroy(Member $member): RedirectResponse
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        $member->delete();

        return redirect()->route('members.index')->with('success', __('app.member_deleted'));
    }

    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function generateUniqueMemberId(): string
    {
        do {
            $memberId = Member::generateNextMemberId();
        } while (Member::where('member_id', $memberId)->exists());

        return $memberId;
    }
}
