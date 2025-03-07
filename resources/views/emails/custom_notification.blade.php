<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        p {
            color: #7f8c8d;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .button-wrapper {
            text-align: center;
        }
        .custom-button {
            background-color: #ff4500;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }
        .custom-button:hover {
            background-color: #e03e00;
        }
    </style>
</head>
<body>
<div class="email-container">
    <h1>Hello, {{ $user->name }}!</h1>
    <p>Thank you for registering!</p>

    <div class="button-wrapper">
        <a href="{{ config('services.front_url') }}" class="custom-button" target="_blank" rel="noopener">
            Go to website
        </a>
    </div>
</div>
</body>
</html>
