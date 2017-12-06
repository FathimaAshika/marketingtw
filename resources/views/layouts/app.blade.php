<!DOCTYPE html>
<html>
    @include('includes.header')
    <body style="margin-top: 10px; z-index: 1" class="body">
        @yield('content')
        <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
        <div class="row" style="height: 120px">
        </div>
        @include('includes.footer')
    </body>
</html>
