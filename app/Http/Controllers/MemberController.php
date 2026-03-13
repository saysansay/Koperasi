<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
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

    public function create(): View
    {
        abort_if(auth()->user()->role?->slug === 'member', 403);

        return view('members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->role?->slug === 'member', 403);

        $data = $request->validate([
            'member_id' => ['required', 'string', 'max:50', 'unique:members,member_id'],
            'name' => ['required', 'string', 'max:255'],
            'ktp_number' => ['required', 'string', 'max:30', 'unique:members,ktp_number'],
            'address' => ['required', 'string'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'string'],
        ]);

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
}
