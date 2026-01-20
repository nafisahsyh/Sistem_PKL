<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-brown-custom">
    <!-- Navbar Brand dengan logo berlatarkan lengkung -->
    <a class="navbar-brand ps-3 d-flex align-items-center" href="{{ auth()->user()->role == 'super_admin' ? route('dashboard.super') : route('dashboard.admin') }}">
        <div class="logo-container mr-2">
            <img src="{{ asset('logo.webp') }}" alt="logo">
        </div>
        <span class="brand-text">SisPlasma</span>
    </a>

    <!-- Sidebar Toggle -->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars custom"></i>
    </button>

    <!-- Navbar Right (Logout) -->
    <div class="ml-auto pr-3">
        <a href="#" onclick="event.preventDefault(); confirmLogout();" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</nav>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogout() {
        Swal.fire({
            title: "<h3 style='font-size:15px;margin-bottom:5px;'>Apakah anda yakin ingin logout?</h3>",
            icon: "warning",
            iconColor: '#dc3545',
            showCancelButton: true,
            confirmButtonColor: "#198754",
            cancelButtonColor: "#dc3545",
            confirmButtonText: "Logout",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                // Langsung submit form logout tanpa notifikasi tambahan
                document.getElementById('logoutForm').submit();
            }
        });
    }
</script>


<script>
    $(document).ready(function() {
        // Menangani pencarian saat tombol search diklik
        $('#btnNavbarSearch').on('click', function() {
            let query = $('#searchInput').val(); // Ambil input pencarian

            if (query) {
                search(query);
            }
        });

        // Menangani pencarian ketika tombol Enter ditekan
        $('#searchInput').on('keypress', function(e) {
            if (e.which == 13) { // Jika tombol Enter ditekan
                let query = $(this).val();
                if (query) {
                    search(query);
                }
            }
        });

        // Fungsi untuk melakukan pencarian
        function search(query) {
            $.ajax({
                url: '/search', // Ganti dengan URL endpoint pencarian di server
                method: 'GET',
                data: {
                    q: query
                },
                success: function(response) {
                    // Misalnya Anda bisa menampilkan hasil pencarian di suatu bagian halaman
                    // Ganti #searchResults dengan elemen tempat hasil pencarian ditampilkan
                    $('#searchResults').html(response);
                },
                error: function() {
                    alert('Terjadi kesalahan dalam pencarian.');
                }
            });
        }
    });
</script>
