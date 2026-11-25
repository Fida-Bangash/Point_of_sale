@php $tabs = ['customer-report' => 'Outstanding credit', 'customer-report.aging' => 'Debt aging', 'customer-report.collections' => 'Collections']; @endphp
<ul class="nav nav-pills mb-3">
	@foreach($tabs as $route => $label)
	<li class="nav-item"><a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a></li>
	@endforeach
</ul>
