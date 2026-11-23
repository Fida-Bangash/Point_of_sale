{{-- Sidebar for the NativePHP desktop app: only what the owner / cashier uses every day.
     Everything else (reports, HRM, purchases, finance, ...) stays on the website. --}}
@php $u = auth()->user(); @endphp
<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			<ul>
				<li class="submenu-open">
					<h6 class="submenu-hdr">Main</h6>
					<ul>
						<li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}"><i
									data-feather="grid"></i><span>Dashboard</span></a></li>
					</ul>
				</li>

				@if($u->hasRole('admin','manager','cashier'))
				<li class="submenu-open">
					<h6 class="submenu-hdr">Sales</h6>
					<ul>
						<li><a href="{{ url('/pos') }}" class="{{ request()->is('pos') ? 'active' : '' }}"><i
									data-feather="hard-drive"></i><span>POS</span></a></li>
						<li><a href="{{ url('/sales-list') }}" class="{{ request()->is('sales-list') ? 'active' : '' }}"><i
									data-feather="shopping-cart"></i><span>Sales</span></a></li>
					@if($u->hasRole('admin','manager'))
						<li><a href="{{ url('/sales-returns') }}" class="{{ request()->is('sales-returns*') ? 'active' : '' }}"><i
        							data-feather="rotate-ccw"></i><span>Sales Returns</span></a></li>
						<li><a href="{{ url('/exchanges') }}" class="{{ request()->is('exchanges*') ? 'active' : '' }}"><i
       								data-feather="repeat"></i><span>Exchanges</span></a></li>
					@endif
					</ul>
				</li>
				@endif

				@if($u->hasRole('admin','manager','stock'))
				<li class="submenu-open">
					<h6 class="submenu-hdr">Inventory</h6>
					<ul>
						<li><a href="{{ url('/product-list') }}" class="{{ request()->is('product-list') ? 'active' : '' }}"><i
									data-feather="box"></i><span>Products</span></a></li>
						<li><a href="{{ url('/add-product') }}" class="{{ request()->is('add-product') ? 'active' : '' }}"><i
									data-feather="plus-square"></i><span>Create Product</span></a></li>
						<li><a href="{{ url('/category-list') }}" class="{{ request()->is('category-list') ? 'active' : '' }}"><i
									data-feather="codepen"></i><span>Category</span></a></li>
						<li><a href="{{ url('/brand-list') }}" class="{{ request()->is('brand-list') ? 'active' : '' }}"><i
									data-feather="tag"></i><span>Brands</span></a></li>
						<li><a href="{{ url('/units') }}" class="{{ request()->is('units') ? 'active' : '' }}"><i
									data-feather="speaker"></i><span>Units</span></a></li>
					</ul>
				</li>
				@endif

				@if($u->hasRole('admin'))
				<li class="submenu-open">
					<h6 class="submenu-hdr">Manage</h6>
					<ul>
						<li><a href="{{ url('/users') }}" class="{{ request()->is('users') ? 'active' : '' }}"><i
									data-feather="user-check"></i><span>Users</span></a></li>
						<li><a href="{{ url('/company-settings') }}" class="{{ request()->is('company-settings') ? 'active' : '' }}"><i
									data-feather="settings"></i><span>Company Settings</span></a></li>
						<li><a href="{{ url('/sync') }}" class="{{ request()->is('sync') ? 'active' : '' }}"><i
									data-feather="refresh-cw"></i><span>Cloud Sync</span></a></li>
						<li><a href="{{ url('/backup') }}" class="{{ request()->is('backup') ? 'active' : '' }}"><i
									data-feather="database"></i><span>Backup</span></a></li>
						<li><a href="{{ url('/updates') }}" class="{{ request()->is('updates') ? 'active' : '' }}"><i
									data-feather="download"></i><span>Updates</span></a></li>
					</ul>
				</li>
				@endif

				<li class="submenu-open">
					<h6 class="submenu-hdr">Account</h6>
					<ul>
						<li><a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><i
									data-feather="user"></i><span>Profile</span></a></li>
						<li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
									data-feather="log-out"></i><span>Logout</span></a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>