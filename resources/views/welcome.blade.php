<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>OneSignal Auto Subscribe Check</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- OneSignal SDK -->
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
      await OneSignal.init({
        appId: "158fd30f-7402-40e9-a094-2553d94e7ab5",
      });

      // 🔎 Check if user is already subscribed
      const isSubscribed = await OneSignal.User.PushSubscription.optedIn;
      console.log("Already subscribed?", isSubscribed);

      if (!isSubscribed) {
        // 🚀 Auto show alert asking for permission
        setTimeout(() => {
          if (confirm("🔔 Do you want to enable push notifications?")) {
            OneSignal.Slidedown.promptPush(); // shows browser prompt
          }
        }, 1000);
      }
    });
  </script>
</head>
<body>
  <h2>OneSignal Auto Subscribe Test</h2>
</body>
</html>
