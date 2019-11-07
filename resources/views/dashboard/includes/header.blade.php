<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Zero Collateral, Instant Loan - NodCredit</title>
    <meta name="description" content="Fast & Easy Loan with Low Interest Rate, Zero Collateral">
    <meta name="keywords" content="Zero Collateral, NodCredit, Cheap Loan, Instant Loan, Nigeria Instant loan, Nigeria school loan, Nigeria cheapest loan">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <!-- Favicon -->
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="shortcut icon" href="{{asset('dashboard/favicon.ico')}}" type="image/x-icon">
    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{asset('dashboard/css/style.min.css?v=1.0')}}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('dashboard/css/custom-style.css')}}?v={{ filemtime(public_path('dashboard/css/custom-style.css')) }}">
    <link rel="stylesheet" href="{{asset('css/styles.css')}}?v={{ filemtime(public_path('css/styles.css')) }}">

    <style>
      .ui-alert{
        background-color: #2a2a2a;
        color: #FFF;
        position: absolute;
        z-index: 9999;
        width: 100%;
        padding: 20px;
        text-align: center;
        bottom: 0;
        right: 0;
      }

      .ui-alert-success{
        background-color: #2fca74;
      }

      .ui-alert-danger{
        background-color: #ee4343;
      }
      .alert-danger{
        color: red;
      }

      .nav-btn{
        margin-right: 10px;
      }
    </style>

    @includeWhen(\App::environment() === 'production', '_partials.ogatracker')

    @includeWhen(\App::environment() === 'production', '_partials.gtag')

  </head>
