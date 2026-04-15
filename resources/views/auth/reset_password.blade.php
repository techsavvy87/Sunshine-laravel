@extends('layouts.auth_main')
@section('title', 'Forgot Password')

@section('content')
<div>
  <h3 class="mt-8 text-center text-xl font-semibold md:mt-12 lg:mt-24">
    Reset Password
  </h3>
  <h3 class="text-base-content/70 mt-2 text-center text-sm">
    Please enter your new password.
  </h3>
  <div class="mt-3 md:mt-6">
    @include('layouts.alerts')
    <form action="{{ route('reset-password') }}" method="post" id="reset_password_form">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <fieldset class="fieldset mt-3">
        <legend class="fieldset-legend">Password</legend>
        <label class="input w-full focus:outline-0">
          <span class="iconify lucide--key-round text-base-content/80 size-5"></span>
          <input class="grow focus:outline-0" placeholder="Password" id="password" type="password" name="new_password" required/>
          <label class="swap btn btn-xs btn-ghost btn-circle text-base-content/60">
            <input type="checkbox" aria-label="Show password" data-password="password" />
            <span class="iconify lucide--eye swap-off size-4"></span>
            <span class="iconify lucide--eye-off swap-on size-4"></span>
          </label>
        </label>
      </fieldset>
      <fieldset class="fieldset">
        <legend class="fieldset-legend">Confirm Password</legend>
        <label class="input w-full focus:outline-0">
          <span class="iconify lucide--key-round text-base-content/80 size-5"></span>
          <input class="grow focus:outline-0" placeholder="Password" id="confirm-password" type="password" name="confirm_password" required/>
          <label class="swap btn btn-xs btn-ghost btn-circle text-base-content/60">
            <input type="checkbox" aria-label="Show password" data-password="confirm-password" />
            <span class="iconify lucide--eye swap-off size-4"></span>
            <span class="iconify lucide--eye-off swap-on size-4"></span>
          </label>
        </label>
      </fieldset>
      <button class="btn btn-primary btn-wide mt-4 max-w-full gap-3 md:mt-6" type="submit">
        <span class="iconify lucide--check size-4"></span>
        Change Password
      </button>
    </form>
    <p class="mt-4 text-center text-sm md:mt-6">
      Go to
      <a class="text-primary ms-1.5 hover:underline" href="{{ route('login') }}">
        Login
      </a>
    </p>
  </div>
</div>
@endsection