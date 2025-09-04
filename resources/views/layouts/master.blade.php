<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'ChatApp')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .chat-container {
      height: 80vh;
      max-width: 500px;
      margin: auto;
      display: flex;
      flex-direction: column;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      background: #ffffff;
    }
    .chat-header {
      background: #0d6efd;
      color: white;
      padding: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
      justify-content: space-between;
    }
    .chat-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .chat-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid white;
    }
.logout-icon {
    color: white;
    font-size: 1.3rem;
    cursor: pointer;
    transition: color 0.2s;
}

.logout-icon:hover {
    color: #ffcccc;
}

    .friends-list {
      flex: 1;
      overflow-y: auto;
      padding: 0;
      margin: 0;
      background: #f8f9fa;
    }
    .friend-item {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      border-bottom: 1px solid #e9ecef;
      cursor: pointer;
      transition: background 0.2s;
    }
    .friend-item:hover {
      background: #e9f0ff;
    }
    .friend-item img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }
    .friend-details {
      flex: 1;
    }
    .friend-name {
      margin: 0;
      font-weight: 600;
    }
    .friend-lastmsg {
      margin: 0;
      font-size: 0.9rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .chat-footer {
      text-align: center;
      padding: 10px;
      background: #f1f3f5;
      font-size: 0.9rem;
      color: #6c757d;
    }

  </style>
      @vite(['resources/js/app.js'])

  @stack('styles')
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

  <div class="chat-container">
<!-- Header -->
<div class="chat-header">
    <div class="chat-header-left">
        <img src="@yield('header-img', 'https://i.pravatar.cc/100?img=5')" alt="Chat Avatar">
        <h5 class="mb-0">@yield('header-title', 'Friends List')</h5>
    </div>

    <div class="chat-header-right d-flex align-items-center gap-3">
        <!-- Search Icon -->
        <a href="{{route('friends.index')}}" class="text-white">
            <i class="bi bi-search logout-icon"></i>
        </a>

        <!-- Logout Icon -->
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right logout-icon"></i>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>


    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <div class="chat-footer">
      © 2025 ChatApp | Built with ❤️ using Bootstrap
    </div>
  </div>

  @stack('scripts')
</body>
</html>
