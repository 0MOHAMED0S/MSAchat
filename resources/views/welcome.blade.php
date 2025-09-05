<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>OneSignal Web Push</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- ✅ OneSignal SDK v16 -->
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
      await OneSignal.init({
        appId: "158fd30f-7402-40e9-a094-2553d94e7ab5",
        promptOptions: {
          slidedown: {
            enabled: true, // Enable prompt
            autoPrompt: true // Show automatically after init
          }
        }
      });

      // 🚀 Force immediate prompt (no waiting)
      await OneSignal.Slidedown.promptPush();
    });
  </script>
</head>
<body>
  <h2>OneSignal Instant Subscribe</h2>
</body>
</html>
