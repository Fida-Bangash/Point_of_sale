@extends('layouts.app')

@section('title', 'Cloud Sync')

@push('styles')
<style>
	.sy { --o: #ff9f43; --oh: #f08a24; --ol: #fff1e0; --line: #eceef1; --mut: #6b7280; --ink: #212b36; }

	.sy-card {
		background: #fff;
		border: 1px solid var(--line);
		border-radius: 16px;
		box-shadow: 0 2px 12px rgba(33, 43, 54, .05);
	}

	.sy-hero {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 24px;
		flex-wrap: wrap;
		padding: 28px 30px;
		border-left: 6px solid var(--o);
	}

	.sy-left { display: flex; align-items: center; gap: 20px; }

	.sy-cloud {
		width: 72px;
		height: 72px;
		border-radius: 50%;
		background: var(--ol);
		color: var(--oh);
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 72px;
	}

	.sy-cloud svg { width: 34px; height: 34px; }
	.sy-cloud.sy-spin svg { animation: sy-rot 1.1s linear infinite; }
	.sy-cloud.sy-ok { background: #e8f8ee; color: #16a34a; }
	.sy-cloud.sy-bad { background: #fee2e2; color: #dc2626; }

	@keyframes sy-rot { to { transform: rotate(360deg); } }

	.sy-hero h4 { margin: 0 0 4px; font-weight: 800; color: var(--ink); }
	.sy-hero p { margin: 0; color: var(--mut); }

	.sy-pill {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		margin-top: 10px;
		padding: 4px 12px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 700;
		background: var(--ol);
		color: #b45309;
	}

	.sy-pill.sy-ok { background: #e8f8ee; color: #15803d; }
	.sy-pill.sy-bad { background: #fee2e2; color: #b91c1c; }
	.sy-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

	.sy-btn {
		border: 0;
		border-radius: 12px;
		padding: 14px 32px;
		font-size: 16px;
		font-weight: 800;
		color: #fff;
		background: var(--o);
		display: inline-flex;
		align-items: center;
		gap: 10px;
		box-shadow: 0 6px 16px rgba(255, 159, 67, .4);
		cursor: pointer;
		transition: background .15s, transform .15s;
	}

	.sy-btn:hover:not(:disabled) { background: var(--oh); transform: translateY(-1px); }
	.sy-btn:disabled { opacity: .7; cursor: not-allowed; }
	.sy-btn svg { width: 19px; height: 19px; }

	.sy-result { margin-top: 16px; }

	.sy-msg {
		border-radius: 12px;
		padding: 13px 18px;
		font-weight: 700;
		border: 1px solid;
	}

	.sy-msg.success { background: #e8f8ee; color: #15803d; border-color: #bbf0cf; }
	.sy-msg.info { background: var(--ol); color: #b45309; border-color: #ffd9a8; }
	.sy-msg.warning { background: #fff7e6; color: #92400e; border-color: #fde2a8; }
	.sy-msg.danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

	.sy-stats {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 16px;
		margin-top: 18px;
	}

	.sy-stat { padding: 20px; display: flex; align-items: center; gap: 16px; }

	.sy-ico {
		width: 50px;
		height: 50px;
		border-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 50px;
	}

	.sy-ico svg { width: 24px; height: 24px; }
	.sy-ico.o { background: var(--ol); color: var(--oh); }
	.sy-ico.r { background: #fee2e2; color: #dc2626; }
	.sy-ico.g { background: #f1f3f5; color: #495057; }

	.sy-stat small { display: block; color: var(--mut); font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
	.sy-stat b { display: block; font-size: 26px; line-height: 1.2; color: var(--ink); }
	.sy-stat b.sm { font-size: 16px; }
	.sy-stat b.bad { color: #dc2626; }

	.sy-note {
		margin-top: 18px;
		padding: 14px 18px;
		color: var(--mut);
		display: flex;
		gap: 12px;
		align-items: center;
	}

	.sy-note svg { width: 20px; height: 20px; color: var(--oh); flex: 0 0 20px; }

	@media (max-width: 800px) {
		.sy-stats { grid-template-columns: 1fr; }
	}
</style>
@endpush

@section('content')
@php
	$connected = (bool) ($url ?: config('sync.url'));
	$allSafe = $connected && $pending == 0 && $failed == 0;
	$state = ! $connected ? 'bad' : ($allSafe ? 'ok' : 'warn');
@endphp
<div class="page-wrapper">
	<div class="content sy">

		@if (session('status'))
			<div class="alert alert-success">{{ session('status') }}</div>
		@endif
		@if ($errors->any())
			<div class="alert alert-danger">{{ $errors->first() }}</div>
		@endif

		<div class="page-header">
			<div class="page-title">
				<h4>Cloud Sync</h4>
				<h6>Back up your sales and products to the server</h6>
			</div>
		</div>

		<div class="sy-card sy-hero">
			<div class="sy-left">
				<div class="sy-cloud {{ $state === 'ok' ? 'sy-ok' : ($state === 'bad' ? 'sy-bad' : '') }}" id="sy-cloud">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z" />
					</svg>
				</div>
				<div>
					<h4>
						@if (! $connected) Not connected
						@elseif ($allSafe) All data is backed up
						@else Some data is waiting
						@endif
					</h4>
					<p>
						@if (! $connected)
							Cloud sync is not set up in this app.
						@elseif ($allSafe)
							Everything is safely stored on the server.
						@else
							It will upload automatically, or press Sync Now.
						@endif
					</p>
					<span class="sy-pill {{ $state === 'ok' ? 'sy-ok' : ($state === 'bad' ? 'sy-bad' : '') }}">
						<i class="sy-dot"></i>
						{{ ! $connected ? 'Not connected' : ($allSafe ? 'Up to date' : 'Pending upload') }}
					</span>
				</div>
			</div>

			@if ($connected)
				<button type="button" class="sy-btn" id="syncNow">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="23 4 23 10 17 10" />
						<polyline points="1 20 1 14 7 14" />
						<path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
					</svg>
					<span id="sy-btn-text">Sync Now</span>
				</button>
			@endif
		</div>

		<div class="sy-result" id="syncResult"></div>

		<div class="sy-stats">
			<div class="sy-card sy-stat">
				<div class="sy-ico o">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16" /><line x1="12" y1="12" x2="12" y2="21" /><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3" /></svg>
				</div>
				<div><small>Waiting to upload</small><b>{{ $pending }}</b></div>
			</div>
			<div class="sy-card sy-stat">
				<div class="sy-ico r">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
				</div>
				<div><small>Failed</small><b class="{{ $failed > 0 ? 'bad' : '' }}">{{ $failed }}</b></div>
			</div>
			<div class="sy-card sy-stat">
				<div class="sy-ico g">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
				</div>
				<div><small>Last sync</small><b class="sm">{{ $last ?? 'Never' }}</b></div>
			</div>
		</div>

		<div class="sy-card sy-note">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" /></svg>
			<span>Sync runs by itself whenever the internet is on. If you are offline your sales are kept safely and uploaded later.</span>
		</div>

	</div>
</div>
@endsection

@push('scripts_after')
<script>
	(function () {
		var btn = document.getElementById('syncNow');
		if (!btn) return;
		var out = document.getElementById('syncResult');
		var cloud = document.getElementById('sy-cloud');
		var label = document.getElementById('sy-btn-text');

		var MESSAGES = {
			ok: ['success', function (d) { return d.sent ? 'Sync complete. ' + d.sent + ' record(s) uploaded.' : 'Everything is already up to date.'; }],
			busy: ['info', function () { return 'A sync is already running. Please check again in a minute.'; }],
			offline: ['warning', function () { return 'No internet or the server is not reachable. Your data is safe and will upload later.'; }],
			not_approved: ['warning', function () { return 'This computer is waiting for approval from the administrator.'; }],
			not_configured: ['danger', function () { return 'Cloud sync is not set up in this app.'; }],
			error: ['danger', function (d) { return 'Sync failed: ' + (d.message || 'unknown error'); }]
		};

		function show(kind, text) {
			out.innerHTML = '';
			var el = document.createElement('div');
			el.className = 'sy-msg ' + kind;
			el.textContent = text;
			out.appendChild(el);
		}

		btn.addEventListener('click', async function () {
			btn.disabled = true;
			label.textContent = 'Syncing...';
			cloud.classList.add('sy-spin');
			show('info', 'Syncing, please wait...');
			try {
				var r = await fetch('{{ route('sync.run') }}', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
						'Accept': 'application/json'
					}
				});
				var d = await r.json();
				var m = MESSAGES[d.status] || ['info', function () { return JSON.stringify(d); }];
				show(m[0], m[1](d));
				if (d.status === 'ok') setTimeout(function () { location.reload(); }, 1500);
			} catch (e) {
				show('danger', 'Error: ' + e.message);
			}
			cloud.classList.remove('sy-spin');
			label.textContent = 'Sync Now';
			btn.disabled = false;
		});
	})();
</script>
@endpush