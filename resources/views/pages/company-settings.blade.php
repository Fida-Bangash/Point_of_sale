@extends('layouts.app')

@section('title', 'Dreams Pos Admin Template')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="page-wrapper">
				<div class="content settings-content">
					<div class="page-header settings-pg-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4>Settings</h4>
								<h6>Manage your settings on portal</h6>
							</div>
						</div>
						<ul class="table-top-head">
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i data-feather="chevron-up" class="feather-chevron-up"></i></a>
							</li>
						</ul>
					</div>
					<div class="row">
						<div class="col-xl-12">
							<div class="settings-wrapper d-flex">
								<div class="sidebars settings-sidebar theiaStickySidebar" id="sidebar2">
									<div class="sidebar-inner slimscroll">
										<div id="sidebar-menu5" class="sidebar-menu">
											<ul>
												<li class="submenu-open">
													<ul>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="settings"></i><span>General Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/notification') }}">Notifications</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);" class="active subdrop"><i data-feather="airplay"></i><span>Website Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li class="active"><a href="{{ url('/company-settings') }}">Company Settings </a></li>
																<li><a href="{{ url('/prefixes') }}">Prefixes</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="archive"></i><span>App Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/invoice-settings') }}">Invoice</a></li>
																<li><a href="{{ url('/printer-settings') }}">Printer </a></li>
																<li><a href="{{ url('/pos-settings') }}">POS</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="credit-card"></i><span>Financial Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/tax-rates') }}">Tax Rates</a></li>
																<li><a href="{{ url('/currency-settings') }}">Currencies</a></li>
															</ul>
														</li>
														
													</ul>								
												</li>
												
											</ul>
										</div>
									</div>
								</div>
								<div class="settings-page-wrap">
	@if(session('status'))
	<div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
		<i data-feather="check-circle"></i>
		<span>{{ session('status') }}</span>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
	@endif

	@if($errors->any())
	<div class="alert alert-danger" role="alert">
		<strong>Please fix the following:</strong>
		<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
	</div>
	@endif

	<form method="POST" action="{{ route('company-settings.update') }}" enctype="multipart/form-data">
		@csrf
		@method('PUT')

		<div class="setting-title">
			<h4>Company Settings</h4>
		</div>

		@php
			$info = [
				['shop_name',  'Shop Name',               'text',  true,  'col-xl-4 col-lg-6 col-md-4'],
				['phone',      'Phone Number',            'text',  false, 'col-xl-4 col-lg-6 col-md-4'],
				['email',      'Email Address',           'email', false, 'col-xl-4 col-lg-6 col-md-4'],
				['tax_number', 'NTN / STRN (Tax Number)', 'text',  false, 'col-xl-4 col-lg-6 col-md-4'],
			];
			$place = [
				['address', 'Address', 'text', false, 'col-md-12'],
				['city',    'City',    'text', false, 'col-xl-6 col-lg-6 col-md-6'],
				['country', 'Country', 'text', true,  'col-xl-6 col-lg-6 col-md-6'],
			];
			$money = [
				['currency_symbol', 'Currency Symbol (e.g. Rs)', 'text', true, 'col-md-6'],
				['currency_code',   'Currency Code (e.g. PKR)',  'text', true, 'col-md-6'],
			];
		@endphp

		<div class="company-info">
			<div class="card-title-head">
				<h6><span><i data-feather="zap"></i></span>Shop Information</h6>
			</div>
			<div class="row">
				@foreach($info as [$name, $label, $type, $required, $col])
				<div class="{{ $col }}">
					<div class="mb-3">
						<label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
						<input type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
							value="{{ old($name, $shop->$name) }}" @required($required)>
						@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				@endforeach
			</div>
		</div>

		<div class="company-info company-images">
			<div class="card-title-head">
				<h6><span><i data-feather="image"></i></span>Shop Logo</h6>
			</div>
			<ul class="logo-company">
				<li class="d-flex align-items-center">
					<div class="logo-info">
						<h6>Shop Logo</h6>
						<p>Header aur receipt mein dikhega</p>
					</div>
					<div class="profile-pic-upload mb-0">
						<div class="new-employee-field">
							<div class="mb-0">
								<div class="image-upload mb-0">
									<input type="file" name="logo" id="logo-input" accept="image/png,image/jpeg,image/webp">
									<div class="image-uploads">
										<h4><i data-feather="upload"></i>Upload Photo</h4>
									</div>
								</div>
								<span>PNG, JPG or WEBP. Max size 2MB.</span>
								@error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
					</div>
					<div class="new-logo ms-auto">
						<img id="logo-preview" src="{{ $shop->logoUrl() ?? asset('assets/img/logo-small.png') }}" alt="Logo" style="max-height:60px;width:auto;">
						@if($shop->logo)
						<label class="d-block mt-2 mb-0">
							<input type="checkbox" name="remove_logo" value="1" @checked(old('remove_logo'))> Remove logo
						</label>
						@endif
					</div>
				</li>
			</ul>
		</div>

		<div class="company-address">
			<div class="card-title-head">
				<h6><span><i data-feather="map-pin"></i></span>Address</h6>
			</div>
			<div class="row">
				@foreach($place as [$name, $label, $type, $required, $col])
				<div class="{{ $col }}">
					<div class="mb-3">
						<label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
						<input type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
							value="{{ old($name, $shop->$name) }}" @required($required)>
						@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				@endforeach
			</div>
		</div>

		<div class="company-info">
			<div class="card-title-head">
				<h6><span><i data-feather="dollar-sign"></i></span>Currency &amp; Receipt</h6>
			</div>
			<div class="row">
				@foreach($money as [$name, $label, $type, $required, $col])
				<div class="{{ $col }}">
					<div class="mb-3">
						<label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
						<input type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
							value="{{ old($name, $shop->$name) }}" @required($required)>
						@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				@endforeach
				<div class="col-md-12">
					<div class="mb-3">
						<label class="form-label">Receipt Footer Message</label>
						<textarea name="receipt_footer" rows="3" maxlength="500"
							class="form-control @error('receipt_footer') is-invalid @enderror">{{ old('receipt_footer', $shop->receipt_footer) }}</textarea>
						@error('receipt_footer')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
		</div>

		<div class="modal-footer-btn">
			<a href="{{ url('/') }}" class="btn btn-cancel me-2">Cancel</a>
			<button type="submit" class="btn btn-submit">Save Changes</button>
		</div>
	</form>
							</div>
							
						</div>
					</div>
				</div>
			</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
<script src="{{ asset('assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
@endpush

@push('scripts_after')
<script>
	document.getElementById('logo-input')?.addEventListener('change', function () {
		var f = this.files && this.files[0];
		if (f) document.getElementById('logo-preview').src = URL.createObjectURL(f);
	});
</script>
@endpush
