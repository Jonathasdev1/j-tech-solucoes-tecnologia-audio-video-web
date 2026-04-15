<?php
$pageTitle = $pageTitle ?? 'J-Tech Login';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(140deg, rgba(16, 33, 78, 0.94), rgba(15, 140, 128, 0.92)), url("img/imgfundo.jpeg.png") center/cover no-repeat fixed;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #163047;
        }

        .page-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .panel-card {
            width: 100%;
            max-width: 760px;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 24px;
            box-shadow: 0 18px 48px rgba(0, 0, 0, 0.22);
            overflow: hidden;
        }

        .panel-header {
            padding: 28px 32px 18px;
            background: linear-gradient(135deg, #10214e, #0f8c80);
            color: #ffffff;
        }

        .panel-header h1 {
            margin: 0;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
        }

        .panel-header p {
            margin: 8px 0 0;
            opacity: 0.92;
        }

        .panel-body {
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field.full {
            grid-column: 1 / -1;
        }

        .form-field label {
            font-weight: 700;
            color: #10214e;
        }

        .form-field input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #c8d4dc;
            border-radius: 12px;
            font-size: 1rem;
        }

        .form-field input:focus {
            outline: none;
            border-color: #0f8c80;
            box-shadow: 0 0 0 3px rgba(15, 140, 128, 0.14);
        }

        .form-actions {
            margin-top: 28px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-primary {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            background: #0f8c80;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #0c7067;
        }

        .btn-secondary {
            padding: 12px 20px;
            border: 1px solid #10214e;
            border-radius: 12px;
            background: transparent;
            color: #10214e;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: rgba(16, 33, 78, 0.08);
        }

        .helper-text {
            margin: 0 0 20px;
            color: #4c6374;
            line-height: 1.6;
        }

        .status-box {
            padding: 18px 20px;
            border-radius: 16px;
            background: #fff4f4;
            border: 1px solid #f0c7c7;
            color: #8d1f1f;
            font-weight: 600;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .panel-header,
            .panel-body {
                padding: 24px 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/top_nav.php'; ?>
    <main class="page-shell">
        <section class="panel-card">
