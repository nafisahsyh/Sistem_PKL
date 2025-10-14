<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-brown-custom">
    <!-- Navbar Brand dengan logo berlatarkan lengkung -->
    <a class="navbar-brand ps-3 d-flex align-items-center" href="/dashboard">
        <div class="logo-container mr-2">
            <img src="{{ asset('/storage/img/logo.png') }}" alt="logo">
        </div>
        <span class="brand-text">SisPlasma</span>
    </a>

    <!-- Sidebar Toggle -->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" 
            id="sidebarToggle" 
            href="#!">
        <i class="fas fa-bars custom"></i>
    </button>

    <!-- Navbar Right (Logout) -->
    <div class="ml-auto pr-3">
        <a href="#" 
           onclick="event.preventDefault(); confirmLogout();" 
           class="btn btn-sm btn-danger">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

<!-- SCRIPT -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Anda tidak akan bisa membatalkannya!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, logout!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
                Swal.fire({
                    title: 'Logout berhasil!',
                    text: 'Anda telah berhasil logout.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }
</script>

<script>
    // Pesan sukses otomatis dari session
    @if($message = Session::get('success'))
        Swal.fire('{{$message}}');
    @endif
</script>

<script>
    $(document).ready(function() {
        // Fungsi pencarian (jika ada input search di navbar)
        $('#btnNavbarSearch').on('click', function() {
            let query = $('#searchInput').val();
            if (query) search(query);
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which == 13) {
                let query = $(this).val();
                if (query) search(query);
            }
        });

        function search(query) {
            $.ajax({
                url: '/search',
                method: 'GET',
                data: { q: query },
                success: function(response) {
                    $('#searchResults').html(response);
                },
                error: function() {
                    alert('Terjadi kesalahan dalam pencarian.');
                }
            });
        }
    });
</script>
