<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSA Chat</title>

    <!-- App Icon (Favicon) -->
    <link rel="icon" type="image/png" href="{{ asset('chat (1).png') }}">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/js/app.js'])

    <style>
        body {
            background: linear-gradient(135deg, #c5c5c5 0%, #2575fca8 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #a4508b 100%);
            padding: 15px 20px;
        }

        .card-header h5 {
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .header-icons a,
        .header-icons button {
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .header-icons a:hover,
        .header-icons button:hover {
            transform: scale(1.15);
            opacity: 0.8;
        }

        .card-footer {
            background-color: #f8f9fa;
            font-size: 0.85rem;
            padding: 10px;
        }

        /* User info side by side */
        .user-info {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .user-info img {
            width: 28px;
            height: 28px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 6px;
        }

        .user-info span {
            color: #fff;
            font-weight: 500;
            white-space: nowrap;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Responsive: center user info on small screens */
        @media (max-width: 576px) {
            .header-icons {
                flex-direction: column;
                align-items: center;
            }

            .user-info {
                justify-content: center;
            }
        }
    </style>
    @yield('styles')
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg w-100 d-flex flex-column" style="max-width: 600px; min-height: 600px;">

            <!-- Header -->
            <div class="card-header d-flex justify-content-between align-items-center text-white flex-wrap">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-chat-dots-fill me-2"></i> MSA Chat
                </h5>
                <div class="header-icons d-flex flex-column align-items-center text-center">

                    <!-- Icons -->
                    <div class="d-flex align-items-center mb-1">
                        <a href="{{ route('chat.index') }}" class="text-white fs-5 me-3">
                            <i class="bi bi-house-door-fill"></i>
                        </a>
                        <a href="{{ route('chat.users') }}" class="text-white fs-5 me-3">
                            <i class="bi bi-people"></i>
                        </a>

                        @auth
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 m-0 text-white fs-5" title="Logout">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            </form>
                        @endauth
                    </div>

                    <!-- User Info (side by side, small) -->
                    @auth
                        <div class="user-info">
                            <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}"
                                alt="Avatar">
                            <span>{{ Auth::user()->name }}</span>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Body -->
            <div class="card-body flex-grow-1 bg-light">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="card-footer text-center text-muted small">
                &copy; {{ date('Y') }} MSA Chat. All rights reserved.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>
