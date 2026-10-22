{{-- Main, collapsed and horizontal sidebars --}}
<div class="sidebar" id="sidebar">
			<div class="sidebar-inner slimscroll">
				<div id="sidebar-menu" class="sidebar-menu">
					<ul>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Main</h6>
							<ul>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="grid"></i><span>Dashboard</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Admin Dashboard</a></li>
										<li><a href="{{ url('/sales-dashboard') }}" class="{{ request()->is('sales-dashboard') ? 'active' : '' }}">Sales Dashboard</a></li>
									</ul>
								</li>
								<!-- <li class="submenu">
									<a href="javascript:void(0);"><i data-feather="smartphone"></i><span>Application</span><span class="menu-arrow"></span></a>
									<ul>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Call<span class="menu-arrow inside-submenu"></span></a>
											<ul>
											</ul>
										</li>
										<li><a href="{{ url('/email') }}" class="{{ request()->is('email') ? 'active' : '' }}">Email</a></li>
									</ul>
								</li> -->
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Inventory</h6>
							<ul>
								<li><a href="{{ url('/product-list') }}" class="{{ request()->is('product-list') ? 'active' : '' }}"><i data-feather="box"></i><span>Products</span></a></li>
								<li><a href="{{ url('/add-product') }}" class="{{ request()->is('add-product') ? 'active' : '' }}"><i data-feather="plus-square"></i><span>Create Product</span></a></li>
								<li><a href="{{ url('/expired-products') }}" class="{{ request()->is('expired-products') ? 'active' : '' }}"><i data-feather="codesandbox"></i><span>Expired Products</span></a></li>
								<li><a href="{{ url('/low-stocks') }}" class="{{ request()->is('low-stocks') ? 'active' : '' }}"><i data-feather="trending-down"></i><span>Low Stocks</span></a></li>
								<li><a href="{{ url('/category-list') }}" class="{{ request()->is('category-list') ? 'active' : '' }}"><i data-feather="codepen"></i><span>Category</span></a></li>
								<li><a href="{{ url('/sub-categories') }}" class="{{ request()->is('sub-categories') ? 'active' : '' }}"><i data-feather="speaker"></i><span>Sub Category</span></a></li>
								<li><a href="{{ url('/brand-list') }}" class="{{ request()->is('brand-list') ? 'active' : '' }}"><i data-feather="tag"></i><span>Brands</span></a></li>
								<li><a href="{{ url('/units') }}" class="{{ request()->is('units') ? 'active' : '' }}"><i data-feather="speaker"></i><span>Units</span></a></li>
								<li><a href="{{ url('/varriant-attributes') }}" class="{{ request()->is('varriant-attributes') ? 'active' : '' }}"><i data-feather="layers"></i><span>Variant Attributes</span></a></li>
								<li><a href="{{ url('/warranty') }}" class="{{ request()->is('warranty') ? 'active' : '' }}"><i data-feather="bookmark"></i><span>Warranties</span></a></li>
								<li><a href="{{ url('/barcode') }}" class="{{ request()->is('barcode') ? 'active' : '' }}"><i data-feather="align-justify"></i><span>Print Barcode</span></a></li>
								<li><a href="{{ url('/qrcode') }}" class="{{ request()->is('qrcode') ? 'active' : '' }}"><i data-feather="maximize"></i><span>Print QR Code</span></a></li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Stock</h6>
							<ul>
								<li><a href="{{ url('/manage-stocks') }}" class="{{ request()->is('manage-stocks') ? 'active' : '' }}"><i data-feather="package"></i><span>Manage Stock</span></a></li>
								<li><a href="{{ url('/stock-adjustment') }}" class="{{ request()->is('stock-adjustment') ? 'active' : '' }}"><i data-feather="clipboard"></i><span>Stock Adjustment</span></a></li>
								<li><a href="{{ url('/stock-transfer') }}" class="{{ request()->is('stock-transfer') ? 'active' : '' }}"><i data-feather="truck"></i><span>Stock Transfer</span></a></li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Sales</h6>
							<ul>
								<li><a href="{{ url('/sales-list') }}" class="{{ request()->is('sales-list') ? 'active' : '' }}"><i data-feather="shopping-cart"></i><span>Sales</span></a></li>
								<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><i data-feather="file-text"></i><span>Invoices</span></a></li>
								<li><a href="{{ url('/sales-returns') }}" class="{{ request()->is('sales-returns') ? 'active' : '' }}"><i data-feather="copy"></i><span>Sales Return</span></a></li>
								<li><a href="{{ url('/quotation-list') }}" class="{{ request()->is('quotation-list') ? 'active' : '' }}"><i data-feather="save"></i><span>Quotation</span></a></li>
								<li><a href="{{ url('/pos') }}" class="{{ request()->is('pos') ? 'active' : '' }}"><i data-feather="hard-drive"></i><span>POS</span></a></li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Promo</h6>
							<ul>
								<li><a href="{{ url('/coupons') }}" class="{{ request()->is('coupons') ? 'active' : '' }}"><i data-feather="shopping-cart"></i><span>Coupons</span></a></li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Purchases</h6>
							<ul>
								<li><a href="{{ url('/purchase-list') }}" class="{{ request()->is('purchase-list') ? 'active' : '' }}"><i data-feather="shopping-bag"></i><span>Purchases</span></a></li>
								<li><a href="{{ url('/purchase-order-report') }}" class="{{ request()->is('purchase-order-report') ? 'active' : '' }}"><i data-feather="file-minus"></i><span>Purchase Order</span></a></li>
								<li><a href="{{ url('/purchase-returns') }}" class="{{ request()->is('purchase-returns') ? 'active' : '' }}"><i data-feather="refresh-cw"></i><span>Purchase Return</span></a></li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Finance & Accounts</h6>
							<ul>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="file-text"></i><span>Expenses</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/expense-list') }}" class="{{ request()->is('expense-list') ? 'active' : '' }}">Expenses</a></li>
										<li><a href="{{ url('/expense-category') }}" class="{{ request()->is('expense-category') ? 'active' : '' }}">Expense Category</a></li>
									</ul>
								</li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Peoples</h6>
							<ul>
								<li><a href="{{ url('/customers') }}" class="{{ request()->is('customers') ? 'active' : '' }}"><i data-feather="user"></i><span>Customers</span></a></li>
								<li><a href="{{ url('/suppliers') }}" class="{{ request()->is('suppliers') ? 'active' : '' }}"><i data-feather="users"></i><span>Suppliers</span></a></li>
								<li><a href="{{ url('/store-list') }}" class="{{ request()->is('store-list') ? 'active' : '' }}"><i data-feather="home"></i><span>Stores</span></a></li>
								<li><a href="{{ url('/warehouse') }}" class="{{ request()->is('warehouse') ? 'active' : '' }}"><i data-feather="archive"></i><span>Warehouses</span></a>
								</li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">HRM</h6>
							<ul>
								<li><a href="{{ url('/employees-grid') }}" class="{{ request()->is('employees-grid') ? 'active' : '' }}"><i data-feather="user"></i><span>Employees</span></a></li>
								<li><a href="{{ url('/department-grid') }}" class="{{ request()->is('department-grid') ? 'active' : '' }}"><i data-feather="users"></i><span>Departments</span></a></li>
								<li><a href="{{ url('/designation') }}" class="{{ request()->is('designation') ? 'active' : '' }}"><i data-feather="git-merge"></i><span>Designation</span></a></li>
								<li><a href="{{ url('/shift') }}" class="{{ request()->is('shift') ? 'active' : '' }}"><i data-feather="shuffle"></i><span>Shifts</span></a></li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="book-open"></i><span>Attendence</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/attendance-employee') }}" class="{{ request()->is('attendance-employee') ? 'active' : '' }}">Employee</a></li>
										<li><a href="{{ url('/attendance-admin') }}" class="{{ request()->is('attendance-admin') ? 'active' : '' }}">Admin</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="calendar"></i><span>Leaves</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/leaves-admin') }}" class="{{ request()->is('leaves-admin') ? 'active' : '' }}">Admin Leaves</a></li>
										<li><a href="{{ url('/leaves-employee') }}" class="{{ request()->is('leaves-employee') ? 'active' : '' }}">Employee Leaves</a></li>
										<li><a href="{{ url('/leave-types') }}" class="{{ request()->is('leave-types') ? 'active' : '' }}">Leave Types</a></li>
									</ul>
								</li>
								<li><a href="{{ url('/holidays') }}" class="{{ request()->is('holidays') ? 'active' : '' }}"><i data-feather="credit-card"></i><span>Holidays</span></a>
								</li>
								<li class="submenu">
									<a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}"><i data-feather="dollar-sign"></i><span>Payroll</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}">Employee Salary</a></li>
										<li><a href="{{ url('/payslip') }}" class="{{ request()->is('payslip') ? 'active' : '' }}">Payslip</a></li>
									</ul>
								</li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Reports</h6>
							<ul>
								<li><a href="{{ url('/sales-report') }}" class="{{ request()->is('sales-report') ? 'active' : '' }}"><i data-feather="bar-chart-2"></i><span>Sales Report</span></a></li>
								<li><a href="{{ url('/purchase-report') }}" class="{{ request()->is('purchase-report') ? 'active' : '' }}"><i data-feather="pie-chart"></i><span>Purchase report</span></a></li>
								<li><a href="{{ url('/inventory-report') }}" class="{{ request()->is('inventory-report') ? 'active' : '' }}"><i data-feather="inbox"></i><span>Inventory Report</span></a></li>
								<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><i data-feather="file"></i><span>Invoice Report</span></a></li>
								<li><a href="{{ url('/supplier-report') }}" class="{{ request()->is('supplier-report') ? 'active' : '' }}"><i data-feather="user-check"></i><span>Supplier Report</span></a></li>
								<li><a href="{{ url('/customer-report') }}" class="{{ request()->is('customer-report') ? 'active' : '' }}"><i data-feather="user"></i><span>Customer Report</span></a></li>
								<li><a href="{{ url('/expense-report') }}" class="{{ request()->is('expense-report') ? 'active' : '' }}"><i data-feather="file"></i><span>Expense Report</span></a></li>
								<li><a href="{{ url('/income-report') }}" class="{{ request()->is('income-report') ? 'active' : '' }}"><i data-feather="bar-chart"></i><span>Income Report</span></a></li>
								<li><a href="{{ url('/tax-reports') }}" class="{{ request()->is('tax-reports') ? 'active' : '' }}"><i data-feather="database"></i><span>Tax Report</span></a></li>
								<li><a href="{{ url('/profit-and-loss') }}" class="{{ request()->is('profit-and-loss') ? 'active' : '' }}"><i data-feather="pie-chart"></i><span>Profit & Loss</span></a></li>
							</ul>
						</li>
						@if(auth()->user()->hasRole('admin'))
						<li class="submenu-open">
							<h6 class="submenu-hdr">User Management</h6>
							<ul>
								<li><a href="{{ url('/users') }}" class="{{ request()->is('users') ? 'active' : '' }}"><i data-feather="user-check"></i><span>Users</span></a></li>
								<li><a href="{{ url('/roles-permissions') }}" class="{{ request()->is('roles-permissions') ? 'active' : '' }}"><i data-feather="shield"></i><span>Roles & Permissions</span></a></li>
								<li><a href="{{ url('/delete-account') }}" class="{{ request()->is('delete-account') ? 'active' : '' }}"><i data-feather="lock"></i><span>Delete Account Request</span></a></li>
							</ul>
						</li>
						@endif
						
						<li class="submenu-open">
							<h6 class="submenu-hdr">Pages</h6>
							<ul>
								<li><a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><i data-feather="user"></i><span>Profile</span></a></li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="shield"></i><span>Authentication</span><span class="menu-arrow"></span></a>
									<ul>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Login<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/signin') }}" class="{{ request()->is('signin') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/signin-2') }}" class="{{ request()->is('signin-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/signin-3') }}" class="{{ request()->is('signin-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Register<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/register') }}" class="{{ request()->is('register') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/register-2') }}" class="{{ request()->is('register-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/register-3') }}" class="{{ request()->is('register-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Forgot Password<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/forgot-password') }}" class="{{ request()->is('forgot-password') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/forgot-password-2') }}" class="{{ request()->is('forgot-password-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/forgot-password-3') }}" class="{{ request()->is('forgot-password-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Reset Password<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/reset-password') }}" class="{{ request()->is('reset-password') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/reset-password-2') }}" class="{{ request()->is('reset-password-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/reset-password-3') }}" class="{{ request()->is('reset-password-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Email Verification<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/email-verification') }}" class="{{ request()->is('email-verification') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/email-verification-2') }}" class="{{ request()->is('email-verification-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/email-verification-3') }}" class="{{ request()->is('email-verification-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">2 Step Verification<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/two-step-verification') }}" class="{{ request()->is('two-step-verification') ? 'active' : '' }}">Cover</a></li>
												<li><a href="{{ url('/two-step-verification-2') }}" class="{{ request()->is('two-step-verification-2') ? 'active' : '' }}">Illustration</a></li>
												<li><a href="{{ url('/two-step-verification-3') }}" class="{{ request()->is('two-step-verification-3') ? 'active' : '' }}">Basic</a></li>
											</ul>
										</li>
										<li><a href="{{ url('/lock-screen') }}" class="{{ request()->is('lock-screen') ? 'active' : '' }}">Lock Screen</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="file-minus"></i><span>Error Pages</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/error-404') }}" class="{{ request()->is('error-404') ? 'active' : '' }}">404 Error </a></li>
										<li><a href="{{ url('/error-500') }}" class="{{ request()->is('error-500') ? 'active' : '' }}">500 Error </a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="map"></i><span>Places</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/countries') }}" class="{{ request()->is('countries') ? 'active' : '' }}">Countries</a></li>
										<li><a href="{{ url('/states') }}" class="{{ request()->is('states') ? 'active' : '' }}">States</a></li>
									</ul>
								</li>
								<li>
									<a href="{{ url('/blank-page') }}" class="{{ request()->is('blank-page') ? 'active' : '' }}"><i data-feather="file"></i><span>Blank Page</span> </a>
								</li>
								<li>
									<a href="{{ url('/coming-soon') }}" class="{{ request()->is('coming-soon') ? 'active' : '' }}"><i data-feather="send"></i><span>Coming Soon</span> </a>
								</li>
								<li>
									<a href="{{ url('/under-maintenance') }}" class="{{ request()->is('under-maintenance') ? 'active' : '' }}"><i data-feather="alert-triangle"></i><span>Under Maintenance</span> </a>
								</li>
							</ul>
						</li>
						<li class="submenu-open">
							<h6 class="submenu-hdr">Settings</h6>
							<ul>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="settings"></i><span>General Settings</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/general-settings') }}" class="{{ request()->is('general-settings') ? 'active' : '' }}">Profile</a></li>
										<li><a href="{{ url('/security-settings') }}" class="{{ request()->is('security-settings') ? 'active' : '' }}">Security</a></li>
										<li><a href="{{ url('/notification') }}" class="{{ request()->is('notification') ? 'active' : '' }}">Notifications</a></li>
										<li><a href="{{ url('/connected-apps') }}" class="{{ request()->is('connected-apps') ? 'active' : '' }}">Connected Apps</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="globe"></i><span>Website Settings</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/system-settings') }}" class="{{ request()->is('system-settings') ? 'active' : '' }}">System Settings</a></li>
										<li><a href="{{ url('/company-settings') }}" class="{{ request()->is('company-settings') ? 'active' : '' }}">Company Settings </a></li>
										<li><a href="{{ url('/localization-settings') }}" class="{{ request()->is('localization-settings') ? 'active' : '' }}">Localization</a></li>
										<li><a href="{{ url('/prefixes') }}" class="{{ request()->is('prefixes') ? 'active' : '' }}">Prefixes</a></li>
										<li><a href="{{ url('/preference') }}" class="{{ request()->is('preference') ? 'active' : '' }}">Preference</a></li>
										<li><a href="{{ url('/appearance') }}" class="{{ request()->is('appearance') ? 'active' : '' }}">Appearance</a></li>
										<li><a href="{{ url('/social-authentication') }}" class="{{ request()->is('social-authentication') ? 'active' : '' }}">Social Authentication</a></li>
										<li><a href="{{ url('/language-settings') }}" class="{{ request()->is('language-settings') ? 'active' : '' }}">Language</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="smartphone"></i>
										<span>App Settings</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/invoice-settings') }}" class="{{ request()->is('invoice-settings') ? 'active' : '' }}">Invoice</a></li>
										<li><a href="{{ url('/printer-settings') }}" class="{{ request()->is('printer-settings') ? 'active' : '' }}">Printer</a></li>
										<li><a href="{{ url('/pos-settings') }}" class="{{ request()->is('pos-settings') ? 'active' : '' }}">POS</a></li>
										<li><a href="{{ url('/custom-fields') }}" class="{{ request()->is('custom-fields') ? 'active' : '' }}">Custom Fields</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="monitor"></i>
										<span>System Settings</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/email-settings') }}" class="{{ request()->is('email-settings') ? 'active' : '' }}">Email</a></li>
										<li><a href="{{ url('/sms-gateway') }}" class="{{ request()->is('sms-gateway') ? 'active' : '' }}">SMS Gateways</a></li>
										<li><a href="{{ url('/otp-settings') }}" class="{{ request()->is('otp-settings') ? 'active' : '' }}">OTP</a></li>
										<li><a href="{{ url('/gdpr-settings') }}" class="{{ request()->is('gdpr-settings') ? 'active' : '' }}">GDPR Cookies</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="dollar-sign"></i>
										<span>Settings</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/payment-gateway-settings') }}" class="{{ request()->is('payment-gateway-settings') ? 'active' : '' }}">Payment Gateway</a></li>
										<li><a href="{{ url('/bank-settings-grid') }}" class="{{ request()->is('bank-settings-grid') ? 'active' : '' }}">Bank Accounts</a></li>
										<li><a href="{{ url('/tax-rates') }}" class="{{ request()->is('tax-rates') ? 'active' : '' }}">Tax Rates</a></li>
										<li><a href="{{ url('/currency-settings') }}" class="{{ request()->is('currency-settings') ? 'active' : '' }}">Currencies</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="hexagon"></i>
										<span>Other Settings</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/storage-settings') }}" class="{{ request()->is('storage-settings') ? 'active' : '' }}">Storage</a></li>
										<li><a href="{{ url('/ban-ip-address') }}" class="{{ request()->is('ban-ip-address') ? 'active' : '' }}">Ban IP Address</a></li>
									</ul>
								</li>
								<li>
									<a href="{{ url('/signin') }}" class="{{ request()->is('signin') ? 'active' : '' }}"><i data-feather="log-out"></i><span>Logout</span> </a>
								</li>
							</ul>
						</li>
						<!-- <li class="submenu-open">
							<h6 class="submenu-hdr">UI Interface</h6>
							<ul>
								<li class="submenu">
									<a href="javascript:void(0);">
										<i data-feather="layers"></i><span>Base UI</span><span class="menu-arrow"></span>
									</a>
									<ul>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);">
										<i data-feather="layers"></i><span>Advanced UI</span><span class="menu-arrow"></span>
									</a>
									<ul>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="bar-chart-2"></i>
										<span>Charts</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/chart-apex') }}" class="{{ request()->is('chart-apex') ? 'active' : '' }}">Apex Charts</a></li>
										<li><a href="{{ url('/chart-c3') }}" class="{{ request()->is('chart-c3') ? 'active' : '' }}">Chart C3</a></li>
										<li><a href="{{ url('/chart-js') }}" class="{{ request()->is('chart-js') ? 'active' : '' }}">Chart Js</a></li>
										<li><a href="{{ url('/chart-morris') }}" class="{{ request()->is('chart-morris') ? 'active' : '' }}">Morris Charts</a></li>
										<li><a href="{{ url('/chart-flot') }}" class="{{ request()->is('chart-flot') ? 'active' : '' }}">Flot Charts</a></li>
										<li><a href="{{ url('/chart-peity') }}" class="{{ request()->is('chart-peity') ? 'active' : '' }}">Peity Charts</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="database"></i>
										<span>Icons</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="{{ url('/icon-fontawesome') }}" class="{{ request()->is('icon-fontawesome') ? 'active' : '' }}">Fontawesome Icons</a></li>
										<li><a href="{{ url('/icon-feather') }}" class="{{ request()->is('icon-feather') ? 'active' : '' }}">Feather Icons</a></li>
										<li><a href="{{ url('/icon-ionic') }}" class="{{ request()->is('icon-ionic') ? 'active' : '' }}">Ionic Icons</a></li>
										<li><a href="{{ url('/icon-material') }}" class="{{ request()->is('icon-material') ? 'active' : '' }}">Material Icons</a></li>
										<li><a href="{{ url('/icon-pe7') }}" class="{{ request()->is('icon-pe7') ? 'active' : '' }}">Pe7 Icons</a></li>
										<li><a href="{{ url('/icon-simpleline') }}" class="{{ request()->is('icon-simpleline') ? 'active' : '' }}">Simpleline Icons</a></li>
										<li><a href="{{ url('/icon-themify') }}" class="{{ request()->is('icon-themify') ? 'active' : '' }}">Themify Icons</a></li>
										<li><a href="{{ url('/icon-weather') }}" class="{{ request()->is('icon-weather') ? 'active' : '' }}">Weather Icons</a></li>
										<li><a href="{{ url('/icon-typicon') }}" class="{{ request()->is('icon-typicon') ? 'active' : '' }}">Typicon Icons</a></li>
										<li><a href="{{ url('/icon-flag') }}" class="{{ request()->is('icon-flag') ? 'active' : '' }}">Flag Icons</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);">
										<i data-feather="edit"></i><span>Forms</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li class="submenu submenu-two">
											<a href="javascript:void(0);">Form Elements<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/form-basic-inputs') }}" class="{{ request()->is('form-basic-inputs') ? 'active' : '' }}">Basic Inputs</a></li>
												<li><a href="{{ url('/form-checkbox-radios') }}" class="{{ request()->is('form-checkbox-radios') ? 'active' : '' }}">Checkbox & Radios</a></li>
												<li><a href="{{ url('/form-input-groups') }}" class="{{ request()->is('form-input-groups') ? 'active' : '' }}">Input Groups</a></li>
												<li><a href="{{ url('/form-grid-gutters') }}" class="{{ request()->is('form-grid-gutters') ? 'active' : '' }}">Grid & Gutters</a></li>
												<li><a href="{{ url('/form-select') }}" class="{{ request()->is('form-select') ? 'active' : '' }}">Form Select</a></li>
												<li><a href="{{ url('/form-mask') }}" class="{{ request()->is('form-mask') ? 'active' : '' }}">Input Masks</a></li>
												<li><a href="{{ url('/form-fileupload') }}" class="{{ request()->is('form-fileupload') ? 'active' : '' }}">File Uploads</a></li>
											</ul>
										</li>
										<li class="submenu submenu-two">
											<a href="javascript:void(0);">Layouts<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="{{ url('/form-horizontal') }}" class="{{ request()->is('form-horizontal') ? 'active' : '' }}">Horizontal Form</a></li>
												<li><a href="{{ url('/form-vertical') }}" class="{{ request()->is('form-vertical') ? 'active' : '' }}">Vertical Form</a></li>
												<li><a href="{{ url('/form-floating-labels') }}" class="{{ request()->is('form-floating-labels') ? 'active' : '' }}">Floating Labels</a></li>
											</ul>
										</li>
										<li><a href="{{ url('/form-validation') }}" class="{{ request()->is('form-validation') ? 'active' : '' }}">Form Validation</a></li>
										<li><a href="{{ url('/form-select2') }}" class="{{ request()->is('form-select2') ? 'active' : '' }}">Select2</a></li>
										<li><a href="{{ url('/form-wizard') }}" class="{{ request()->is('form-wizard') ? 'active' : '' }}">Form Wizard</a></li>
									</ul>
								</li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="columns"></i><span>Tables</span><span
											class="menu-arrow"></span></a>
									<ul>
										<li><a href="{{ url('/tables-basic') }}" class="{{ request()->is('tables-basic') ? 'active' : '' }}">Basic Tables </a></li>
										<li><a href="{{ url('/data-tables') }}" class="{{ request()->is('data-tables') ? 'active' : '' }}">Data Table </a></li>
									</ul>
								</li>
							</ul>
						</li> -->
						<li class="submenu-open">
							<h6 class="submenu-hdr">Help</h6>
							<ul>
								<li><a href="javascript:void(0);"><i data-feather="file-text"></i><span>Documentation</span></a></li>
								<li><a href="javascript:void(0);"><i data-feather="lock"></i><span>Changelog v2.0.3</span></a></li>
								<li class="submenu">
									<a href="javascript:void(0);"><i data-feather="file-minus"></i><span>Multi Level</span><span class="menu-arrow"></span></a>
									<ul>
										<li><a href="javascript:void(0);">Level 1.1</a></li>
										<li class="submenu submenu-two"><a href="javascript:void(0);">Level 1.2<span class="menu-arrow inside-submenu"></span></a>
											<ul>
												<li><a href="javascript:void(0);">Level 2.1</a></li>
												<li class="submenu submenu-two submenu-three"><a href="javascript:void(0);">Level 2.2<span class="menu-arrow inside-submenu inside-submenu-two"></span></a>
													<ul>
														<li><a href="javascript:void(0);">Level 3.1</a></li>
														<li><a href="javascript:void(0);">Level 3.2</a></li>
													</ul>
												</li>
											</ul>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>

