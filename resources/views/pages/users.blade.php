@extends('layouts.app')

@section('title', 'Dreams Pos Admin Template')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">
@endpush

@section('content')
<div class="page-wrapper">
	<div class="content">
		@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>User List</h4>
					<h6>Manage Your Users</h6>
				</div>
			</div>
			<ul class="table-top-head">
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img
							src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a>
				</li>
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img
							src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a>
				</li>
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer"
							class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw"
							class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i
							data-feather="chevron-up" class="feather-chevron-up"></i></a>
				</li>
			</ul>
			<div class="page-btn">
				<a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-units"><i
						data-feather="plus-circle" class="me-2"></i>Add New User</a>
			</div>
		</div>


		<!-- /product list -->
		<div class="card table-list-card">
			<div class="card-body">
				<div class="table-top">
					<div class="search-set">
						<div class="search-input">
							<a href="" class="btn btn-searchset"><i data-feather="search"
									class="feather-search"></i></a>
						</div>
					</div>
					<div class="search-path">
						<div class="d-flex align-items-center">
							<a class="btn btn-filter" id="filter_search">
								<i data-feather="filter" class="filter-icon"></i>
								<span><img src="{{ asset('assets/img/icons/closes.svg') }}" alt="img"></span>
							</a>
						</div>
					</div>
					<div class="form-sort">
						<i data-feather="sliders" class="info-img"></i>
						<select class="select">
							<option>Sort by Date</option>
							<option>Newest</option>
							<option>Oldest</option>
						</select>
					</div>
				</div>
				<!-- /Filter -->
				<div class="card" id="filter_inputs">
					<div class="card-body pb-0">
						<div class="row">
							<div class="col-lg-3 col-sm-6 col-12">
								<div class="input-blocks">
									<i data-feather="user" class="info-img"></i>
									<select class="select">
										<option>Choose Name</option>
										<option>Lilly</option>
										<option>Benjamin</option>
									</select>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 col-12">
								<div class="input-blocks">
									<i data-feather="stop-circle" class="info-img"></i>
									<select class="select">
										<option>Choose Status</option>
										<option>Active</option>
										<option>Inactive</option>
									</select>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 col-12">
								<div class="input-blocks">
									<i data-feather="zap" class="info-img"></i>
									<select class="select">
										<option>Choose Role</option>
										<option>Store Keeper</option>
										<option>Salesman</option>
									</select>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 col-12">
								<div class="input-blocks">
									<a class="btn btn-filters ms-auto"> <i data-feather="search"
											class="feather-search"></i> Search </a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Filter -->
				<div class="table-responsive">
					<table class="table datanew">
						<thead>
							<tr>
								<th class="no-sort">
									<label class="checkboxs">
										<input type="checkbox" id="select-all">
										<span class="checkmarks"></span>
									</label>
								</th>
								<th>User Name</th>
								<th>Phone</th>
								<th>email</th>
								<th>Role</th>
								<th>Created On</th>
								<th>Status</th>
								<th class="no-sort">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($users as $user)
							<tr>
								<td>
									<label class="checkboxs">
										<input type="checkbox">
										<span class="checkmarks"></span>
									</label>
								</td>
								<td>{{ $user->name }}</td>
								<td>{{ $user->phone }}</td>
								<td>{{ $user->email }}</td>
								<td>{{ ucfirst($user->role) }}</td>
								<td>{{ $user->created_at->format('d M Y') }}</td>
								<td>
									@if($user->is_active)
									<span class="badge badge-linesuccess">Active</span>
									@else
									<span class="badge badge-linedanger">Inactive</span>
									@endif
								</td>
								<td class="action-table-data">
									<div class="edit-delete-action">
										<a class="me-2 p-2 mb-0" data-bs-toggle="modal"
											data-bs-target="#edit-user-{{ $user->id }}">
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
		<!-- /product list -->
	</div>
</div>

<div class="modal fade" id="add-units">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title">
							<h4>Add User</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body custom-modal-body">
						<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="row">
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>User Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>Role</label>
                <select class="select" name="role">
                    <option value="">Choose</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="stock">Stock</option>
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>Password</label>
                <div class="pass-group">
                    <input type="password" name="password" class="pass-input">
                    <span class="fas toggle-password fa-eye-slash"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-blocks">
                <label>Confirm Password</label>
                <div class="pass-group">
                    <input type="password" name="password_confirmation" class="pass-input">
                    <span class="fas toggle-password fa-eye-slash"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer-btn">
        <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-submit">Submit</button>
    </div>
</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



@foreach($users as $user)
<div class="modal fade" id="edit-user-{{ $user->id }}">
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
                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>User Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Role</label>
                                        <select class="select" name="role">
                                            <option value="manager" @selected($user->role === 'manager')>Manager</option>
                                            <option value="admin"   @selected($user->role === 'admin')>Admin</option>
                                            <option value="cashier" @selected($user->role === 'cashier')>Cashier</option>
                                            <option value="stock"   @selected($user->role === 'stock')>Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Status</label>
                                        <select class="select" name="is_active">
                                            <option value="1" @selected($user->is_active)>Active</option>
                                            <option value="0" @selected(!$user->is_active)>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>New Password (optional)</label>
                                        <div class="pass-group">
                                            <input type="password" name="password" class="pass-input" placeholder="Leave blank to keep the current password">
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Confirm Password</label>
                                        <div class="pass-group">
                                            <input type="password" name="password_confirmation" class="pass-input">
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer-btn">
                                <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-submit">Submit</button>
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
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
@endpush