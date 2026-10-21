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
															<a href="javascript:void(0);"><i data-feather="airplay"></i><span>Website Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/system-settings') }}">System Settings</a></li>
																<li><a href="{{ url('/company-settings') }}">Company Settings </a></li>
																<li><a href="{{ url('/localization-settings') }}">Localization</a></li>
																<li><a href="{{ url('/prefixes') }}">Prefixes</a></li>
																<li><a href="{{ url('/preference') }}">Preference</a></li>
																<li><a href="{{ url('/appearance') }}">Appearance</a></li>
																<li><a href="{{ url('/social-authentication') }}">Social Authentication</a></li>
																<li><a href="{{ url('/language-settings') }}">Language</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);" class="active subdrop"><i data-feather="archive"></i><span>App Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/invoice-settings') }}">Invoice</a></li>
																<li><a href="{{ url('/printer-settings') }}">Printer </a></li>
																<li><a href="{{ url('/pos-settings') }}" class="active">POS</a></li>
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
								<div class="settings-page-wrap">
									<form action="{{ url('/pos-settings') }}">
										<div class="setting-title">
											<h4>POS Settings</h4>
										</div>
										<div class="company-info border-0">
											<div class="localization-info">
												<div class="row align-items-center mb-sm-4">
													<div class="col-sm-4">
														<div class="setting-info">
															<h6>POS Printer</h6>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="localization-select">
															<select class="select">
																<option>A4</option>
																<option>A4</option>
																<option>A4</option>
															</select>
														</div>
													</div>
												</div>	
												<div class="row align-items-center mb-sm-4">
													<div class="col-sm-4">
														<div class="setting-info">
															<h6>Payment Method</h6>
														</div>
													</div>
													<div class="col-sm-8">
														<div class="localization-select pos-payment-method d-flex align-items-center mb-0 w-100">
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>COD
																</label>
															</div>
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>Cheque
																</label>
															</div>
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>Card
																</label>
															</div>
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>Paypal
																</label>
															</div>
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>Bank Transfer
																</label>
															</div>
															<div class="custom-control custom-checkbox">
																<label class="checkboxs mb-0 pb-0 line-height-1">
																	<input type="checkbox">
																	<span class="checkmarks"></span>Cash
																</label>
															</div>
														</div>
													</div>
												</div>	
												<div class="row align-items-center">
													<div class="col-sm-4">
														<div class="setting-info">
															<h6>Enable Sound Effect </h6>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="localization-select d-flex align-items-center">
															<div class="status-toggle modal-status d-flex justify-content-between align-items-center me-3">
																<input type="checkbox" id="user4" class="check" checked>
																<label for="user4" class="checktoggle"></label>
															</div>
														</div>
													</div>
												</div>									
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