<div class="sidebar collapsed-sidebar" id="collapsed-sidebar">
			<div class="sidebar-inner slimscroll">
				<div id="sidebar-menu-2" class="sidebar-menu sidebar-menu-three">
					<aside id="aside" class="ui-aside">
						<ul class="tab nav nav-tabs" id="myTab" role="tablist">
						<li class="nav-item" role="presentation">
							<a href="#home" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"  role="tab" aria-selected="true" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/menu-icon.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#messages" id="messages-tab" data-bs-toggle="tab" data-bs-target="#product"  role="tab" aria-selected="false" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/product.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation"> 
							<a  href="#profile" id="profile-tab" data-bs-toggle="tab" data-bs-target="#sales"  role="tab" aria-selected="false" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/sales1.svg') }}" alt="">
							</a>
						</li>
						
						<li class="nav-item" role="presentation">
							<a href="#report" id="report-tab" data-bs-toggle="tab" data-bs-target="#purchase"  role="tab" aria-selected="true" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/purchase1.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set" id="set-tab" data-bs-toggle="tab" data-bs-target="#user"  role="tab" aria-selected="true" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/users1.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set2" id="set-tab2" data-bs-toggle="tab" data-bs-target="#employee"  role="tab" aria-selected="true" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/calendars.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set3" id="set-tab3" data-bs-toggle="tab" data-bs-target="#report"  role="tab" aria-selected="true" class="tablinks nav-link">
								<img src="{{ asset('assets/img/icons/printer.svg') }}" alt="">
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set4" id="set-tab4" data-bs-toggle="tab" data-bs-target="#document"  role="tab" aria-selected="true" class="tablinks nav-link">
								<i data-feather="file-minus"></i>
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set5" id="set-tab6" data-bs-toggle="tab" data-bs-target="#permission"  role="tab" aria-selected="true" class="tablinks nav-link">
								<i data-feather="user"></i>
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a href="#set6" id="set-tab5" data-bs-toggle="tab" data-bs-target="#settings"  role="tab" aria-selected="true" class="tablinks nav-link">
								<i data-feather="settings"></i>
							</a>
						</li> 
						</ul>
					</aside>
					<div class="tab-content tab-content-four pt-2">
						<ul class="tab-pane active" id="home" aria-labelledby="home-tab">
							<li class="submenu">
								<a href="javascript:void(0);"><span>Dashboard</span> <span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Admin Dashboard</a></li>
									<li><a href="{{ url('/sales-dashboard') }}" class="{{ request()->is('sales-dashboard') ? 'active' : '' }}">Sales Dashboard</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Application</span><span class="menu-arrow"></span></a>
								<ul>
									<li class="submenu submenu-two"><a href="javascript:void(0);"><span>Call</span><span class="menu-arrow inside-submenu"></span></a>
										<ul>
										</ul>
									</li>
									<li><a href="{{ url('/email') }}" class="{{ request()->is('email') ? 'active' : '' }}">Email</a></li>
								</ul>
							</li>
						</ul>
						<ul class="tab-pane" id="product" aria-labelledby="messages-tab">
							<li><a href="{{ url('/product-list') }}" class="{{ request()->is('product-list') ? 'active' : '' }}"><span>Products</span></a></li>
							<li><a href="{{ url('/add-product') }}" class="{{ request()->is('add-product') ? 'active' : '' }}"><span>Create Product</span></a></li>
							<li><a href="{{ url('/expired-products') }}" class="{{ request()->is('expired-products') ? 'active' : '' }}"><span>Expired Products</span></a></li>
							<li><a href="{{ url('/low-stocks') }}" class="{{ request()->is('low-stocks') ? 'active' : '' }}"><span>Low Stocks</span></a></li>
							<li><a href="{{ url('/category-list') }}" class="{{ request()->is('category-list') ? 'active' : '' }}"><span>Category</span></a></li>
							<li><a href="{{ url('/sub-categories') }}" class="{{ request()->is('sub-categories') ? 'active' : '' }}"><span>Sub Category</span></a></li>
							<li><a href="{{ url('/brand-list') }}" class="{{ request()->is('brand-list') ? 'active' : '' }}"><span>Brands</span></a></li>
							<li><a href="{{ url('/units') }}" class="{{ request()->is('units') ? 'active' : '' }}"><span>Units</span></a></li>
							<li><a href="{{ url('/varriant-attributes') }}" class="{{ request()->is('varriant-attributes') ? 'active' : '' }}"><span>Variant Attributes</span></a></li>
							<li><a href="{{ url('/warranty') }}" class="{{ request()->is('warranty') ? 'active' : '' }}"><span>Warranties</span></a></li>
							<li><a href="{{ url('/barcode') }}" class="{{ request()->is('barcode') ? 'active' : '' }}"><span>Print Barcode</span></a></li>
							<li><a href="{{ url('/qrcode') }}" class="{{ request()->is('qrcode') ? 'active' : '' }}"><span>Print QR Code</span></a></li>
						</ul>
						<ul class="tab-pane" id="sales" aria-labelledby="profile-tab">
							<li><a href="{{ url('/sales-list') }}" class="{{ request()->is('sales-list') ? 'active' : '' }}"><span>Sales</span></a></li>
							<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><span>Invoices</span></a></li>
							<li><a href="{{ url('/sales-returns') }}" class="{{ request()->is('sales-returns') ? 'active' : '' }}"><span>Sales Return</span></a></li>
							<li><a href="{{ url('/quotation-list') }}" class="{{ request()->is('quotation-list') ? 'active' : '' }}"><span>Quotation</span></a></li>
							<li><a href="{{ url('/pos') }}" class="{{ request()->is('pos') ? 'active' : '' }}"><span>POS</span></a></li>
							<li><a href="{{ url('/coupons') }}" class="{{ request()->is('coupons') ? 'active' : '' }}"><span>Coupons</span></a></li>
						</ul>
						<ul class="tab-pane" id="purchase" aria-labelledby="report-tab">
							<li><a href="{{ url('/purchase-list') }}" class="{{ request()->is('purchase-list') ? 'active' : '' }}"><span>Purchases</span></a></li>
							<li><a href="{{ url('/purchase-order-report') }}" class="{{ request()->is('purchase-order-report') ? 'active' : '' }}"><span>Purchase Order</span></a></li>
							<li><a href="{{ url('/purchase-returns') }}" class="{{ request()->is('purchase-returns') ? 'active' : '' }}"><span>Purchase Return</span></a></li>
							<li><a href="{{ url('/manage-stocks') }}" class="{{ request()->is('manage-stocks') ? 'active' : '' }}"><span>Manage Stock</span></a></li>
							<li><a href="{{ url('/stock-adjustment') }}" class="{{ request()->is('stock-adjustment') ? 'active' : '' }}"><span>Stock Adjustment</span></a></li>
							<li><a href="{{ url('/stock-transfer') }}" class="{{ request()->is('stock-transfer') ? 'active' : '' }}"><span>Stock Transfer</span></a></li>							
							<li class="submenu">
								<a href="javascript:void(0);"><span>Expenses</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/expense-list') }}" class="{{ request()->is('expense-list') ? 'active' : '' }}">Expenses</a></li>
									<li><a href="{{ url('/expense-category') }}" class="{{ request()->is('expense-category') ? 'active' : '' }}">Expense Category</a></li>
								</ul>
							</li>

						</ul>
						<ul class="tab-pane" id="user" aria-labelledby="set-tab">

							<li><a href="{{ url('/customers') }}" class="{{ request()->is('customers') ? 'active' : '' }}"><span>Customers</span></a></li>
							<li><a href="{{ url('/suppliers') }}" class="{{ request()->is('suppliers') ? 'active' : '' }}"><span>Suppliers</span></a></li>
							<li><a href="{{ url('/store-list') }}" class="{{ request()->is('store-list') ? 'active' : '' }}"><span>Stores</span></a></li>
							<li><a href="{{ url('/warehouse') }}" class="{{ request()->is('warehouse') ? 'active' : '' }}"><span>Warehouses</span></a></li>

						</ul>
						<ul class="tab-pane" id="employee" aria-labelledby="set-tab2">
							<li><a href="{{ url('/employees-grid') }}" class="{{ request()->is('employees-grid') ? 'active' : '' }}"><span>Employees</span></a></li>
							<li><a href="{{ url('/department-grid') }}" class="{{ request()->is('department-grid') ? 'active' : '' }}"><span>Departments</span></a></li>
							<li><a href="{{ url('/designation') }}" class="{{ request()->is('designation') ? 'active' : '' }}"><span>Designation</span></a></li>
							<li><a href="{{ url('/shift') }}" class="{{ request()->is('shift') ? 'active' : '' }}"><span>Shifts</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Attendence</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/attendance-employee') }}" class="{{ request()->is('attendance-employee') ? 'active' : '' }}">Employee Attendence</a></li>
									<li><a href="{{ url('/attendance-admin') }}" class="{{ request()->is('attendance-admin') ? 'active' : '' }}">Admin Attendence</a></li>
								</ul>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Leaves</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/leaves-admin') }}" class="{{ request()->is('leaves-admin') ? 'active' : '' }}">Admin Leaves</a></li>
									<li><a href="{{ url('/leaves-employee') }}" class="{{ request()->is('leaves-employee') ? 'active' : '' }}">Employee Leaves</a></li>
									<li><a href="{{ url('/leave-types') }}" class="{{ request()->is('leave-types') ? 'active' : '' }}">Leave Types</a></li>
								</ul>
							</li>
							<li><a href="{{ url('/holidays') }}" class="{{ request()->is('holidays') ? 'active' : '' }}"><span>Holidays</span></a></li>
							<li class="submenu">
								<a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}"><span>Payroll</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}">Employee Salary</a></li>
									<li><a href="{{ url('/payslip') }}" class="{{ request()->is('payslip') ? 'active' : '' }}">Payslip</a></li>
								</ul>
							</li>
						</ul>
						<ul class="tab-pane" id="report" aria-labelledby="set-tab3">
							<li><a href="{{ url('/sales-report') }}" class="{{ request()->is('sales-report') ? 'active' : '' }}"><span>Sales Report</span></a></li>
							<li><a href="{{ url('/purchase-report') }}" class="{{ request()->is('purchase-report') ? 'active' : '' }}"><span>Purchase report</span></a></li>
							<li><a href="{{ url('/inventory-report') }}" class="{{ request()->is('inventory-report') ? 'active' : '' }}"><span>Inventory Report</span></a></li>
							<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><span>Invoice Report</span></a></li>
							<li><a href="{{ url('/supplier-report') }}" class="{{ request()->is('supplier-report') ? 'active' : '' }}"><span>Supplier Report</span></a></li>
							<li><a href="{{ url('/customer-report') }}" class="{{ request()->is('customer-report') ? 'active' : '' }}"><span>Customer Report</span></a></li>
							<li><a href="{{ url('/expense-report') }}" class="{{ request()->is('expense-report') ? 'active' : '' }}"><span>Expense Report</span></a></li>
							<li><a href="{{ url('/income-report') }}" class="{{ request()->is('income-report') ? 'active' : '' }}"><span>Income Report</span></a></li>
							<li><a href="{{ url('/tax-reports') }}" class="{{ request()->is('tax-reports') ? 'active' : '' }}"><span>Tax Report</span></a></li>
							<li><a href="{{ url('/profit-and-loss') }}" class="{{ request()->is('profit-and-loss') ? 'active' : '' }}"><span>Profit & Loss</span></a></li>
						</ul>
						<ul class="tab-pane" id="permission" aria-labelledby="set-tab4">
							<li><a href="{{ url('/users') }}" class="{{ request()->is('users') ? 'active' : '' }}"><span>Users</span></a></li>
							<li><a href="{{ url('/roles-permissions') }}" class="{{ request()->is('roles-permissions') ? 'active' : '' }}"><span>Roles & Permissions</span></a></li>
							<li><a href="{{ url('/delete-account') }}" class="{{ request()->is('delete-account') ? 'active' : '' }}"><span>Delete Account Request</span></a></li>

							<li class="submenu">
								<a href="javascript:void(0);">
									<span>Base UI</span><span class="menu-arrow"></span>
								</a>
								<ul>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									<span>Advanced UI</span><span class="menu-arrow"></span>
								</a>
								<ul>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Charts</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/chart-apex') }}" class="{{ request()->is('chart-apex') ? 'active' : '' }}">Apex Charts</a></li>
									<li><a href="{{ url('/chart-c3') }}" class="{{ request()->is('chart-c3') ? 'active' : '' }}">Chart C3</a></li>
									<li><a href="{{ url('/chart-js') }}" class="{{ request()->is('chart-js') ? 'active' : '' }}">Chart Js</a></li>
									<li><a href="{{ url('/chart-morris') }}" class="{{ request()->is('chart-morris') ? 'active' : '' }}">Morris Charts</a></li>
									<li><a href="{{ url('/chart-flot') }}" class="{{ request()->is('chart-flot') ? 'active' : '' }}">Flot Charts</a></li>
									<li><a href="{{ url('/chart-peity') }}" class="{{ request()->is('chart-peity') ? 'active' : '' }}">Peity Charts</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Icons</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/icon-fontawesome') }}" class="{{ request()->is('icon-fontawesome') ? 'active' : '' }}">Fontawesome Icons</a></li>
									<li><a href="{{ url('/icon-feather') }}" class="{{ request()->is('icon-feather') ? 'active' : '' }}">Feather Icons</a></li>
									<li><a href="{{ url('/icon-ionic') }}" class="{{ request()->is('icon-ionic') ? 'active' : '' }}">Ionic Icons</a></li>
									<li><a href="{{ url('/icon-material') }}" class="{{ request()->is('icon-material') ? 'active' : '' }}">Material Icons</a></li>
									<li><a href="{{ url('/icon-pe7') }}" class="{{ request()->is('icon-pe7') ? 'active' : '' }}">Pe7 Icons</a></li>
									<li><a href="{{ url('/icon-simpleline') }}" class="{{ request()->is('icon-simpleline') ? 'active' : '' }}">Simpleline Icons</a></li>
									<li><a href="{{ url('/icon-themify') }}" class="{{ request()->is('icon-themify') ? 'active' : '' }}">Themify Icons</a></li>
									<li><a href="{{ url('/icon-weather') }}" class="{{ request()->is('icon-weather') ? 'active' : '' }}">Weather Icons</a></li>
									<li><a href="{{ url('/icon-typicon') }}" class="{{ request()->is('icon-typicon') ? 'active' : '' }}">Typicon Icons</a></li>
									<li><a href="{{ url('/icon-flag') }}" class="{{ request()->is('icon-flag') ? 'active' : '' }}">Flag Icons</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									<span>Forms</span><span class="menu-arrow"></span>
								</a>
								<ul>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);">Form Elements<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/form-basic-inputs') }}" class="{{ request()->is('form-basic-inputs') ? 'active' : '' }}">Basic Inputs</a></li>
											<li><a href="{{ url('/form-checkbox-radios') }}" class="{{ request()->is('form-checkbox-radios') ? 'active' : '' }}">Checkbox & Radios</a></li>
											<li><a href="{{ url('/form-input-groups') }}" class="{{ request()->is('form-input-groups') ? 'active' : '' }}">Input Groups</a></li>
											<li><a href="{{ url('/form-grid-gutters') }}" class="{{ request()->is('form-grid-gutters') ? 'active' : '' }}">Grid & Gutters</a></li>
											<li><a href="{{ url('/form-select') }}" class="{{ request()->is('form-select') ? 'active' : '' }}">Form Select</a></li>
											<li><a href="{{ url('/form-mask') }}" class="{{ request()->is('form-mask') ? 'active' : '' }}">Input Masks</a></li>
											<li><a href="{{ url('/form-fileupload') }}" class="{{ request()->is('form-fileupload') ? 'active' : '' }}">File Uploads</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);">Layouts<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/form-horizontal') }}" class="{{ request()->is('form-horizontal') ? 'active' : '' }}">Horizontal Form</a></li>
											<li><a href="{{ url('/form-vertical') }}" class="{{ request()->is('form-vertical') ? 'active' : '' }}">Vertical Form</a></li>
											<li><a href="{{ url('/form-floating-labels') }}" class="{{ request()->is('form-floating-labels') ? 'active' : '' }}">Floating Labels</a></li>
										</ul>
									</li>
									<li><a href="{{ url('/form-validation') }}" class="{{ request()->is('form-validation') ? 'active' : '' }}">Form Validation</a></li>
									<li><a href="{{ url('/form-select2') }}" class="{{ request()->is('form-select2') ? 'active' : '' }}">Select2</a></li>
									<li><a href="{{ url('/form-wizard') }}" class="{{ request()->is('form-wizard') ? 'active' : '' }}">Form Wizard</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Tables</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/tables-basic') }}" class="{{ request()->is('tables-basic') ? 'active' : '' }}">Basic Tables </a></li>
									<li><a href="{{ url('/data-tables') }}" class="{{ request()->is('data-tables') ? 'active' : '' }}">Data Table </a></li>
								</ul>
							</li>

						</ul>
						<ul class="tab-pane" id="document" aria-labelledby="set-tab5">
							<li><a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><span>Profile</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Authentication</span><span class="menu-arrow"></span></a>
								<ul>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Login<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/signin') }}" class="{{ request()->is('signin') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/signin-2') }}" class="{{ request()->is('signin-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/signin-3') }}" class="{{ request()->is('signin-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Register<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/register') }}" class="{{ request()->is('register') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/register-2') }}" class="{{ request()->is('register-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/register-3') }}" class="{{ request()->is('register-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Forgot Password<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/forgot-password') }}" class="{{ request()->is('forgot-password') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/forgot-password-2') }}" class="{{ request()->is('forgot-password-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/forgot-password-3') }}" class="{{ request()->is('forgot-password-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Reset Password<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/reset-password') }}" class="{{ request()->is('reset-password') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/reset-password-2') }}" class="{{ request()->is('reset-password-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/reset-password-3') }}" class="{{ request()->is('reset-password-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Email Verification<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/email-verification') }}" class="{{ request()->is('email-verification') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/email-verification-2') }}" class="{{ request()->is('email-verification-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/email-verification-3') }}" class="{{ request()->is('email-verification-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">2 Step Verification<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/two-step-verification') }}" class="{{ request()->is('two-step-verification') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/two-step-verification-2') }}" class="{{ request()->is('two-step-verification-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/two-step-verification-3') }}" class="{{ request()->is('two-step-verification-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li><a href="{{ url('/lock-screen') }}" class="{{ request()->is('lock-screen') ? 'active' : '' }}">Lock Screen</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Error Pages</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/error-404') }}" class="{{ request()->is('error-404') ? 'active' : '' }}">404 Error </a></li>
									<li><a href="{{ url('/error-500') }}" class="{{ request()->is('error-500') ? 'active' : '' }}">500 Error </a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Places</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/countries') }}" class="{{ request()->is('countries') ? 'active' : '' }}">Countries</a></li>
									<li><a href="{{ url('/states') }}" class="{{ request()->is('states') ? 'active' : '' }}">States</a></li>
								</ul>
							</li>
							<li>
								<a href="{{ url('/blank-page') }}" class="{{ request()->is('blank-page') ? 'active' : '' }}"><span>Blank Page</span> </a>
							</li>
							<li>
								<a href="{{ url('/coming-soon') }}" class="{{ request()->is('coming-soon') ? 'active' : '' }}"><span>Coming Soon</span> </a>
							</li>
							<li>
								<a href="{{ url('/under-maintenance') }}" class="{{ request()->is('under-maintenance') ? 'active' : '' }}"><span>Under Maintenance</span> </a>
							</li>
						</ul>
						<ul class="tab-pane" id="settings" aria-labelledby="set-tab6">
							<li class="submenu">
								<a href="javascript:void(0);"><span>General Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/general-settings') }}" class="{{ request()->is('general-settings') ? 'active' : '' }}">Profile</a></li>
									<li><a href="{{ url('/security-settings') }}" class="{{ request()->is('security-settings') ? 'active' : '' }}">Security</a></li>
									<li><a href="{{ url('/notification') }}" class="{{ request()->is('notification') ? 'active' : '' }}">Notifications</a></li>
									<li><a href="{{ url('/connected-apps') }}" class="{{ request()->is('connected-apps') ? 'active' : '' }}">Connected Apps</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Website Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/system-settings') }}" class="{{ request()->is('system-settings') ? 'active' : '' }}">System Settings</a></li>
									<li><a href="{{ url('/company-settings') }}" class="{{ request()->is('company-settings') ? 'active' : '' }}">Company Settings </a></li>
									<li><a href="{{ url('/localization-settings') }}" class="{{ request()->is('localization-settings') ? 'active' : '' }}">Localization</a></li>
									<li><a href="{{ url('/prefixes') }}" class="{{ request()->is('prefixes') ? 'active' : '' }}">Prefixes</a></li>
									<li><a href="{{ url('/preference') }}" class="{{ request()->is('preference') ? 'active' : '' }}">Preference</a></li>
									<li><a href="{{ url('/appearance') }}" class="{{ request()->is('appearance') ? 'active' : '' }}">Appearance</a></li>
									<li><a href="{{ url('/social-authentication') }}" class="{{ request()->is('social-authentication') ? 'active' : '' }}">Social Authentication</a></li>
									<li><a href="{{ url('/language-settings') }}" class="{{ request()->is('language-settings') ? 'active' : '' }}">Language</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>App Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/invoice-settings') }}" class="{{ request()->is('invoice-settings') ? 'active' : '' }}">Invoice</a></li>
									<li><a href="{{ url('/printer-settings') }}" class="{{ request()->is('printer-settings') ? 'active' : '' }}">Printer</a></li>
									<li><a href="{{ url('/pos-settings') }}" class="{{ request()->is('pos-settings') ? 'active' : '' }}">POS</a></li>
									<li><a href="{{ url('/custom-fields') }}" class="{{ request()->is('custom-fields') ? 'active' : '' }}">Custom Fields</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>System Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/email-settings') }}" class="{{ request()->is('email-settings') ? 'active' : '' }}">Email</a></li>
									<li><a href="{{ url('/sms-gateway') }}" class="{{ request()->is('sms-gateway') ? 'active' : '' }}">SMS Gateways</a></li>
									<li><a href="{{ url('/otp-settings') }}" class="{{ request()->is('otp-settings') ? 'active' : '' }}">OTP</a></li>
									<li><a href="{{ url('/gdpr-settings') }}" class="{{ request()->is('gdpr-settings') ? 'active' : '' }}">GDPR Cookies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Financial Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/payment-gateway-settings') }}" class="{{ request()->is('payment-gateway-settings') ? 'active' : '' }}">Payment Gateway</a></li>
									<li><a href="{{ url('/bank-settings-grid') }}" class="{{ request()->is('bank-settings-grid') ? 'active' : '' }}">Bank Accounts</a></li>
									<li><a href="{{ url('/tax-rates') }}" class="{{ request()->is('tax-rates') ? 'active' : '' }}">Tax Rates</a></li>
									<li><a href="{{ url('/currency-settings') }}" class="{{ request()->is('currency-settings') ? 'active' : '' }}">Currencies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Other Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/storage-settings') }}" class="{{ request()->is('storage-settings') ? 'active' : '' }}">Storage</a></li>
									<li><a href="{{ url('/ban-ip-address') }}" class="{{ request()->is('ban-ip-address') ? 'active' : '' }}">Ban IP Address</a></li>
								</ul>
							</li>
							<li><a href="javascript:void(0);"><span>Documentation</span></a></li>
							<li><a href="javascript:void(0);"><span>Changelog v2.0.3</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Multi Level</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="javascript:void(0);">Level 1.1</a></li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Level 1.2<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="javascript:void(0);">Level 2.1</a></li>
											<li class="submenu submenu-two submenu-three"><a href="javascript:void(0);">Level 2.2<span class="menu-arrow inside-submenu inside-submenu-two"></span></a>
												<ul>
													<li><a href="javascript:void(0);">Level 3.1</a></li>
													<li><a href="javascript:void(0);">Level 3.2</a></li>
												</ul>
											</li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

<div class="sidebar horizontal-sidebar">
			<div id="sidebar-menu-3" class="sidebar-menu">
				<ul class="nav">
					<li class="submenu">
						<a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}"><i data-feather="grid"></i><span> Main Menu</span> <span class="menu-arrow"></span></a>
						<ul>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Dashboard</span> <span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Admin Dashboard</a></li>
									<li><a href="{{ url('/sales-dashboard') }}" class="{{ request()->is('sales-dashboard') ? 'active' : '' }}">Sales Dashboard</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Application</span><span class="menu-arrow"></span></a>
								<ul>
									<li class="submenu submenu-two"><a href="javascript:void(0);"><span>Call</span><span class="menu-arrow inside-submenu"></span></a>
										<ul>
										</ul>
									</li>
									<li><a href="{{ url('/email') }}" class="{{ request()->is('email') ? 'active' : '' }}">Email</a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><img src="{{ asset('assets/img/icons/product.svg') }}" alt="img"><span> Inventory
							</span> <span class="menu-arrow"></span></a>
						<ul>
							<li><a href="{{ url('/product-list') }}" class="{{ request()->is('product-list') ? 'active' : '' }}"><span>Products</span></a></li>
							<li><a href="{{ url('/add-product') }}" class="{{ request()->is('add-product') ? 'active' : '' }}"><span>Create Product</span></a></li>
							<li><a href="{{ url('/expired-products') }}" class="{{ request()->is('expired-products') ? 'active' : '' }}"><span>Expired Products</span></a></li>
							<li><a href="{{ url('/low-stocks') }}" class="{{ request()->is('low-stocks') ? 'active' : '' }}"><span>Low Stocks</span></a></li>
							<li><a href="{{ url('/category-list') }}" class="{{ request()->is('category-list') ? 'active' : '' }}"><span>Category</span></a></li>
							<li><a href="{{ url('/sub-categories') }}" class="{{ request()->is('sub-categories') ? 'active' : '' }}"><span>Sub Category</span></a></li>
							<li><a href="{{ url('/brand-list') }}" class="{{ request()->is('brand-list') ? 'active' : '' }}"><span>Brands</span></a></li>
							<li><a href="{{ url('/units') }}" class="{{ request()->is('units') ? 'active' : '' }}"><span>Units</span></a></li>
							<li><a href="{{ url('/varriant-attributes') }}" class="{{ request()->is('varriant-attributes') ? 'active' : '' }}"><span>Variant Attributes</span></a></li>
							<li><a href="{{ url('/warranty') }}" class="{{ request()->is('warranty') ? 'active' : '' }}"><span>Warranties</span></a></li>
							<li><a href="{{ url('/barcode') }}" class="{{ request()->is('barcode') ? 'active' : '' }}"><span>Print Barcode</span></a></li>
							<li><a href="{{ url('/qrcode') }}" class="{{ request()->is('qrcode') ? 'active' : '' }}"><span>Print QR Code</span></a></li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><img src="{{ asset('assets/img/icons/purchase1.svg') }}" alt="img"><span>Sales &amp; Purchase</span> <span class="menu-arrow"></span></a>
						<ul>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Sales</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/sales-list') }}" class="{{ request()->is('sales-list') ? 'active' : '' }}"><span>Sales</span></a></li>
									<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><span>Invoices</span></a></li>
									<li><a href="{{ url('/sales-returns') }}" class="{{ request()->is('sales-returns') ? 'active' : '' }}"><span>Sales Return</span></a></li>
									<li><a href="{{ url('/quotation-list') }}" class="{{ request()->is('quotation-list') ? 'active' : '' }}"><span>Quotation</span></a></li>
									<li><a href="{{ url('/pos') }}" class="{{ request()->is('pos') ? 'active' : '' }}"><span>POS</span></a></li>
									<li><a href="{{ url('/coupons') }}" class="{{ request()->is('coupons') ? 'active' : '' }}"><span>Coupons</span></a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Purchase</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/purchase-list') }}" class="{{ request()->is('purchase-list') ? 'active' : '' }}"><span>Purchases</span></a></li>
									<li><a href="{{ url('/purchase-order-report') }}" class="{{ request()->is('purchase-order-report') ? 'active' : '' }}"><span>Purchase Order</span></a></li>
									<li><a href="{{ url('/purchase-returns') }}" class="{{ request()->is('purchase-returns') ? 'active' : '' }}"><span>Purchase Return</span></a></li>
									<li><a href="{{ url('/manage-stocks') }}" class="{{ request()->is('manage-stocks') ? 'active' : '' }}"><span>Manage Stock</span></a></li>
									<li><a href="{{ url('/stock-adjustment') }}" class="{{ request()->is('stock-adjustment') ? 'active' : '' }}"><span>Stock Adjustment</span></a></li>
									<li><a href="{{ url('/stock-transfer') }}" class="{{ request()->is('stock-transfer') ? 'active' : '' }}"><span>Stock Transfer</span></a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Expenses</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/expense-list') }}" class="{{ request()->is('expense-list') ? 'active' : '' }}">Expenses</a></li>
									<li><a href="{{ url('/expense-category') }}" class="{{ request()->is('expense-category') ? 'active' : '' }}">Expense Category</a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><img src="{{ asset('assets/img/icons/users1.svg') }}" alt="img"><span>User Management</span> <span class="menu-arrow"></span></a>
						<ul>
							<li class="submenu">
								<a href="javascript:void(0);"><span>People</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/customers') }}" class="{{ request()->is('customers') ? 'active' : '' }}"><span>Customers</span></a></li>
									<li><a href="{{ url('/suppliers') }}" class="{{ request()->is('suppliers') ? 'active' : '' }}"><span>Suppliers</span></a></li>
									<li><a href="{{ url('/store-list') }}" class="{{ request()->is('store-list') ? 'active' : '' }}"><span>Stores</span></a></li>
									<li><a href="{{ url('/warehouse') }}" class="{{ request()->is('warehouse') ? 'active' : '' }}"><span>Warehouses</span></a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Roles &amp; Permissions</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/roles-permissions') }}" class="{{ request()->is('roles-permissions') ? 'active' : '' }}"><span>Roles & Permissions</span></a></li>
									<li><a href="{{ url('/delete-account') }}" class="{{ request()->is('delete-account') ? 'active' : '' }}"><span>Delete Account Request</span></a></li>

								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Base UI</span><span class="menu-arrow"></span></a>
								<ul>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Advanced UI</span><span class="menu-arrow"></span></a>
								<ul>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Charts</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/chart-apex') }}" class="{{ request()->is('chart-apex') ? 'active' : '' }}">Apex Charts</a></li>
									<li><a href="{{ url('/chart-c3') }}" class="{{ request()->is('chart-c3') ? 'active' : '' }}">Chart C3</a></li>
									<li><a href="{{ url('/chart-js') }}" class="{{ request()->is('chart-js') ? 'active' : '' }}">Chart Js</a></li>
									<li><a href="{{ url('/chart-morris') }}" class="{{ request()->is('chart-morris') ? 'active' : '' }}">Morris Charts</a></li>
									<li><a href="{{ url('/chart-flot') }}" class="{{ request()->is('chart-flot') ? 'active' : '' }}">Flot Charts</a></li>
									<li><a href="{{ url('/chart-peity') }}" class="{{ request()->is('chart-peity') ? 'active' : '' }}">Peity Charts</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Primary Icons</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/icon-fontawesome') }}" class="{{ request()->is('icon-fontawesome') ? 'active' : '' }}">Fontawesome Icons</a></li>
									<li><a href="{{ url('/icon-feather') }}" class="{{ request()->is('icon-feather') ? 'active' : '' }}">Feather Icons</a></li>
									<li><a href="{{ url('/icon-ionic') }}" class="{{ request()->is('icon-ionic') ? 'active' : '' }}">Ionic Icons</a></li>
									<li><a href="{{ url('/icon-material') }}" class="{{ request()->is('icon-material') ? 'active' : '' }}">Material Icons</a></li>
									<li><a href="{{ url('/icon-pe7') }}" class="{{ request()->is('icon-pe7') ? 'active' : '' }}">Pe7 Icons</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Secondary Icons</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/icon-simpleline') }}" class="{{ request()->is('icon-simpleline') ? 'active' : '' }}">Simpleline Icons</a></li>
									<li><a href="{{ url('/icon-themify') }}" class="{{ request()->is('icon-themify') ? 'active' : '' }}">Themify Icons</a></li>
									<li><a href="{{ url('/icon-weather') }}" class="{{ request()->is('icon-weather') ? 'active' : '' }}">Weather Icons</a></li>
									<li><a href="{{ url('/icon-typicon') }}" class="{{ request()->is('icon-typicon') ? 'active' : '' }}">Typicon Icons</a></li>
									<li><a href="{{ url('/icon-flag') }}" class="{{ request()->is('icon-flag') ? 'active' : '' }}">Flag Icons</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span> Forms</span><span class="menu-arrow"></span></a>
								<ul>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);"><span>Form Elements</span><span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/form-basic-inputs') }}" class="{{ request()->is('form-basic-inputs') ? 'active' : '' }}">Basic Inputs</a></li>
											<li><a href="{{ url('/form-checkbox-radios') }}" class="{{ request()->is('form-checkbox-radios') ? 'active' : '' }}">Checkbox & Radios</a></li>
											<li><a href="{{ url('/form-input-groups') }}" class="{{ request()->is('form-input-groups') ? 'active' : '' }}">Input Groups</a></li>
											<li><a href="{{ url('/form-grid-gutters') }}" class="{{ request()->is('form-grid-gutters') ? 'active' : '' }}">Grid & Gutters</a></li>
											<li><a href="{{ url('/form-select') }}" class="{{ request()->is('form-select') ? 'active' : '' }}">Form Select</a></li>
											<li><a href="{{ url('/form-mask') }}" class="{{ request()->is('form-mask') ? 'active' : '' }}">Input Masks</a></li>
											<li><a href="{{ url('/form-fileupload') }}" class="{{ request()->is('form-fileupload') ? 'active' : '' }}">File Uploads</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);"><span> Layouts</span><span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/form-horizontal') }}" class="{{ request()->is('form-horizontal') ? 'active' : '' }}">Horizontal Form</a></li>
											<li><a href="{{ url('/form-vertical') }}" class="{{ request()->is('form-vertical') ? 'active' : '' }}">Vertical Form</a></li>
											<li><a href="{{ url('/form-floating-labels') }}" class="{{ request()->is('form-floating-labels') ? 'active' : '' }}">Floating Labels</a></li>
										</ul>
									</li>
									<li><a href="{{ url('/form-validation') }}" class="{{ request()->is('form-validation') ? 'active' : '' }}">Form Validation</a></li>
									<li><a href="{{ url('/form-select2') }}" class="{{ request()->is('form-select2') ? 'active' : '' }}">Select2</a></li>
									<li><a href="{{ url('/form-wizard') }}" class="{{ request()->is('form-wizard') ? 'active' : '' }}">Form Wizard</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Tables</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/tables-basic') }}" class="{{ request()->is('tables-basic') ? 'active' : '' }}">Basic Tables </a></li>
									<li><a href="{{ url('/data-tables') }}" class="{{ request()->is('data-tables') ? 'active' : '' }}">Data Table </a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><i data-feather="user"></i><span>Profile</span> <span class="menu-arrow"></span></a>
						<ul>
							<li><a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><span>Profile</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Authentication</span><span class="menu-arrow"></span></a>
								<ul>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Login<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/signin') }}" class="{{ request()->is('signin') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/signin-2') }}" class="{{ request()->is('signin-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/signin-3') }}" class="{{ request()->is('signin-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Register<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/register') }}" class="{{ request()->is('register') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/register-2') }}" class="{{ request()->is('register-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/register-3') }}" class="{{ request()->is('register-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Forgot Password<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/forgot-password') }}" class="{{ request()->is('forgot-password') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/forgot-password-2') }}" class="{{ request()->is('forgot-password-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/forgot-password-3') }}" class="{{ request()->is('forgot-password-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Reset Password<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/reset-password') }}" class="{{ request()->is('reset-password') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/reset-password-2') }}" class="{{ request()->is('reset-password-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/reset-password-3') }}" class="{{ request()->is('reset-password-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Email Verification<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/email-verification') }}" class="{{ request()->is('email-verification') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/email-verification-2') }}" class="{{ request()->is('email-verification-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/email-verification-3') }}" class="{{ request()->is('email-verification-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">2 Step Verification<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{ url('/two-step-verification') }}" class="{{ request()->is('two-step-verification') ? 'active' : '' }}">Cover</a></li>
											<li><a href="{{ url('/two-step-verification-2') }}" class="{{ request()->is('two-step-verification-2') ? 'active' : '' }}">Illustration</a></li>
											<li><a href="{{ url('/two-step-verification-3') }}" class="{{ request()->is('two-step-verification-3') ? 'active' : '' }}">Basic</a></li>
										</ul>
									</li>
									<li><a href="{{ url('/lock-screen') }}" class="{{ request()->is('lock-screen') ? 'active' : '' }}">Lock Screen</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Pages</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/error-404') }}" class="{{ request()->is('error-404') ? 'active' : '' }}">404 Error </a></li>
									<li><a href="{{ url('/error-500') }}" class="{{ request()->is('error-500') ? 'active' : '' }}">500 Error </a></li>
									<li><a href="{{ url('/blank-page') }}" class="{{ request()->is('blank-page') ? 'active' : '' }}"><span>Blank Page</span> </a></li>
									<li><a href="{{ url('/coming-soon') }}" class="{{ request()->is('coming-soon') ? 'active' : '' }}"><span>Coming Soon</span> </a></li>
									<li><a href="{{ url('/under-maintenance') }}" class="{{ request()->is('under-maintenance') ? 'active' : '' }}"><span>Under Maintenance</span> </a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Places</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/countries') }}" class="{{ request()->is('countries') ? 'active' : '' }}">Countries</a></li>
									<li><a href="{{ url('/states') }}" class="{{ request()->is('states') ? 'active' : '' }}">States</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Employees</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/employees-grid') }}" class="{{ request()->is('employees-grid') ? 'active' : '' }}"><span>Employees</span></a></li>
									<li><a href="{{ url('/department-grid') }}" class="{{ request()->is('department-grid') ? 'active' : '' }}"><span>Departments</span></a></li>
									<li><a href="{{ url('/designation') }}" class="{{ request()->is('designation') ? 'active' : '' }}"><span>Designation</span></a></li>
									<li><a href="{{ url('/shift') }}" class="{{ request()->is('shift') ? 'active' : '' }}"><span>Shifts</span></a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Attendence</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/attendance-employee') }}" class="{{ request()->is('attendance-employee') ? 'active' : '' }}">Employee Attendence</a></li>
									<li><a href="{{ url('/attendance-admin') }}" class="{{ request()->is('attendance-admin') ? 'active' : '' }}">Admin Attendence</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Leaves &amp; Holidays</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/leaves-admin') }}" class="{{ request()->is('leaves-admin') ? 'active' : '' }}">Admin Leaves</a></li>
									<li><a href="{{ url('/leaves-employee') }}" class="{{ request()->is('leaves-employee') ? 'active' : '' }}">Employee Leaves</a></li>
									<li><a href="{{ url('/leave-types') }}" class="{{ request()->is('leave-types') ? 'active' : '' }}">Leave Types</a></li>
									<li><a href="{{ url('/holidays') }}" class="{{ request()->is('holidays') ? 'active' : '' }}"><span>Holidays</span></a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}"><span>Payroll</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/payroll-list') }}" class="{{ request()->is('payroll-list') ? 'active' : '' }}">Employee Salary</a></li>
									<li><a href="{{ url('/payslip') }}" class="{{ request()->is('payslip') ? 'active' : '' }}">Payslip</a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><img src="{{ asset('assets/img/icons/printer.svg') }}" alt="img"><span>Reports</span> <span class="menu-arrow"></span></a>
						<ul>
							<li><a href="{{ url('/sales-report') }}" class="{{ request()->is('sales-report') ? 'active' : '' }}"><span>Sales Report</span></a></li>
							<li><a href="{{ url('/purchase-report') }}" class="{{ request()->is('purchase-report') ? 'active' : '' }}"><span>Purchase report</span></a></li>
							<li><a href="{{ url('/inventory-report') }}" class="{{ request()->is('inventory-report') ? 'active' : '' }}"><span>Inventory Report</span></a></li>
							<li><a href="{{ url('/invoice-report') }}" class="{{ request()->is('invoice-report') ? 'active' : '' }}"><span>Invoice Report</span></a></li>
							<li><a href="{{ url('/supplier-report') }}" class="{{ request()->is('supplier-report') ? 'active' : '' }}"><span>Supplier Report</span></a></li>
							<li><a href="{{ url('/customer-report') }}" class="{{ request()->is('customer-report') ? 'active' : '' }}"><span>Customer Report</span></a></li>
							<li><a href="{{ url('/expense-report') }}" class="{{ request()->is('expense-report') ? 'active' : '' }}"><span>Expense Report</span></a></li>
							<li><a href="{{ url('/income-report') }}" class="{{ request()->is('income-report') ? 'active' : '' }}"><span>Income Report</span></a></li>
							<li><a href="{{ url('/tax-reports') }}" class="{{ request()->is('tax-reports') ? 'active' : '' }}"><span>Tax Report</span></a></li>
							<li><a href="{{ url('/profit-and-loss') }}" class="{{ request()->is('profit-and-loss') ? 'active' : '' }}"><span>Profit & Loss</span></a></li>
						</ul>
					</li>
					<li class="submenu">
						<a href="javascript:void(0);"><img src="{{ asset('assets/img/icons/settings.svg') }}" alt="img"><span> Settings</span> <span class="menu-arrow"></span></a>
						<ul>
							<li class="submenu">
								<a href="javascript:void(0);"><span>General Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/general-settings') }}" class="{{ request()->is('general-settings') ? 'active' : '' }}">Profile</a></li>
									<li><a href="{{ url('/security-settings') }}" class="{{ request()->is('security-settings') ? 'active' : '' }}">Security</a></li>
									<li><a href="{{ url('/notification') }}" class="{{ request()->is('notification') ? 'active' : '' }}">Notifications</a></li>
									<li><a href="{{ url('/connected-apps') }}" class="{{ request()->is('connected-apps') ? 'active' : '' }}">Connected Apps</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Website Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/system-settings') }}" class="{{ request()->is('system-settings') ? 'active' : '' }}">System Settings</a></li>
									<li><a href="{{ url('/company-settings') }}" class="{{ request()->is('company-settings') ? 'active' : '' }}">Company Settings </a></li>
									<li><a href="{{ url('/localization-settings') }}" class="{{ request()->is('localization-settings') ? 'active' : '' }}">Localization</a></li>
									<li><a href="{{ url('/prefixes') }}" class="{{ request()->is('prefixes') ? 'active' : '' }}">Prefixes</a></li>
									<li><a href="{{ url('/preference') }}" class="{{ request()->is('preference') ? 'active' : '' }}">Preference</a></li>
									<li><a href="{{ url('/appearance') }}" class="{{ request()->is('appearance') ? 'active' : '' }}">Appearance</a></li>
									<li><a href="{{ url('/social-authentication') }}" class="{{ request()->is('social-authentication') ? 'active' : '' }}">Social Authentication</a></li>
									<li><a href="{{ url('/language-settings') }}" class="{{ request()->is('language-settings') ? 'active' : '' }}">Language</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>App Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/invoice-settings') }}" class="{{ request()->is('invoice-settings') ? 'active' : '' }}">Invoice</a></li>
									<li><a href="{{ url('/printer-settings') }}" class="{{ request()->is('printer-settings') ? 'active' : '' }}">Printer</a></li>
									<li><a href="{{ url('/pos-settings') }}" class="{{ request()->is('pos-settings') ? 'active' : '' }}">POS</a></li>
									<li><a href="{{ url('/custom-fields') }}" class="{{ request()->is('custom-fields') ? 'active' : '' }}">Custom Fields</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>System Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/email-settings') }}" class="{{ request()->is('email-settings') ? 'active' : '' }}">Email</a></li>
									<li><a href="{{ url('/sms-gateway') }}" class="{{ request()->is('sms-gateway') ? 'active' : '' }}">SMS Gateways</a></li>
									<li><a href="{{ url('/otp-settings') }}" class="{{ request()->is('otp-settings') ? 'active' : '' }}">OTP</a></li>
									<li><a href="{{ url('/gdpr-settings') }}" class="{{ request()->is('gdpr-settings') ? 'active' : '' }}">GDPR Cookies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Financial Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/payment-gateway-settings') }}" class="{{ request()->is('payment-gateway-settings') ? 'active' : '' }}">Payment Gateway</a></li>
									<li><a href="{{ url('/bank-settings-grid') }}" class="{{ request()->is('bank-settings-grid') ? 'active' : '' }}">Bank Accounts</a></li>
									<li><a href="{{ url('/tax-rates') }}" class="{{ request()->is('tax-rates') ? 'active' : '' }}">Tax Rates</a></li>
									<li><a href="{{ url('/currency-settings') }}" class="{{ request()->is('currency-settings') ? 'active' : '' }}">Currencies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Other Settings</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{ url('/storage-settings') }}" class="{{ request()->is('storage-settings') ? 'active' : '' }}">Storage</a></li>
									<li><a href="{{ url('/ban-ip-address') }}" class="{{ request()->is('ban-ip-address') ? 'active' : '' }}">Ban IP Address</a></li>
								</ul>
							</li>
							<li><a href="javascript:void(0);"><span>Documentation</span></a></li>
							<li><a href="javascript:void(0);"><span>Changelog v2.0.3</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Multi Level</span><span class="menu-arrow"></span></a>
								<ul>
									<li><a href="javascript:void(0);">Level 1.1</a></li>
									<li class="submenu submenu-two"><a href="javascript:void(0);">Level 1.2<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="javascript:void(0);">Level 2.1</a></li>
											<li class="submenu submenu-two submenu-three"><a href="javascript:void(0);">Level 2.2<span class="menu-arrow inside-submenu inside-submenu-two"></span></a>
												<ul>
													<li><a href="javascript:void(0);">Level 3.1</a></li>
													<li><a href="javascript:void(0);">Level 3.2</a></li>
												</ul>
											</li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
