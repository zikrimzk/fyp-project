<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-Pasca | Unable to complete request</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card border-0 shadow-sm mx-auto" style="max-width: 680px">
            <div class="card-body p-5 text-center">
                <i class="ti ti-alert-circle text-danger" style="font-size: 64px"></i>
                <h1 class="h3 mt-3">We couldn't complete that request</h1>
                <p class="text-muted">{{ $message }}</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-primary" onclick="history.back()">Go back</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/') }}">Return home</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
