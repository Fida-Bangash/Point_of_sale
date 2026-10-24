@extends('layouts.app')

@section('title', 'Users')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
<style>
    /* ---- Summary cards ---- */
    .user-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        border: 1px solid #edf0f4;
        border-radius: 12px;
        background: #fff;
        height: 100%;
    }
    .user-stat .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-stat .stat-icon svg { width: 22px; height: 22px; }
    .user-stat h3 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.1; }
    .user-stat p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
    .stat-total   { background: #fff4e6; color: #ff9f43; }
    .stat-active  { background: #e8f7ee; color: #28c76f; }
    .stat-inactive{ background: #fdeaea; color: #ea5455; }
    .stat-admin   { background: #eceafd; color: #7367f0; }

    /* ---- Table ---- */
    .user-cell { display: flex; align-items: center; gap: 12px; }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff4e6;
        color: #ff9f43;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-cell .user-name { font-weight: 600; color: #1b2230; line-height: 1.2; }
    .user-cell .user-email { color: #6b7280; font-size: 13px; }
    .you-tag {
        font-size: 11px;
        font-weight: 600;
        color: #ff9f43;
        background: #fff4e6;
        border-radius: 20px;
        padding: 1px 8px;
        margin-left: 6px;
        vertical-align: middle;
    }

    .role-badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .role-admin   { background: #eceafd; color: #7367f0; }
    .role-manager { background: #e1f3fc; color: #00a2e8; }
    .role-cashier { background: #e8f7ee; color: #28c76f; }
    .role-stock   { background: #fff4e6; color: #ff9f43; }

    /* Edit icon is centered under the "Action" header */
    .user-actions { display: flex; justify-content: center; width: 100%; }

    /* ---- Alerts ---- */
    .page-alert { border-radius: 10px; display: flex; align-items: center; gap: 10px; }
    .page-alert svg { width: 18px; height: 18px; flex-shrink: 0; }
    .page-alert ul { margin: 0; padding-left: 18px; }

    /* ---- Modals ---- */
    .role-help {
        background: #f8f9fb;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 16px;
    }
    .role-help strong { color: #1b2230; }
    .field-note { color: #6b7280; font-size: 12px; margin-top: 4px; display: block; }
    .required-star { color: #ea5455; }
    .modal-body .alert { border-radius: 10px; font-size: 14px; }
    .modal-body .alert ul { margin: 0; padding-left: 18px; }
</style>
@endpush

@section('content')
@php
    $totalUsers    = $users->count();
    $activeUsers   = $users->where('is_active', true)->count();
    $inactiveUsers = $totalUsers - $activeUsers;
    $adminUsers    = $users->where('role', 'admin')->count();
    $me            = auth()->user();
@endphp

<div class="page-wrapper">
    <div class="content">

        {{-- Flash message (success) --}}
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
                <i data-feather="check-circle"></i>
                <span>{{ session('status') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Errors that do not belong to a modal form (e.g. changing your own role) --}}
        @if($errors->any() && ! old('_form'))
            <div class="alert alert-danger alert-dismissible fade show page-alert" role="alert">
                <i data-feather="alert-circle"></i>
                <span>{{ $errors->first() }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Users</h4>
                    <h6>Manage who can sign in to the POS and what they can do</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a href="{{ url('/users') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
                </li>
                <li>
                    <a href="javascript:void(0);" onclick="window.print()" data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i data-feather="chevron-up" class="feather-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-user">
                    <i data-feather="plus-circle" class="me-2"></i>Add New User
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="user-stat">
                    <div class="stat-icon stat-total"><i data-feather="users"></i></div>
                    <div><h3>{{ $totalUsers }}</h3><p>Total users</p></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="user-stat">
                    <div class="stat-icon stat-active"><i data-feather="user-check"></i></div>
                    <div><h3>{{ $activeUsers }}</h3><p>Active</p></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="user-stat">
                    <div class="stat-icon stat-inactive"><i data-feather="user-x"></i></div>
                    <div><h3>{{ $inactiveUsers }}</h3><p>Inactive</p></div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="user-stat">
                    <div class="stat-icon stat-admin"><i data-feather="shield"></i></div>
                    <div><h3>{{ $adminUsers }}</h3><p>Admins</p></div>
                </div>
            </div>
        </div>

        {{-- Users table --}}
        <div class="card table-list-card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-input">
                            <a href="" class="btn btn-searchset"><i data-feather="search" class="feather-search"></i></a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th class="no-sort text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="user-name">
                                                {{ $user->name }}
                                                @if($user->is($me))<span class="you-tag">You</span>@endif
                                            </div>
                                            <div class="user-email">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->phone ?: '—' }}</td>
                                <td><span class="role-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->created_at?->format('d M Y') }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-linesuccess">Active</span>
                                    @else
                                        <span class="badge badge-linedanger">Inactive</span>
                                    @endif
                                </td>
                                <td class="action-table-data text-center">
                                    <div class="edit-delete-action user-actions">
                                        <a class="p-0 mb-0" data-bs-toggle="modal"
                                           data-bs-target="#edit-user-{{ $user->id }}" title="Edit user">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ===================== Add user modal ===================== --}}
@php $addOld = old('_form') === 'add-user'; @endphp
<div class="modal fade" id="add-user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
        <div class="modal-content">
            <div class="page-wrapper-new p-0">
                <div class="content">
                    <div class="modal-header border-0 custom-modal-header">
                        <div class="page-title"><h4>Add User</h4></div>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">

                        @if($addOld && $errors->any())
                            <div class="alert alert-danger">
                                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            <input type="hidden" name="_form" value="add-user">

                            <div class="role-help">
                                <strong>Admin</strong> – everything &nbsp;·&nbsp;
                                <strong>Manager</strong> – sales, stock and reports &nbsp;·&nbsp;
                                <strong>Cashier</strong> – POS and customers &nbsp;·&nbsp;
                                <strong>Stock</strong> – products, stock and purchases
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Full name <span class="required-star">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="e.g. Ahmed Khan"
                                               value="{{ $addOld ? old('name') : '' }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control js-phone" placeholder="03XXXXXXXXX"
                                               maxlength="11" inputmode="numeric" pattern="03[0-9]{9}"
                                               title="11 digits, starting with 03"
                                               value="{{ $addOld ? old('phone') : '' }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Email <span class="required-star">*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="you@example.com"
                                               value="{{ $addOld ? old('email') : '' }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Role <span class="required-star">*</span></label>
                                        <select class="select" name="role" required>
                                            <option value="">Choose role</option>
                                            <option value="manager" @selected($addOld && old('role') === 'manager')>Manager</option>
                                            <option value="admin"   @selected($addOld && old('role') === 'admin')>Admin</option>
                                            <option value="cashier" @selected($addOld && old('role') === 'cashier')>Cashier</option>
                                            <option value="stock"   @selected($addOld && old('role') === 'stock')>Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Password <span class="required-star">*</span></label>
                                        <div class="pass-group">
                                            <input type="password" name="password" class="pass-input" minlength="8"
                                                   autocomplete="new-password" required>
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                        <span class="field-note">At least 8 characters.</span>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Confirm password <span class="required-star">*</span></label>
                                        <div class="pass-group">
                                            <input type="password" name="password_confirmation" class="pass-input"
                                                   autocomplete="new-password" required>
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer-btn">
                                <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-submit">Create user</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Edit user modals (one per user) ===================== --}}
@foreach($users as $user)
@php
    $formId   = 'edit-user-' . $user->id;
    $isOld    = old('_form') === $formId;
    $vName    = $isOld ? old('name')  : $user->name;
    $vPhone   = $isOld ? old('phone') : $user->phone;
    $vEmail   = $isOld ? old('email') : $user->email;
    $vRole    = $isOld ? old('role')  : $user->role;
    $vActive  = $isOld ? (string) old('is_active') : ($user->is_active ? '1' : '0');
    $isMe     = $user->is($me);
@endphp
<div class="modal fade" id="{{ $formId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal-two">
        <div class="modal-content">
            <div class="page-wrapper-new p-0">
                <div class="content">
                    <div class="modal-header border-0 custom-modal-header">
                        <div class="page-title"><h4>Edit User</h4></div>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">

                        @if($isOld && $errors->any())
                            <div class="alert alert-danger">
                                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="{{ $formId }}">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Full name <span class="required-star">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $vName }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control js-phone" placeholder="03XXXXXXXXX"
                                               maxlength="11" inputmode="numeric" pattern="03[0-9]{9}"
                                               title="11 digits, starting with 03" value="{{ $vPhone }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Email <span class="required-star">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ $vEmail }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Role <span class="required-star">*</span></label>
                                        <select class="select" name="role" required>
                                            <option value="manager" @selected($vRole === 'manager')>Manager</option>
                                            <option value="admin"   @selected($vRole === 'admin')>Admin</option>
                                            <option value="cashier" @selected($vRole === 'cashier')>Cashier</option>
                                            <option value="stock"   @selected($vRole === 'stock')>Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Status</label>
                                        <select class="select" name="is_active">
                                            <option value="1" @selected($vActive === '1')>Active</option>
                                            <option value="0" @selected($vActive === '0')>Inactive</option>
                                        </select>
                                        @if($isMe)
                                            <span class="field-note">You cannot change your own role or deactivate your own account.</span>
                                        @else
                                            <span class="field-note">Inactive users cannot sign in.</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6"></div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>New password</label>
                                        <div class="pass-group">
                                            <input type="password" name="password" class="pass-input" minlength="8"
                                                   autocomplete="new-password">
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Confirm new password</label>
                                        <div class="pass-group">
                                            <input type="password" name="password_confirmation" class="pass-input"
                                                   autocomplete="new-password">
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer-btn">
                                <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-submit">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Phone fields: digits only, max 11
        document.querySelectorAll('.js-phone').forEach(function (input) {
            input.addEventListener('input', function () {
                input.value = input.value.replace(/\D/g, '').slice(0, 11);
            });
        });

        @if($errors->any() && old('_form'))
        // Re-open the modal whose form failed validation
        var failedModal = document.getElementById(@json(old('_form')));
        if (failedModal && window.bootstrap) {
            new bootstrap.Modal(failedModal).show();
        }
        @endif
    });
</script>
@endpush