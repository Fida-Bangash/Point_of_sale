@extends('layouts.app')

@section('title', 'Dreams Pos Admin Template')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="page-wrapper cardhead">
                <div class="content container-fluid">
				
					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col-sm-12">
								<h3 class="page-title">Form Select</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
									<li class="breadcrumb-item active">Form Select</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Basics</h5>
								</div>
								<div class="card-body">
									<select class="form-select" aria-label="Default select example">
									  	<option selected>Open this select menu</option>
									 	 <option value="1">One</option>
									 	 <option value="2">Two</option>
									 	 <option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Sizing</h5>
								</div>
								<div class="card-body">
									<select class="form-select form-select-lg mb-3" aria-label=".form-select-lg example">
										  <option selected>Open this select menu</option>
										  <option value="1">One</option>
										  <option value="2">Two</option>
										  <option value="3">Three</option>
									</select>

									<select class="form-select form-select-sm" aria-label=".form-select-sm example">
										  <option selected>Open this select menu</option>
										  <option value="1">One</option>
										  <option value="2">Two</option>
										  <option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title">Multiple Select</h5>
								</div>
								<div class="card-body">
									<select class="form-select" multiple aria-label="multiple select example">
										<option selected>Open this select menu</option>
	  									<option value="1">One</option>
	  									<option value="2">Two</option>
	  									<option value="3">Three</option>
									</select>
								</div>
							</div>
						</div>					
						
					</div>
				
				</div>				
            </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/custom-select.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('scripts_after')
<script src="{{ asset('assets/js/custom-select2.js') }}"></script>
@endpush
