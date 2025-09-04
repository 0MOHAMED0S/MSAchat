<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gradient" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg w-100 d-flex flex-column" style="max-width: 480px; height: 600px; border-radius: 20px;">

        <!-- Header -->
        <div class="card-header d-flex justify-content-between align-items-center text-white"
             style="background: linear-gradient(135deg, #6a11cb 0%, #a4508b 100%); border-top-left-radius: 20px; border-top-right-radius: 20px;">
            <h5 class="mb-0 fw-bold">Chat App</h5>
            <div class="d-flex align-items-center">
                @auth
                <!-- Search Icon -->
                    <a href="#" class="text-white fs-5 me-3"><i class="bi bi-search"></i></a>

                <!-- Logout Form -->
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 m-0 text-white fs-5">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
                @endauth
            </div>
        </div>

        <!-- Body -->
        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center flex-grow-1">
            <h4 class="fw-bold text-dark mb-2">Welcome to Chat App</h4>
            <p class="text-muted mb-4">Stay connected with your friends and colleagues in real time.<br>
            Simple, fast, and secure messaging experience.</p>

            @auth
                <h5 class="fw-bold text-primary mb-2">Hello, {{ auth()->user()->name }} 👋</h5>
                <p class="text-muted">You are successfully logged in!</p>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-dark d-flex align-items-center px-4 py-2">
                    <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" width="22" class="me-2">
                    <span class="fw-bold">Login with Google</span>
                </a>
            @endauth
        </div>

        <!-- Footer -->
        <div class="card-footer text-center text-muted small"
             style="background-color: #f8f9fa; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
            &copy; {{ date('Y') }} Chat App. Built with ❤️ using Laravel & Bootstrap.
        </div>

    </div>
</div>

</body>
</html>
