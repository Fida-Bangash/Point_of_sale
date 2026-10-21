@extends('layouts.app')

@section('title', 'Dreams Pos Admin Template')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="page-wrapper">
				<div class="content">
					<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4>Warranty</h4>
								<h6>Manage your warranty</h6>
							</div>
						</div>
						<ul class="table-top-head">
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i data-feather="chevron-up" class="feather-chevron-up"></i></a>
							</li>
						</ul>
						<div class="page-btn">
							<a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-units"><i data-feather="plus-circle" class="me-2"></i> Add New Warranty</a>
						</div>
					</div>
					<!-- /product list -->
					<div class="card table-list-card">
						<div class="card-body">
							<div class="table-top">
								<div class="search-set">
									<div class="search-input">
										<a href="" class="btn btn-searchset"><i data-feather="search" class="feather-search"></i></a>
									</div>
								</div>
								<div class="search-path">
									<a class="btn btn-filter" id="filter_search">
										<i data-feather="filter" class="filter-icon"></i>
										<span><img src="{{ asset('assets/img/icons/closes.svg') }}" alt="img"></span>
									</a>
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
												<i data-feather="clock" class="info-img"></i>
												<select class="select">
													<option>Choose Duration</option>
													<option>3 Months</option>
													<option>6 Months</option>
													<option>1 Year</option>
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
										<div class="col-lg-3 col-sm-6 col-12 ms-auto">
											<div class="input-blocks">
												<a class="btn btn-filters ms-auto"> <i data-feather="search" class="feather-search"></i> Search </a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /Filter -->
							<div class="table-responsive">
								<table class="table  datanew">
									<thead>
										<tr>
											<th class="no-sort">
												<label class="checkboxs">
													<input type="checkbox" id="select-all">
													<span class="checkmarks"></span>
												</label>
											</th>
											<th>Name</th>
											<th>Description</th>
											<th>Duration</th>
											<th>Status</th>
											<th class="no-sort">Action</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>Express Warranty </td>
											<td><p class="description-para">Repairs or a replacement for a faulty product within a specified time period after it was purchased.</p></td>
											<td>3 Months</td>
											<td><span class="badge badge-linesuccess">Active</span></td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="confirm-text p-2" href="javascript:void(0);">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>Special warranty </td>
											<td><p class="description-para">Seller of the property(grantor) warrants only against anything that occurred during their physical ownership.</p></td>
											<td>6 Months</td>
											<td><span class="badge badge-linesuccess">Active</span></td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="confirm-text p-2" href="javascript:void(0);">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
												
											</td>
										</tr>
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>Implied Warranty </td>
											<td><p class="description-para">Assurances that a product is fit for the purpose that it is intended and that it is merchantable.</p></td>
											<td>1 Year</td>
											<td><span class="badge badge-linesuccess">Active</span></td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="confirm-text p-2" href="javascript:void(0);">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
												
											</td>
										</tr>
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
									<h4>Add Warrranty</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="{{ url('/warranty') }}">
									<div class="mb-3">
										<label class="form-label">Name</label>
										<input type="text" class="form-control">
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">Duration</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Periods</label>
												<select class="select">
													<option>Choose</option>
													<option>Month</option>
													<option>Year</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3 input-blocks">
												<label class="form-label">Description</label>
												<textarea class="form-control"></textarea>
											</div>
										</div>
									</div>
									
									<div class="mb-0">
										<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
											<span class="status-label">Status</span>
											<input type="checkbox" id="user2" class="check">
											<label for="user2" class="checktoggle"></label>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Create Warrenty</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		

		
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Warrranty</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="{{ url('/warranty') }}">
									<div class="mb-3">
										<label class="form-label">Name</label>
										<input type="text" class="form-control" value="Piece">
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">Duration</label>
												<input type="text" class="form-control" value="3">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Periods</label>
												<select class="select">
													<option>Month</option>
													<option>Year</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3 input-blocks">
												<label class="form-label">Description</label>
												<textarea class="form-control">Repairs or a replacement for a faulty product within a specified time period after it was purchased.</textarea>
											</div>
										</div>
									</div>
									<div class="mb-0">
										<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
											<span class="status-label">Status</span>
											<input type="checkbox" id="user3" class="check">
											<label for="user3" class="checktoggle"></label>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="sidebar-settings nav-toggle all" id="myDiv"  onclick="toggleClass()">
			<div class="sidebar-content sticky-sidebar-one" id="sect">


				<div class="sidebar-header">
					<div class="sidebar-theme-title">


						<h5>Theme Customizer</h5>
						<p>Customize & Preview in Real Time</p>
					</div>
					<div class="close-sidebar-icon d-flex">
						<a class="sidebar-refresh me-2" href="#"><i data-feather="refresh-ccw"></i></a>
						<a class="sidebar-close" href="#"><i data-feather="x"></i></a>
					</div>
				</div>
				<form action="#" method="post">
					<div class="sidebar-body">
						<div class="theme-mode">
							<div class="theme-head">
								<h6>Theme Mode</h6>
								<p>Enjoy Dark & Light modes.</p>
							</div>
							<div class="row">
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="theme-mode" id="light_mode"
													class="check color-check stylemode alldata" value="light_mode">
												<label for="light_mode" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-01.jpg') }}" alt="">
													<span class="theme-name">Light Mode</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="theme-mode" id="dark_mode"
													class="check color-check stylemode alldata" value="dark_mode"
													onclick="addClass()">
												<label for="dark_mode" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-02.jpg') }}" alt="">
													<span class="theme-name">Dark Mode</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>


						<div class="theme-mode">
							<div class="theme-head">
								<h6>Direction</h6>
								<p>Select the direction for your app.</p>
							</div>
							<div class="row">
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="direction" id="ltr" class="check direction"
													value="ltr">
												<label for="ltr" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-01.jpg') }}" alt="">
													<span class="theme-name">LTR</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="direction" id="rtl" class="check direction"
													value="rtl">
												<label for="rtl" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-03.jpg') }}" alt="">
													<span class="theme-name">RTL</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="theme-mode border-0 mb-0">
							<div class="theme-head">
								<h6>Layout Mode</h6>
								<p>Select the primary layout style for your app.</p>
							</div>
							<div class="row">
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="layout" id="default_layout"
													class="check layout-mode alldata" value="default">
												<label for="default_layout" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-01.jpg') }}" alt="">
													<span class="theme-name">Default</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="layout" id="box_layout"
													class="check layout-mode alldata" value="box">
												<label for="box_layout" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-07.jpg') }}" alt="">
													<span class="theme-name">Box</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="layout" id="collapse_layout"
													class="check layout-mode alldata" value="collapsed">
												<label for="collapse_layout" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-05.jpg') }}" alt="">
													<span class="theme-name">Collapsed</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="layout" id="horizontal_layout"
													class="check layout-mode alldata" value="horizontal">
												<label for="horizontal_layout" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-06.jpg') }}" alt="">
													<span class="theme-name">Horizontal</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-6 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="layout" id="modern_layout"
													class="check layout-mode alldata" value="modern">
												<label for="modern_layout" class="checktoggles">
													<img src="{{ asset('assets/img/theme/theme-img-04.jpg') }}" alt="">
													<span class="theme-name">Modern</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="theme-mode">
							<div class="theme-head">
								<h6>Navigation Colors</h6>
								<p>Setup the color for the Navigation</p>
							</div>
							<div class="row">
								<div class="col-xl-4 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="nav_color" id="light_color"
													class="check nav-color alldata" value="light">
												<label for="light_color" class="checktoggles">
													<span class="theme-name">Light</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-4 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="nav_color" id="grey_color"
													class="check nav-color alldata" value="grey">
												<label for="grey_color" class="checktoggles">
													<span class="theme-name">Grey</span>
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-4 ere">
									<div class="layout-wrap">
										<div class="d-flex align-items-center">
											<div class="status-toggle d-flex align-items-center me-2">
												<input type="radio" name="nav_color" id="dark_color"
													class="check nav-color alldata" value="dark">
												<label for="dark_color" class="checktoggles">
													<span class="theme-name">Dark</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="sidebar-footer">
						<div class="row">
							<div class="col-xl-6">
								<div class="footer-preview-btn">
									<a href="#" class="btn btn-primary w-100">Preview</a>
								</div>
							</div>
							<div class="col-xl-6">
								<div class="footer-reset-btn">
									<button  class="btn btn-secondary w-100"
									id="resetBtn">Reset</button>

								</div>
							</div>
						</div>
					</div>
				</form>
			</div>

		</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
@endpush
