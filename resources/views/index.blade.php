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

    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
                appId: "{{config('onesignal.app_id')}}",
            });
        });
    </script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
                appId: "{{config('onesignal.app_id')}}",
            });

            // Get user ID when they subscribe
            OneSignal.User.PushSubscription.addEventListener('change', function(event) {
                if (event.current.id) {
                    const oneSignalId = event.current.id;
                    console.log('OneSignal User ID:', oneSignalId);

                    // Send to your backend to store with user profile
                    sendOneSignalIdToBackend(oneSignalId);
                }
            });

            // Also check if user is already subscribed
            const subscription = OneSignal.User.PushSubscription.id;
            if (subscription) {
                console.log('Existing OneSignal User ID:', subscription);
                sendOneSignalIdToBackend(subscription);
            }
        });

        function sendOneSignalIdToBackend(oneSignalId) {
            fetch('/api/save-onesignal-id', {  // Remove the /api prefix
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    onesignal_id: oneSignalId
                })
            });
        }

    </script>

</head>
<body>
<div id="app">
</div>

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
