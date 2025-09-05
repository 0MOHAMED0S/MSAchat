importScripts('https://www.gstatic.com/firebasejs/9.6.11/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.11/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyBmHHmGCGIDvjoESjITK6P-Q2ZI0-LRiiE",
  authDomain: "homeservice-3bd86.firebaseapp.com",
  projectId: "homeservice-3bd86",
  storageBucket: "homeservice-3bd86.appspot.com",
  messagingSenderId: "1021870946240",
  appId: "1:1021870946240:web:8b00e53edbe81d27e27a25",
  measurementId: "G-LPFHQB5FHE"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/firebase-logo.png' // optional
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
