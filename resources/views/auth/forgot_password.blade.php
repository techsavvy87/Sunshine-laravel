@extends('layouts.auth_main')
@section('title', 'Forgot Password')

@section('content')
<div>
  <h3 class="mt-8 text-center text-xl font-semibold md:mt-12 lg:mt-24">
    Forgot Password
  </h3>
  <h3 class="text-base-content/70 mt-2 text-center text-sm">
    First, let's find your account.
  </h3>
  <div class="mt-3 md:mt-6">
    @include('layouts.alerts')
    <form action="{{ route('verify-forgot-password') }}" method="post">
      @csrf
      <fieldset class="fieldset mt-3">
        <legend class="fieldset-legend">Email Address</legend>
        <label class="input w-full focus:outline-0">
          <span class="iconify lucide--mail text-base-content/80 size-5"></span>
          <input class="grow focus:outline-0" placeholder="Email Address" type="email" name="email" required/>
        </label>
      </fieldset>
      <button class="btn btn-primary btn-wide mt-4 max-w-full gap-3 md:mt-6" type="submit">
        <span class="iconify lucide--mail-plus size-4"></span>
        Send a reset link
      </button>
    </form>
    <p class="text-base-content/80 mt-4 text-center text-sm md:mt-6">
      I have already to
      <a class="text-primary ms-1 hover:underline" href="{{ route('login') }}">
        Login
      </a>
    </p>
  </div>
</div>
@endsection