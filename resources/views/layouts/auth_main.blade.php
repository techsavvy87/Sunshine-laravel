<!doctype html>
<html lang="en" class="group/html">
  <head>
    <title>@yield('title') - PawPrints</title>
    <meta charset="UTF-8" />
    <meta name="author" content="Denish Navadiya" />
    <meta name="keywords" content="HTML, CSS, daisyui, tailwindcss, admin, client, dashboard, ui kit, component" />
    <meta name="description" content="Start your next project with Nexus, designed for effortless customization to streamline your development process" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-dark.png') }}" media="(prefers-color-scheme: dark)" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-light.png') }}" media="(prefers-color-scheme: light)" />
    @yield('page-css')
    <script>
      try {
        const localStorageItem = localStorage.getItem("__NEXUS_CONFIG_v2.0__")
        if (localStorageItem) {
          const theme = JSON.parse(localStorageItem).theme
          if (theme !== "system") {
            document.documentElement.setAttribute("data-theme", theme)
          }
        }
      } catch (err) {
        console.log(err)
      }
    </script>

    <link href="{{ asset('src/assets/app.css') }}" rel="stylesheet">
  </head>
  <body>
    <div class="grid grid-cols-12 overflow-auto sm:h-screen">
      <div class="relative hidden bg-[#fafbfc] lg:col-span-7 lg:block xl:col-span-8 2xl:col-span-9 dark:bg-[#14181c]">
        <div class="absolute inset-0 flex items-center justify-center">
          <img class="object-cover" alt="Auth Image" src="{{ asset('images/auth-bg.jpg') }}" />
        </div>
        <div class="animate-bounce-2 absolute right-[20%] bottom-[15%]">
          <div class="card bg-base-100/80 w-64 backdrop-blur-lg">
            <div class="card-body p-5">
              <div class="flex flex-col items-center justify-center">
                <div class="mask mask-squircle overflow-hidden">
                  <img class="bg-base-200 size-14" alt="" src="{{ asset('images/landing/testimonial-avatar-1.jpg') }}" />
                </div>
                <div class="mt-3 flex items-center justify-center gap-0.5">
                  <span class="iconify lucide--star size-4 text-orange-600"></span>
                  <span class="iconify lucide--star size-4 text-orange-600"></span>
                  <span class="iconify lucide--star size-4 text-orange-600"></span>
                  <span class="iconify lucide--star size-4 text-orange-600"></span>
                  <span class="iconify lucide--star size-4 text-orange-600"></span>
                </div>
                <p class="mt-1 text-lg font-medium">Sean Verne</p>
                <p class="text-base-content/60 text-sm">Creator of PawPrints</p>
              </div>
              <p class="mt-2 text-center text-sm">
                This is the ultimate way to track your services
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-span-12 lg:col-span-5 xl:col-span-4 2xl:col-span-3">
        <div class="flex flex-col items-stretch p-6 md:p-8 lg:p-16">
          <div class="flex items-center justify-between">
            <a href="./dashboards-ecommerce.html">
              <img alt="logo-dark" class="hidden dark:inline" src="{{ asset('images/logo.png') }}" width="140"/>
              <img alt="logo-light" class="dark:hidden" src="{{ asset('images/logo.png') }}" width="140"/>
            </a>
            <div class="dropdown dropdown-end">
              <div tabindex="0" role="button" class="btn btn-circle btn-outline border-base-300" aria-label="Theme toggle">
                <span class="iconify lucide--sun hidden size-4 group-data-[theme=light]/html:inline"></span>
                <span class="iconify lucide--moon hidden size-4 group-data-[theme=dark]/html:inline"></span>
                <span class="iconify lucide--monitor hidden size-4 group-[:not([data-theme])]/html:inline"></span>
                <span class="iconify lucide--palette hidden size-4 group-data-[theme=contrast]/html:inline group-data-[theme=dim]/html:inline group-data-[theme=material]/html:inline group-data-[theme=material-dark]/html:inline"></span>
              </div>
              <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-36 space-y-0.5 p-1 shadow-sm">
                <li>
                  <div data-theme-control="light" class="group-data-[theme=light]/html:bg-base-200 flex gap-2">
                    <span class="iconify lucide--sun size-4.5"></span>
                    <span class="font-medium">Light</span>
                  </div>
                </li>
                <li>
                  <div data-theme-control="dark" class="group-data-[theme=dark]/html:bg-base-200 flex gap-2">
                    <span class="iconify lucide--moon size-4.5"></span>
                    <span class="font-medium">Dark</span>
                  </div>
                </li>
                <li>
                  <div data-theme-control="system" class="group-[:not([data-theme])]/html:bg-base-200 flex gap-2">
                    <span class="iconify lucide--monitor size-4.5"></span>
                    <span class="font-medium">System</span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          @yield('content')
        </div>
      </div>
    </div>

    <script src="{{ asset('src/js/components/password-field.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.2.7/simplebar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.2.7/simplebar.css" />
    <script src="{{ asset('src/js/jquery.min.js') }}"></script>
    <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
    <script src="{{ asset('src/js/app.js') }}"></script>

    @yield('page-js')
  </body>
</html>