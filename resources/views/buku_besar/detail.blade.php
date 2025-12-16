<table class="table table-bordered table-sm">
    <thead class="text-center">
        <tr>
            <th>No</th>
            <th>Lahan</th>
            <th>Bulan</th>
            <th>Luas (Ha)</th>
            <th>Nominal (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tabelData as $row)
            <tr>
                <td class="text-center">{{ $row['no'] }}</td>
                <td class="text-center">{{ $row['lahan'] }}</td>
                <td class="text-center">{{ $row['bulan'] }}</td>
                <td class="text-center">{{ number_format($row['luas'], 2,',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($row['nominal'], 2, ',', '.') }}</td>
            </tr>
        @endforeach

        @if (count($tabelData) === 0)
            <tr>
                <td colspan="5" class="text-center">Data tidak ditemukan</td>
            </tr>
        @endif
    </tbody>
</table>
