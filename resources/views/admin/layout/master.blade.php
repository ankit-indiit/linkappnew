<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Link</title>
        <link rel="shortcut icon" href="{{ asset('public/assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/bootstrap-datetimepicker.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fullcalendar/fullcalendar.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/animate.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/nice-select.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.css'">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Spartan:wght@300;400;500;600;700&display=swap">
        <link href="{{ asset('public/assets/css/summernote-bs4.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('public/assets/css/admin.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/responsive.css') }}">
        <script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>
    </head>
    <body>
        <div class="main-wrapper">

        @include('admin.layout.header')
        
        @include('admin.layout.sidebar')

        @yield('content')

        @include('admin.component.custom-modal')

        <script src="{{ asset('public/assets/js/jquery.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/popper.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
        {{-- <script src="{{ asset('public/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script> --}}
        <script src="{{ asset('public/assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/moment.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ asset('public/assets/plugins/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/jquery-ui.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.3.1/fullcalendar.js"></script>
        <script src="{{ asset('public/assets/plugins/fullcalendar/jquery.fullcalendar.js') }}"></script>
        <script src="{{ asset('public/assets/js/jquery.nice-select.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js'"></script>
        <script src="{{ asset('public/assets/js/admin.js') }}"></script>
        <script src="{{ asset('public/assets/js/summernote-bs4.min.js') }}"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>       
        <script type="text/javascript">
            @if (Session::has('success'))
                swal({
                  position: 'top-end',
                  icon: 'success',
                  title: "{{ Session::get('success') }}",
                  showConfirmButton: false,
                  // timer: 1500
                })
            @endif
            @if (Session::has('error'))
                swal({
                  icon: 'error',
                  title: 'Oops...',
                  text: "{{ Session::get('error') }}",
                  footer: '<a href="">Why do I have this issue?</a>'
                })
            @endif
            $(document).ready(function () {
                $('.my-select').niceSelect();
            });

            $('.label.ui.dropdown')
                .dropdown();

            $('.no.label.ui.dropdown')
                .dropdown({
                    useLabels: false
                });

            $('.ui.button').on('click', function () {
                $('.ui.dropdown')
                    .dropdown('restore defaults')
            })

            $('#summernote').summernote({
                tabsize: 2,
                height: 200
            });
            $('#summernote2').summernote({
                tabsize: 2,
                height: 200
            });
            $('#summernote3').summernote({
                tabsize: 2,
                height: 200
            });
        </script>

        @yield('customScript')
    </body>
</html>