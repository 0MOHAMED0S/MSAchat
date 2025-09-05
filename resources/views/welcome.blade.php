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
      });

      // Button click will show native push permission prompt
      document.getElementById("subscribeBtn").addEventListener("click", async () => {
        await OneSignal.Slidedown.promptPush();
      });
    });
  </script>
</head>
<body>
  <h2>Click to Subscribe for Notifications</h2>
  <button id="subscribeBtn">Subscribe Now</button>
</body>
</html>
