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
																<li><a href="{{ url('/general-settings') }}">Profile</a></li>
																<li><a href="{{ url('/security-settings') }}">Security</a></li>
																<li><a href="{{ url('/notification') }}">Notifications</a></li>
																<li><a href="{{ url('/connected-apps') }}">Connected Apps</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);" class="active subdrop"><i data-feather="airplay"></i><span>Website Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/system-settings') }}">System Settings</a></li>
																<li><a href="{{ url('/company-settings') }}">Company Settings </a></li>
																<li><a href="{{ url('/localization-settings') }}">Localization</a></li>
																<li><a href="{{ url('/prefixes') }}">Prefixes</a></li>
																<li><a href="{{ url('/preference') }}">Preference</a></li>
																<li><a href="{{ url('/appearance') }}">Appearance</a></li>
																<li><a href="{{ url('/social-authentication') }}">Social Authentication</a></li>
																<li><a href="{{ url('/language-settings') }}" class="active">Language</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="archive"></i><span>App Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/invoice-settings') }}">Invoice</a></li>
																<li><a href="{{ url('/printer-settings') }}">Printer </a></li>
																<li><a href="{{ url('/pos-settings') }}">POS</a></li>
																<li><a href="{{ url('/custom-fields') }}">Custom Fields</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="server"></i><span>System Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/email-settings') }}">Email</a></li>
																<li><a href="{{ url('/sms-gateway') }}">SMS Gateways</a></li>
																<li><a href="{{ url('/otp-settings') }}">OTP</a></li>
																<li><a href="{{ url('/gdpr-settings') }}">GDPR Cookies</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="credit-card"></i><span>Financial Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/payment-gateway-settings') }}">Payment Gateway</a></li>
																<li><a href="{{ url('/bank-settings-grid') }}">Bank Accounts </a></li>
																<li><a href="{{ url('/tax-rates') }}">Tax Rates</a></li>
																<li><a href="{{ url('/currency-settings') }}">Currencies</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);"><i data-feather="layout"></i><span>Other Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/storage-settings') }}">Storage</a></li>
																<li><a href="{{ url('/ban-ip-address') }}">Ban IP Address </a></li>
															</ul>
														</li>
													</ul>								
												</li>
												
											</ul>
										</div>
									</div>
								</div>
								<div class="settings-page-wrap w-50">
									<div class="setting-title">
										<h4>Language</h4>
									</div>
									<div class="page-header justify-content-end">
										<ul class="table-top-head me-auto">
											<li>
												<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a>
											</li>
											<li>
												<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a>
											</li>
											<li>
												<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
											</li>
										</ul>
										<div class="page-btn d-flex align-items-center ms-0">
											<div class="select-language">
												<select class="select">
													<option>Select Language</option>
													<option>English</option>
													<option>Arabic</option>
													<option>Chinese</option>
												</select>
											</div>
											<a href="javascript:void(0);" class="btn btn-added ms-3">Add Translation</a>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="card table-list-card">
												<div class="card-body">
													<div class="table-top">
														<div class="search-set">
															<div class="search-input">
																<a href="javascript:void(0);" class="btn btn-searchset"><i data-feather="search" class="feather-search"></i></a>
															</div>
														</div>
														<div class="search-path">
															<div class="d-flex align-items-center">
																<a class="btn btn-secondary" href="javascript:void(0);">
																	<i data-feather="filter" class="filter-icon"></i>
																	Import Sample
																</a>
															</div>
														</div>
													</div>
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
																	<th>Language</th>
																	<th>Code</th>
																	<th>RTL</th>
																	<th>Total</th>
																	<th>Done</th>
																	<th>Progress</th>
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
																	<td>
																		<div class="language-name d-flex align-items-center">
																			<img src="{{ asset('assets/img/icons/flag-01.svg') }}" class="me-2" alt="">
																			English
																		</div>
																	</td>
																	<td>
																		en
																	</td>
																	<td>
																		<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																			<input type="checkbox" id="user1" class="check" checked="">
																			<label for="user1" class="checktoggle"></label>
																		</div>						
																	</td>
																	<td>2145</td>
																	<td>1815</td>
																	<td>
																		<div class="position-relative">
																			<div class="progress attendance language-progress">																			
																				<div class="progress-bar progress-bar-warning" role="progressbar">
																					<span>80%</span>
																				</div>
																			</div>
																		</div>
																		
																	</td>
																	<td><span class="badge badge-linesuccess">Active</span></td>
																	<td class="action-table-data">
																		<div class="edit-delete-action language-action">
																			<a href="javascript:void(0);" class="me-2 language-import"><i data-feather="download" class="feather-download"></i></a>
																			<div class="custom-control custom-checkbox">
																				<label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
																					<input type="checkbox" checked>
																					<span class="checkmarks"></span>
																				</label>
																			</div>
																			<a href="{{ url('/language-settings-web') }}" class="btn btn-secondary me-2">Web</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">App</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">Admin</a>
																			<a class="confirm-text p-0" href="javascript:void(0);">
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
																	<td>
																		<div class="language-name d-flex align-items-center">
																			<img src="{{ asset('assets/img/icons/flag-02.svg') }}" class="me-2" alt="">
																			Arabic
																		</div>
																	</td>
																	<td>
																		Ar
																	</td>
																	<td>
																		<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																			<input type="checkbox" id="user2" class="check" checked="">
																			<label for="user2" class="checktoggle"></label>
																		</div>						
																	</td>
																	<td>2045</td>
																	<td>2045</td>
																	<td>
																		<div class="position-relative">
																			<div class="progress attendance language-progress">																			
																				<div class="progress-bar progress-bar-success" role="progressbar">
																					<span>100%</span>
																				</div>
																			</div>
																		</div>																	
																	</td>
																	<td><span class="badge badge-linedanger">Inactive</span></td>
																	<td class="action-table-data">
																		<div class="edit-delete-action language-action">
																			<a href="javascript:void(0);" class="me-2 language-import"><i data-feather="download" class="feather-download"></i></a>
																			<div class="custom-control custom-checkbox">
																				<label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																				</label>
																			</div>
																			<a href="{{ url('/language-settings-web') }}" class="btn btn-secondary me-2">Web</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">App</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">Admin</a>
																			<a class="confirm-text p-0" href="javascript:void(0);">
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
																	<td>
																		<div class="language-name d-flex align-items-center">
																			<img src="{{ asset('assets/img/icons/flag-03.svg') }}" class="me-2" alt="">
																			Chinese
																		</div>
																	</td>
																	<td>
																		zh
																	</td>
																	<td>
																		<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																			<input type="checkbox" id="user3" class="check" checked="">
																			<label for="user3" class="checktoggle"></label>
																		</div>						
																	</td>
																	<td>2245</td>
																	<td>295</td>
																	<td>
																		<div class="position-relative">
																			<div class="progress attendance language-progress">																			
																				<div class="progress-bar progress-bar-violet" role="progressbar">
																					<span>5%</span>
																				</div>
																			</div>
																		</div>
																		
																	</td>
																	<td><span class="badge badge-linesuccess">Active</span></td>
																	<td class="action-table-data">
																		<div class="edit-delete-action language-action">
																			<a href="javascript:void(0);" class="me-2 language-import"><i data-feather="download" class="feather-download"></i></a>
																			<div class="custom-control custom-checkbox">
																				<label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
																					<input type="checkbox" checked>
																					<span class="checkmarks"></span>
																				</label>
																			</div>
																			<a href="{{ url('/language-settings-web') }}" class="btn btn-secondary me-2">Web</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">App</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">Admin</a>
																			<a class="confirm-text p-0" href="javascript:void(0);">
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
																	<td>
																		<div class="language-name d-flex align-items-center">
																			<img src="{{ asset('assets/img/icons/flag-04.svg') }}" class="me-2" alt="">
																			Hindi
																		</div>
																	</td>
																	<td>
																		hi
																	</td>
																	<td>
																		<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																			<input type="checkbox" id="user4" class="check" checked="">
																			<label for="user4" class="checktoggle"></label>
																		</div>						
																	</td>
																	<td>2535</td>
																	<td>1145</td>
																	<td>
																		<div class="position-relative">
																			<div class="progress attendance language-progress">																			
																				<div class="progress-bar progress-bar-violet-two" role="progressbar">
																					<span>40%</span>
																				</div>
																			</div>
																		</div>
																		
																	</td>
																	<td><span class="badge badge-linesuccess">Active</span></td>
																	<td class="action-table-data">
																		<div class="edit-delete-action language-action">
																			<a href="javascript:void(0);" class="me-2 language-import"><i data-feather="download" class="feather-download"></i></a>
																			<div class="custom-control custom-checkbox">
																				<label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
																					<input type="checkbox" checked>
																					<span class="checkmarks"></span>
																				</label>
																			</div>
																			<a href="{{ url('/language-settings-web') }}" class="btn btn-secondary me-2">Web</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">App</a>
																			<a href="javascript:void(0);" class="btn btn-secondary me-2">Admin</a>
																			<a class="confirm-text p-0" href="javascript:void(0);">
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
										</div>
									</div>
								</div>
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
