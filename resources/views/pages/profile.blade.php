@extends('layouts.app')

@section('title', 'My profile')

@push('styles')
<style>
	/* ---- Cards (same look as the Users page) ---- */
	.pf-card {
		border: 1px solid #edf0f4;
		border-radius: 12px;
		background: #fff;
		box-shadow: none;
		margin-bottom: 24px;
	}

	.pf-card .card-body {
		padding: 24px;
	}

	.pf-card-title {
		font-size: 16px;
		font-weight: 700;
		color: #1b2230;
		margin: 0;
	}

	.pf-card-sub {
		color: #6b7280;
		font-size: 13px;
		margin: 2px 0 0;
	}

	.pf-card-head {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-bottom: 20px;
	}

	.pf-card-head .pf-icon {
		width: 40px;
		height: 40px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.pf-card-head .pf-icon svg {
		width: 20px;
		height: 20px;
	}

	.pf-icon-details {
		background: #fff4e6;
		color: #ff9f43;
	}

	.pf-icon-password {
		background: #eceafd;
		color: #7367f0;
	}

	/* ---- Identity card ---- */
	.pf-identity {
		text-align: center;
	}

	.pf-avatar {
		width: 110px;
		height: 110px;
		border-radius: 50%;
		margin: 0 auto 16px;
		background: #fff4e6;
		color: #ff9f43;
		font-size: 44px;
		font-weight: 700;
		display: flex;
		align-items: center;
		justify-content: center;
		box-shadow: 0 0 0 4px #fff, 0 0 0 6px #ffe3c4;
	}

	.pf-name {
		font-size: 20px;
		font-weight: 700;
		color: #1b2230;
		margin: 0 0 8px;
	}

	.pf-role {
		display: inline-block;
		padding: 3px 14px;
		border-radius: 20px;
		font-size: 12px;
		font-weight: 600;
	}

	.pf-role-admin {
		background: #eceafd;
		color: #7367f0;
	}

	.pf-role-manager {
		background: #e1f3fc;
		color: #00a2e8;
	}

	.pf-role-cashier {
		background: #e8f7ee;
		color: #28c76f;
	}

	.pf-role-stock {
		background: #fff4e6;
		color: #ff9f43;
	}

	.pf-facts {
		list-style: none;
		margin: 22px 0 0;
		padding: 0;
		text-align: left;
	}

	.pf-facts li {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 12px 0;
		border-top: 1px solid #edf0f4;
	}

	.pf-facts svg {
		width: 18px;
		height: 18px;
		color: #9aa3b2;
		flex-shrink: 0;
	}

	.pf-facts .pf-fact-label {
		color: #6b7280;
		font-size: 12px;
		line-height: 1.2;
	}

	.pf-facts .pf-fact-value {
		color: #1b2230;
		font-weight: 600;
		font-size: 14px;
		word-break: break-word;
	}

	/* ---- Forms ---- */
	.pf-required {
		color: #ea5455;
	}

	.pf-note {
		color: #6b7280;
		font-size: 12px;
		margin-top: 4px;
		display: block;
	}

	.pf-error {
		color: #ea5455;
		font-size: 13px;
		margin-top: 4px;
		display: block;
	}

	.pf-actions {
		display: flex;
		gap: 8px;
		flex-wrap: wrap;
		margin-top: 8px;
	}

	.pf-card input.form-control:disabled {
		background: #f8f9fb;
		color: #6b7280;
	}

	/* ---- Alerts (same as the Users page) ---- */
	.page-alert {
		border-radius: 10px;
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.page-alert svg {
		width: 18px;
		height: 18px;
		flex-shrink: 0;
	}
</style>
@endpush

@section('content')
@php
$user = $user ?? auth()->user();
$initial = mb_strtoupper(mb_substr($user->name, 0, 1));
$hasPasswordErrors = $errors->password->any();
@endphp

<div class="page-wrapper">
	<div class="content">

		{{-- Success message --}}
		@if(session('status'))
		<div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
			<i data-feather="check-circle"></i>
			<span>{{ session('status') }}</span>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		@endif

		<div class="page-header">
			<div class="page-title">
				<h4>My profile</h4>
				<h6>Manage your personal details and password</h6>
			</div>
		</div>

		<div class="row g-4">

			{{-- ===== Left: identity ===== --}}
			<div class="col-xl-4 col-lg-5">
				<div class="card pf-card">
					<div class="card-body pf-identity">

						<div class="pf-avatar">{{ $initial }}</div>

						<h3 class="pf-name">{{ $user->name }}</h3>
						<span class="pf-role pf-role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>

						<ul class="pf-facts">
							<li>
								<i data-feather="mail"></i>
								<div>
									<div class="pf-fact-label">Email</div>
									<div class="pf-fact-value">{{ $user->email }}</div>
								</div>
							</li>
							<li>
								<i data-feather="phone"></i>
								<div>
									<div class="pf-fact-label">Phone</div>
									<div class="pf-fact-value">{{ $user->phone ?: 'Not added yet' }}</div>
								</div>
							</li>
							<li>
								<i data-feather="calendar"></i>
								<div>
									<div class="pf-fact-label">Member since</div>
									<div class="pf-fact-value">{{ $user->created_at?->format('d M Y') }}</div>
								</div>
							</li>
						</ul>

					</div>
				</div>
			</div>

			{{-- ===== Right: forms ===== --}}
			<div class="col-xl-8 col-lg-7">

				{{-- Personal details --}}
				<form method="POST" action="{{ route('profile.update') }}">
					@csrf
					@method('PUT')
					<div class="card pf-card">
						<div class="card-body">
							<div class="pf-card-head">
								<div class="pf-icon pf-icon-details"><i data-feather="user"></i></div>
								<div>
									<h5 class="pf-card-title">Personal details</h5>
									<p class="pf-card-sub">This is how your name appears across the POS.</p>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="name">Full name <span
												class="pf-required">*</span></label>
										<input type="text" id="name" name="name" class="form-control"
											value="{{ old('name', $user->name) }}" required maxlength="255">
										@error('name')<span class="pf-error">{{ $message }}</span>@enderror
									</div>
								</div>
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="phone">Phone</label>
										<input type="text" id="phone" name="phone" class="form-control"
											placeholder="03XXXXXXXXX" inputmode="numeric"
											value="{{ old('phone', $user->phone) }}">
										@error('phone')
										<span class="pf-error">{{ $message }}</span>
										@else
										<span class="pf-note">11 digits, starting with 03.</span>
										@enderror
									</div>
								</div>
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="email">Email</label>
										<input type="email" id="email" class="form-control" value="{{ $user->email }}"
											disabled>
										<span class="pf-note">Only an admin can change your email.</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="role">Role</label>
										<input type="text" id="role" class="form-control"
											value="{{ ucfirst($user->role) }}" disabled>
										<span class="pf-note">Your role decides which pages you can open.</span>
									</div>
								</div>
							</div>

							<div class="pf-actions">
								<button type="submit" class="btn btn-submit">Save changes</button>
								<a href="{{ url('/') }}" class="btn btn-cancel">Cancel</a>
							</div>
						</div>
					</div>
				</form>

				{{-- Change password --}}
				<form id="password-card" method="POST" action="{{ route('profile.password') }}">
					@csrf
					@method('PUT')
					<div class="card pf-card">
						<div class="card-body">
							<div class="pf-card-head">
								<div class="pf-icon pf-icon-password"><i data-feather="lock"></i></div>
								<div>
									<h5 class="pf-card-title">Password</h5>
									<p class="pf-card-sub">Use a password you don't use anywhere else.</p>
								</div>
							</div>

							@if($hasPasswordErrors)
							<div class="alert alert-danger page-alert" role="alert">
								<i data-feather="alert-circle"></i>
								<span>{{ $errors->password->first() }}</span>
							</div>
							@endif

							<div class="row">
								<div class="col-12">
									<div class="input-blocks">
										<label class="form-label" for="current_password">Current password <span
												class="pf-required">*</span></label>
										<div class="pass-group">
											<input type="password" id="current_password" name="current_password"
												class="pass-input form-control" autocomplete="current-password"
												required>
											<span class="fas toggle-password fa-eye-slash"></span>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="password">New password <span
												class="pf-required">*</span></label>
										<div class="pass-group">
											<input type="password" id="password" name="password"
												class="pass-input form-control" autocomplete="new-password" required>
											<span class="fas toggle-password fa-eye-slash"></span>
										</div>
										<span class="pf-note">At least 8 characters.</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="input-blocks">
										<label class="form-label" for="password_confirmation">Confirm new password <span
												class="pf-required">*</span></label>
										<div class="pass-group">
											<input type="password" id="password_confirmation"
												name="password_confirmation" class="pass-input form-control"
												autocomplete="new-password" required>
											<span class="fas toggle-password fa-eye-slash"></span>
										</div>
									</div>
								</div>
							</div>

							<div class="pf-actions">
								<button type="submit" class="btn btn-submit">Update password</button>
							</div>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
	// After a failed password change, bring the password card into view
	@if ($errors -> password -> any())
		document.getElementById('password-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
	@endif
</script>
@endpush