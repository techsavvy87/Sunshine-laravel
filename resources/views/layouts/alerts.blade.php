@if($errors->any())
  <div class="alert alert-error alert-soft" role="alert">
    <span class="iconify lucide--info size-4"></span>
    <ul style="margin-bottom: 0px">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button class="btn btn-ghost" style="height: 1px; padding: 0px"><span class="iconify lucide--x size-4"></span></button>
  </div>
@endif
@if (session('status') === 'success')
  <div class="alert alert-soft alert-info" role="alert">
    <span class="iconify lucide--info size-4"></span>
    <span>{{session('message')}}</span>
    <button class="btn btn-ghost" style="height: 1px; padding: 0px"><span class="iconify lucide--x size-4"></span></button>
  </div>
@endif
@if (session('status') === 'fail')
  <div class="alert alert-error alert-soft" role="alert">
    <span class="iconify lucide--info size-4"></span>
    <span>{{session('message')}}</span>
    <button class="btn btn-ghost" style="height: 1px; padding: 0px"><span class="iconify lucide--x size-4"></span></button>
  </div>
@endif
<script>
  document.querySelectorAll('.alert .btn-ghost').forEach(button => {
    button.addEventListener('click', function() {
      this.closest('.alert').remove();
    });
  });
</script>