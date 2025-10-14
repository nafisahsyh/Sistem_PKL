<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<style>

    .custom {
    color: white !important;
    }

    .bg-brown-custom {
        background-color: #02834E;
    }

    .sidebarToggle {
        color: white; /* mengubah warna ikon menjadi putih */
    }

    .avbarDropdown {
        color: white;
    }
        /* SweetAlert ukuran lebih kecil */
    .swal2-xs {
    max-width: 300px !important; /* Lebar lebih kecil */
    font-size: 12px !important;  /* Ukuran font lebih kecil */
    padding: 10px !important;    /* Padding lebih kecil */
    }
    .custom{
        color: #543A28;
    }




</style>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-brown-custom">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="/dashboard">
       
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars custom"></i></button>
    <!-- Navbar Search-->

    <!-- Navbar-->
    <div class="d-flex justify-content-end w-100">
        <a href="#" onclick="event.preventDefault(); 
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda tidak akan bisa membatalkannya!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, logout!',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                    Swal.fire({
                        title: 'Logout berhasil!',
                        text: 'Anda telah berhasil logout.',
                        icon: 'success'
                    });
                }
            });" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('logoutButton').addEventListener('click', function (e) {
        e.preventDefault(); // Mencegah aksi default tombol
        Swal.fire({
            title: "Apakah Anda yakin ingin logout?",
            text: "Anda harus login kembali untuk mengakses sistem!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Logout",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan notifikasi logout berhasil
                Swal.fire({
                    title: "Berhasil Logout!",
                    text: "Anda telah keluar dari sistem.",
                    icon: "success",
                    timer: 2000, // Notifikasi otomatis hilang setelah 2 detik
                    showConfirmButton: false,
                }).then(() => {
                    // Redirect ke halaman login atau logout setelah notifikasi selesai
                    window.location.href = "/login"; // Sesuaikan dengan rute logout Anda
                });
            }
        });
    });
</script>

<script>
    @if($message = Session::get('success'))
        <script>
            Swal.fire('{{$message}}');
        </script>
    @endif
</script>
<script>
    $(document).ready(function() {
        // Menangani pencarian saat tombol search diklik
        $('#btnNavbarSearch').on('click', function() {
            let query = $('#searchInput').val(); // Ambil input pencarian

            if(query) {
                search(query);
            }
        });

        // Menangani pencarian ketika tombol Enter ditekan
        $('#searchInput').on('keypress', function(e) {
            if (e.which == 13) { // Jika tombol Enter ditekan
                let query = $(this).val();
                if(query) {
                    search(query);
                }
            }
        });

        // Fungsi untuk melakukan pencarian
        function search(query) {
            $.ajax({
                url: '/search', // Ganti dengan URL endpoint pencarian di server
                method: 'GET',
                data: { q: query },
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
