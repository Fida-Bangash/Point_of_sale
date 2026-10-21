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
															<a href="javascript:void(0);" class="active subdrop"><i data-feather="settings"></i><span>General Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/general-settings') }}">Profile</a></li>
																<li><a href="{{ url('/security-settings') }}" class="active">Security</a></li>
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
																<li><a href="">SMS Gateways</a></li>
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
									<div class="setting-title">
										<h4>Security</h4>
									</div>
									<div class="security-settings">
										<ul>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="eye-off"></i></span>
													<div class="security-title">
														<h5>Password</h5>
														<p>Last Changed 22 July 2023, 10:30 AM</p>
													</div>
												</div>
												<div class="security-btn">
													<a href="javascript:void(0);" class="btn btn-primary">Change Password</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="shield"></i></span>
													<div class="security-title">
														<h5>Two Factor</h5>
														<p>Receive codes via SMS or email every time you login</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<a href="javascript:void(0);" class="btn btn-danger">Disable</a>
													<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
														<input type="checkbox" id="user3" class="check" checked>
														<label for="user3" class="checktoggle">	</label>
													</div>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="shield"></i></span>
													<div class="security-title">
														<h5>Google Authentication</h5>
														<p>Connect to Google</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<span class="badges-connected">Connected</span>
													<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
														<input type="checkbox" id="user4" class="check" checked>
														<label for="user4" class="checktoggle">	</label>
													</div>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="phone"></i></span>
													<div class="security-title">
														<h5>Phone Number Verification</h5>
														<p>Verified Mobile Number : +81699799974</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<span><i class=" fa fa-check-circle me-2"></i></span>
													<a href="javascript:void(0);" class="btn btn-primary">Change</a>
													<a href="javascript:void(0);" class="remove-red ms-2">Remove</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="mail"></i></span>
													<div class="security-title">
														<h5>Email Verification</h5>
														<p>Verified Email : info@example.com</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<span><i class=" fa fa-check-circle me-2"></i></span>
													<a href="javascript:void(0);" class="btn btn-primary">Change</a>
													<a href="javascript:void(0);" class="remove-red ms-2">Remove</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="tool"></i></span>
													<div class="security-title">
														<h5>Device Management</h5>
														<p>Last Changed 22 July 2023, 10:30 AM</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<a href="javascript:void(0);" class="manage-btn">Manage</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="box"></i></span>
													<div class="security-title">
														<h5>Account Activity</h5>
														<p>Last Changed 25 July 2023, 11:00 AM</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<a href="javascript:void(0);" class="manage-btn">View</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="slash"></i></span>
													<div class="security-title">
														<h5>Deactivate Account</h5>
														<p>Last Changed 21 July 2023, 09:37 AM</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<a href="javascript:void(0);" class="manage-btn">Deactivate</a>
												</div>
											</li>
											<li>
												<div class="security-type">
													<span class="security-icon"><i data-feather="trash-2"></i></span>
													<div class="security-title">
														<h5>Delete Account</h5>
														<p>Last Changed 26 July 2023, 11:40 AM</p>
													</div>
												</div>
												<div class="security-btn d-flex align-items-center">
													<a href="javascript:void(0);" class="btn btn-danger">Delete</a>
												</div>
											</li>
										</ul>
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
