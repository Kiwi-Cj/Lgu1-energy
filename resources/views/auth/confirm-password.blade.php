<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password</title>
</head>
<body>
    <h1>Confirm Password</h1>
    <p>Please confirm your password before continuing.</p>

    @if($errors->any())
        <div>
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>
        <button type="submit">Confirm</button>
    </form>
</body>
</html>
