@extends('layouts.auth_main')
@section('title', 'Login')

@section('content')
<div>
  <h3 class="mt-8 text-center text-xl font-semibold md:mt-12 lg:mt-24">Login</h3>
  <h3 class="text-base-content/70 mt-2 text-center text-sm">
    Seamless Access, Secure Connection: Your Gateway to a Personalized Experience.
  </h3>
  <div class="mt-3 md:mt-6">
    @include('layouts.alerts')
    <form action="{{ route('login-handle') }}" method="post">
      @csrf
      <fieldset class="fieldset mt-3">
        <legend class="fieldset-legend">Email Address</legend>
        <label class="input w-full focus:outline-0">
          <span class="iconify lucide--mail text-base-content/80 size-5"></span>
          <input class="grow focus:outline-0" placeholder="Email Address" type="email" name="email" required/>
        </label>
      </fieldset>
      <fieldset class="fieldset">
        <legend class="fieldset-legend">Password</legend>
        <label class="input w-full focus:outline-0">
          <span class="iconify lucide--key-round text-base-content/80 size-5"></span>
          <input class="grow focus:outline-0" placeholder="Password" id="password" type="password" name="password" required/>
          <label class="swap btn btn-xs btn-ghost btn-circle text-base-content/60">
            <input type="checkbox" aria-label="Show password" data-password="password" />
            <span class="iconify lucide--eye swap-off size-4"></span>
            <span class="iconify lucide--eye-off swap-on size-4"></span>
          </label>
      </fieldset>
      <div class="text-end">
        <a class="label-text text-base-content/80 text-xs" href="{{ route('forgot-password') }}">
          Forgot Password?
        </a>
      </div>
      <div class="mt-4 flex items-center gap-3 md:mt-6">
        <input class="checkbox checkbox-sm checkbox-primary" aria-label="Checkbox example" id="remember_me" type="checkbox" name="remember_me"/>
        <label for="remember_me" class="text-sm">
          Remember me
        </label>
      </div>
      <button class="btn btn-primary btn-wide mt-4 max-w-full gap-3 md:mt-6" type="submit">
        <span class="iconify lucide--log-in size-4"></span>
        Login
      </button>
    </form>
  </div>
</div>
@endsection