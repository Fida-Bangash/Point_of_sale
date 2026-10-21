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
															<a href="javascript:void(0);"><i data-feather="archive"></i><span>App Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/invoice-settings') }}">Invoice</a></li>
																<li><a href="{{ url('/printer-settings') }}">Printer </a></li>
																<li><a href="{{ url('/pos-settings') }}">POS</a></li>
																<li><a href="{{ url('/custom-fields') }}">Custom Fields</a></li>
															</ul>
														</li>
														<li class="submenu">
															<a href="javascript:void(0);" class="active subdrop"><i data-feather="server"></i><span>System Settings</span><span class="menu-arrow"></span></a>
															<ul>
																<li><a href="{{ url('/email-settings') }}" class="active">Email</a></li>
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
									<div class="setting-title">
										<h4>Email Settings</h4>
									</div>
									<div class="page-header text-end justify-content-end">
										<a href="javascript:void(0);" class="btn-added btn-primary"><i data-feather="mail" class="me-2"></i>Send test email</a>
									</div>
									<div class="row">
										<div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
											<div class="connected-app-card email-setting d-flex w-100">
												<ul class="w-100">
													<li class="flex-column align-items-start">
														<div class="d-flex align-items-center justify-content-between w-100 mb-2">
															<div class="security-type d-flex align-items-center">
																<div class="security-title">
																	<h5>PHP Mailer</h5>
																</div>
															</div>
														</div>
														<p>Used to send emails safely and easily via PHP code from a web server.</p>
													</li>
													<li>
														<div class="integration-btn">
															<a href="" data-bs-toggle="modal" data-bs-target="#php-mail"><i data-feather="tool" class="me-2"></i>Connect</a>
														</div>
														<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
															<input type="checkbox" id="user1" class="check" checked>
															<label for="user1" class="checktoggle">	</label>
														</div>
													</li>
												</ul>
											</div>
										</div>
										<div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
											<div class="connected-app-card email-setting d-flex w-100">
												<ul class="w-100">
													<li class="flex-column align-items-start">
														<div class="d-flex align-items-center justify-content-between w-100 mb-2">
															<div class="security-type d-flex align-items-center">
																<div class="security-title">
																	<h5>SMTP</h5>
																</div>
															</div>
														</div>
														<p>SMTP is used to send, relay or forward messages from a mail client.</p>
													</li>
													<li>
														<div class="integration-btn">
															<a href="" data-bs-toggle="modal" data-bs-target="#smtp-mail"><i data-feather="tool" class="me-2"></i>Connect</a>
														</div>
														<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
															<input type="checkbox" id="user2" class="check" checked>
															<label for="user2" class="checktoggle">	</label>
														</div>
													</li>
												</ul>
											</div>
										</div>
										<div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
											<div class="connected-app-card email-setting d-flex w-100">
												<ul class="w-100">
													<li class="flex-column align-items-start">
														<div class="d-flex align-items-center justify-content-between w-100 mb-2">
															<div class="security-type d-flex align-items-center">
																<div class="security-title">
																	<h5>SendGrid </h5>
																</div>
															</div>
															<div class="connect-btn">
																<a href="javascript:void(0);">Connected</a>
															</div>
														</div>
														<p>Cloud-based email marketing tool that assists marketers and developers .</p>
													</li>
													<li>
														<div class="integration-btn">
															<a href="" data-bs-toggle="modal" data-bs-target="#test-mail"><i data-feather="tool" class="me-2"></i>View Integration</a>
														</div>
														<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
															<input type="checkbox" id="user3" class="check" checked>
															<label for="user3" class="checktoggle">	</label>
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
				</div>
			</div>

<div class="modal fade" id="php-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>PHP Mailer</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="{{ url('/email-settings') }}">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">From Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Email Password <span> *</span></label>
												<input type="password" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> From Email Name <span> *</span></label>
												<input type="text" class="form-control">
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
		

		
		<div class="modal fade" id="smtp-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>SMTP</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user5" class="check" checked>
									<label for="user5" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="{{ url('/email-settings') }}">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">From Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Email Password <span> *</span></label>
												<input type="password" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label"> Email Host <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Port <span> *</span></label>
												<input type="text" class="form-control">
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
		

		
		<div class="modal fade" id="test-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Test Mail</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user6" class="check" checked>
									<label for="user6" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="{{ url('/email-settings') }}">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Enter Email Address <span> *</span></label>
												<input type="email" class="form-control">
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
