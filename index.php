<?php
// ============================================================
// APLIKASI PENGHITUNG HPP
// PHP Native - Tanpa Database
// ============================================================

function rupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Nilai default
$hasil = null;
$error = null;

// Data yang dikirim dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaProduk = trim($_POST['nama_produk'] ?? '');
    $jumlahProduk = (int) ($_POST['jumlah_produk'] ?? 0);
    $keuntungan = (float) ($_POST['keuntungan'] ?? 0);

    $bahanNama = $_POST['bahan_nama'] ?? [];
    $bahanHarga = $_POST['bahan_harga'] ?? [];
    $bahanJumlah = $_POST['bahan_jumlah'] ?? [];

    $tenagaNama = $_POST['tenaga_nama'] ?? [];
    $tenagaBiaya = $_POST['tenaga_biaya'] ?? [];

    $overheadNama = $_POST['overhead_nama'] ?? [];
    $overheadBiaya = $_POST['overhead_biaya'] ?? [];

    // --------------------------------------------------------
    // Validasi
    // --------------------------------------------------------

    if ($namaProduk === '') {
        $error = 'Nama produk harus diisi.';
    } elseif ($jumlahProduk <= 0) {
        $error = 'Jumlah produk harus lebih dari 0.';
    } elseif ($keuntungan < 0) {
        $error = 'Persentase keuntungan tidak boleh negatif.';
    } else {

        // ----------------------------------------------------
        // Hitung Bahan Baku
        // ----------------------------------------------------

        $totalBahan = 0;

        foreach ($bahanHarga as $i => $harga) {

            $harga = (float) $harga;
            $jumlah = (float) ($bahanJumlah[$i] ?? 0);

            if ($harga >= 0 && $jumlah > 0) {
                $totalBahan += $harga * $jumlah;
            }
        }

        // ----------------------------------------------------
        // Hitung Tenaga Kerja
        // ----------------------------------------------------

        $totalTenaga = 0;

        foreach ($tenagaBiaya as $biaya) {

            $biaya = (float) $biaya;

            if ($biaya > 0) {
                $totalTenaga += $biaya;
            }
        }

        // ----------------------------------------------------
        // Hitung Overhead
        // ----------------------------------------------------

        $totalOverhead = 0;

        foreach ($overheadBiaya as $biaya) {

            $biaya = (float) $biaya;

            if ($biaya > 0) {
                $totalOverhead += $biaya;
            }
        }

        // ----------------------------------------------------
        // Perhitungan HPP
        // ----------------------------------------------------

        $totalProduksi = $totalBahan + $totalTenaga + $totalOverhead;

        $hpp = $totalProduksi / $jumlahProduk;

        // Harga jual berdasarkan keuntungan
        $hargaJual = $hpp + ($hpp * $keuntungan / 100);

        $totalPendapatan = $hargaJual * $jumlahProduk;
        $totalKeuntungan = $totalPendapatan - $totalProduksi;

        $hasil = [
            'nama_produk' => $namaProduk,
            'jumlah_produk' => $jumlahProduk,
            'keuntungan' => $keuntungan,

            'total_bahan' => $totalBahan,
            'total_tenaga' => $totalTenaga,
            'total_overhead' => $totalOverhead,

            'total_produksi' => $totalProduksi,
            'hpp' => $hpp,
            'harga_jual' => $hargaJual,

            'total_pendapatan' => $totalPendapatan,
            'total_keuntungan' => $totalKeuntungan
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HPP Calculator</title>

    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(0, 255, 200, .08), transparent 35%),
                radial-gradient(circle at bottom left, rgba(100, 100, 255, .08), transparent 35%),
                #080b12;

            color: #f4f7fb;
            min-height: 100vh;
        }

        /* =====================================================
           CONTAINER
        ===================================================== */

        .container {
            width: min(1150px, 92%);
            margin: auto;
            padding: 40px 0;
        }

        /* =====================================================
           HEADER
        ===================================================== */

        .header {
            margin-bottom: 35px;
        }

        .badge {
            display: inline-block;
            padding: 7px 13px;
            border: 1px solid rgba(0, 255, 200, .3);
            background: rgba(0, 255, 200, .05);
            color: #57ffe0;
            border-radius: 30px;
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: clamp(32px, 5vw, 52px);
            line-height: 1;
            margin-bottom: 12px;
        }

        .header h1 span {
            color: #57ffe0;
        }

        .header p {
            color: #8d96a8;
            max-width: 650px;
            line-height: 1.7;
        }

        /* =====================================================
           GRID
        ===================================================== */

        .grid {
            display: grid;
            grid-template-columns: 1.4fr .8fr;
            gap: 22px;
            align-items: start;
        }

        /* =====================================================
           CARD
        ===================================================== */

        .card {
            background: rgba(16, 21, 32, .85);
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 18px;
            padding: 24px;

            box-shadow:
                0 15px 50px rgba(0, 0, 0, .25);

            backdrop-filter: blur(15px);
        }

        .card + .card {
            margin-top: 20px;
        }

        .card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title h2 {
            font-size: 18px;
        }

        .card-title span {
            color: #687286;
            font-size: 12px;
        }

        /* =====================================================
           FORM
        ===================================================== */

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            color: #aab2c1;
            font-size: 13px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px 14px;

            background: #0b0f18;
            border: 1px solid #202838;

            color: white;
            border-radius: 10px;
            outline: none;

            transition: .2s;
        }

        input:focus {
            border-color: #57ffe0;

            box-shadow:
                0 0 0 3px rgba(87, 255, 224, .08);
        }

        /* =====================================================
           ITEM ROW
        ===================================================== */

        .item {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr auto;
            gap: 10px;

            margin-bottom: 10px;
        }

        .item.three {
            grid-template-columns: 1.5fr 1fr auto;
        }

        .remove {
            width: 42px;
            border: none;
            border-radius: 10px;

            background: rgba(255, 70, 100, .08);
            border: 1px solid rgba(255, 70, 100, .15);

            color: #ff7189;
            cursor: pointer;

            transition: .2s;
        }

        .remove:hover {
            background: rgba(255, 70, 100, .18);
        }

        /* =====================================================
           BUTTON
        ===================================================== */

        button {
            font-family: inherit;
        }

        .add-btn {
            margin-top: 7px;

            background: transparent;
            color: #57ffe0;

            border: 1px dashed rgba(87, 255, 224, .4);

            padding: 10px 14px;
            border-radius: 9px;

            cursor: pointer;

            transition: .2s;
        }

        .add-btn:hover {
            background: rgba(87, 255, 224, .07);
        }

        .calculate {
            width: 100%;

            margin-top: 10px;
            padding: 15px;

            border: none;
            border-radius: 11px;

            background: #57ffe0;
            color: #06100e;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;

            transition: .2s;
        }

        .calculate:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 30px rgba(87, 255, 224, .15);
        }

        /* =====================================================
           RESULT
        ===================================================== */

        .result-card {
            position: sticky;
            top: 20px;
        }

        .result-main {
            padding: 22px;

            background:
                linear-gradient(
                    135deg,
                    rgba(87, 255, 224, .12),
                    rgba(87, 255, 224, .025)
                );

            border: 1px solid rgba(87, 255, 224, .15);

            border-radius: 15px;

            margin-bottom: 18px;
        }

        .result-main small {
            color: #8d96a8;
        }

        .result-main h3 {
            font-size: 34px;
            color: #57ffe0;
            margin-top: 7px;
        }

        .result-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .result-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;

            padding-bottom: 12px;

            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .result-row:last-child {
            border-bottom: none;
        }

        .result-row span:first-child {
            color: #858ea0;
        }

        .result-row span:last-child {
            font-weight: bold;
            text-align: right;
        }

        .profit {
            color: #57ffe0;
        }

        /* =====================================================
           ERROR
        ===================================================== */

        .error {
            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 10px;

            background: rgba(255, 70, 100, .08);
            border: 1px solid rgba(255, 70, 100, .2);

            color: #ff8a9c;

            font-size: 14px;
        }

        /* =====================================================
           EMPTY RESULT
        ===================================================== */

        .empty {
            color: #687286;
            text-align: center;

            padding: 40px 10px;

            line-height: 1.7;
        }

        .empty-icon {
            font-size: 35px;
            margin-bottom: 12px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .grid {
                grid-template-columns: 1fr;
            }

            .result-card {
                position: static;
            }
        }

        @media (max-width: 600px) {

            .container {
                padding: 25px 0;
            }

            .card {
                padding: 18px;
            }

            .item,
            .item.three {
                grid-template-columns: 1fr 1fr;
            }

            .item input:first-child {
                grid-column: span 2;
            }

            .remove {
                width: 100%;
                height: 42px;
            }
        }

    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">

        <div class="badge">
            HPP CALCULATOR / PHP
        </div>

        <h1>
            Hitung <span>HPP</span>
            lebih mudah.
        </h1>

        <p>
            Kalkulator Harga Pokok Produksi untuk menghitung
            total biaya produksi, HPP per produk, dan estimasi
            harga jual secara cepat.
        </p>

    </div>


    <div class="grid">

        <!-- =================================================
             KOLOM INPUT
        ================================================== -->

        <div>

            <?php if ($error): ?>

                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST" id="hppForm">

                <!-- PRODUK -->
                <div class="card">

                    <div class="card-title">
                        <h2>Informasi Produk</h2>
                        <span>01</span>
                    </div>

                    <div class="form-group">

                        <label>Nama Produk</label>

                        <input
                            type="text"
                            name="nama_produk"
                            placeholder="Contoh: Kopi Susu"
                            value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Jumlah Produk yang Diproduksi</label>

                        <input
                            type="number"
                            name="jumlah_produk"
                            min="1"
                            placeholder="Contoh: 100"
                            value="<?= htmlspecialchars($_POST['jumlah_produk'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Target Keuntungan (%)</label>

                        <input
                            type="number"
                            name="keuntungan"
                            min="0"
                            step="0.01"
                            placeholder="Contoh: 30"
                            value="<?= htmlspecialchars($_POST['keuntungan'] ?? '30') ?>"
                            required
                        >

                    </div>

                </div>


                <!-- BAHAN BAKU -->
                <div class="card">

                    <div class="card-title">
                        <h2>Bahan Baku</h2>
                        <span>02</span>
                    </div>

                    <div id="bahanContainer">

                        <?php

                        $bahanCount = max(
                            count($_POST['bahan_nama'] ?? []),
                            1
                        );

                        for ($i = 0; $i < $bahanCount; $i++):

                        ?>

                        <div class="item">

                            <input
                                type="text"
                                name="bahan_nama[]"
                                placeholder="Nama bahan"
                                value="<?= htmlspecialchars($_POST['bahan_nama'][$i] ?? '') ?>"
                            >

                            <input
                                type="number"
                                name="bahan_harga[]"
                                placeholder="Harga"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($_POST['bahan_harga'][$i] ?? '') ?>"
                            >

                            <input
                                type="number"
                                name="bahan_jumlah[]"
                                placeholder="Jumlah"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($_POST['bahan_jumlah'][$i] ?? '') ?>"
                            >

                            <button
                                type="button"
                                class="remove"
                                onclick="hapusItem(this)"
                            >
                                ×
                            </button>

                        </div>

                        <?php endfor; ?>

                    </div>


                    <button
                        type="button"
                        class="add-btn"
                        onclick="tambahBahan()"
                    >
                        + Tambah bahan
                    </button>

                </div>


                <!-- TENAGA KERJA -->
                <div class="card">

                    <div class="card-title">
                        <h2>Tenaga Kerja</h2>
                        <span>03</span>
                    </div>

                    <div id="tenagaContainer">

                        <?php

                        $tenagaCount = max(
                            count($_POST['tenaga_nama'] ?? []),
                            1
                        );

                        for ($i = 0; $i < $tenagaCount; $i++):

                        ?>

                        <div class="item three">

                            <input
                                type="text"
                                name="tenaga_nama[]"
                                placeholder="Contoh: Upah produksi"
                                value="<?= htmlspecialchars($_POST['tenaga_nama'][$i] ?? '') ?>"
                            >

                            <input
                                type="number"
                                name="tenaga_biaya[]"
                                placeholder="Biaya"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($_POST['tenaga_biaya'][$i] ?? '') ?>"
                            >

                            <button
                                type="button"
                                class="remove"
                                onclick="hapusItem(this)"
                            >
                                ×
                            </button>

                        </div>

                        <?php endfor; ?>

                    </div>


                    <button
                        type="button"
                        class="add-btn"
                        onclick="tambahTenaga()"
                    >
                        + Tambah biaya tenaga kerja
                    </button>

                </div>


                <!-- OVERHEAD -->
                <div class="card">

                    <div class="card-title">
                        <h2>Biaya Overhead</h2>
                        <span>04</span>
                    </div>

                    <div id="overheadContainer">

                        <?php

                        $overheadCount = max(
                            count($_POST['overhead_nama'] ?? []),
                
