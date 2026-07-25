@extends('admin.layouts.admin')

@section('title', 'Users — Admin')
@section('page-title', 'Users')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email..." value="{{ request('search') }}" style="max-width: 280px;">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button>
        @if (request('search'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        @endif
    </form>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="text-muted">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                         style="width: 38px; height: 38px; font-size: .85rem; {{ $user->isAdmin() ? 'background: var(--sh-orange);' : 'background: #6c757d;' }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        @if ($user->profile && $user->profile->phone)
                                            <small class="text-muted">{{ $user->profile->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="badge bg-dark"><i class="bi bi-shield-fill-check me-1"></i>Admin</span>
                                @else
                                    <span class="badge bg-secondary">Customer</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium">{{ $user->orders_count }}</span>
                            </td>
                            <td><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">No users found.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">
    {{ $users->links() }}
</div>
@endsection