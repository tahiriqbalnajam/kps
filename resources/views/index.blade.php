<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IDLSchool</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#ffffff">
    {{ vite_assets() }}
</head>
<body>
<div id="app">
</div>

<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(function(OneSignal) {
    OneSignal.init({
      appId: "{{ config('services.onesignal.app_id') }}",
      safari_web_id: "web.onesignal.auto.17bc012e-15f1-432a-bc96-18751543883a", // Optional, if you have it
      notifyButton: {
        enable: true,
      },
    });
  });
</script>

@if(config('content.google.open'))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('content.google.id') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }

      gtag('js', new Date());

      gtag('config', '{{ config('content.google.id') }}');
    </script>
@endif
</body>
</html>
