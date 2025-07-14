<?php 
include 'header.php';
$kd = mysqli_real_escape_string($conn,$_GET['kode_cs']);
$cs = mysqli_query($conn, "SELECT * FROM customer WHERE kode_customer = '$kd'");
$rows = mysqli_fetch_assoc($cs);
?>

<div class="container" style="padding-bottom: 200px">
	<h2 style="width: 100%; border-bottom: 4px solid #ff8680"><b>Checkout</b></h2>

	<!-- Tabel Daftar Pesanan -->
	<div class="row">
		<div class="col-md-6">
			<h4>Daftar Pesanan</h4>
			<table class="table table-striped">
				<tr>
					<th>No</th>
					<th>Nama</th>
					<th>Harga per pcs</th>
					<th>Diskon (%)</th>
					<th>Qty</th>
					<th>Sub Total</th>
				</tr>
				<?php 
				$result = mysqli_query($conn, "SELECT * FROM keranjang WHERE kode_customer = '$kd'");
				$no = 1;
				$hasil = 0;
				while($row = mysqli_fetch_assoc($result)){
					$diskon = 10;
					$harga_awal = $row['harga'];
					$harga_diskon = $harga_awal - ($harga_awal * $diskon / 100);
					$subtotal = $harga_diskon * $row['qty'];
					$hasil += $subtotal;
				?>
					<tr>
						<td><?= $no++; ?></td>
						<td><?= $row['nama_produk']; ?></td>
						<td>Rp.<?= number_format($harga_awal); ?></td>
						<td><?= $diskon; ?>%</td>
						<td><?= $row['qty']; ?></td>
						<td>Rp.<?= number_format($subtotal); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="6" style="text-align: right; font-weight: bold;">
						Grand Total = Rp.<?= number_format($hasil); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Info -->
	<div class="row">
		<div class="col-md-6 bg-success" style="padding: 10px;">
			<h5>✅ Pastikan Pesanan Anda Sudah Benar</h5>
		</div>
	</div><br>

	<div class="row">
		<div class="col-md-6 bg-warning" style="padding: 10px;">
			<h5>📝 Isi Form di bawah ini</h5>
		</div>
	</div><br>

	<!-- Form Checkout -->
	<form id="checkoutForm" action="proses/order.php" method="POST">
		<input type="hidden" name="kode_cs" value="<?= $kd; ?>">
		<div class="form-group">
			<label>Nama</label>
			<input type="text" class="form-control" name="nama" value="<?= $rows['nama']; ?>" readonly>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Provinsi</label>
					<input type="text" class="form-control" name="prov" required>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Kota</label>
					<input type="text" class="form-control" name="kota" required>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Alamat</label>
					<input type="text" class="form-control" name="almt" required>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Kode Pos</label>
					<input type="text" class="form-control" name="kopos" required>
				</div>

				<div class="form-group">
					<label>Metode Pembayaran</label><br>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="metode_pembayaran" value="ShopeePay" required>
						<label class="form-check-label">ShopeePay</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="metode_pembayaran" value="Tunai">
						<label class="form-check-label">Tunai</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="metode_pembayaran" value="Transfer Bank">
						<label class="form-check-label">Transfer Bank</label>
					</div>
				</div>
			</div>
		</div>

		<!-- Tombol -->
		<button type="button" class="btn btn-success" onclick="handleOrder()">
			<i class="glyphicon glyphicon-shopping-cart"></i> Order Sekarang
		</button>
		<a href="keranjang.php" class="btn btn-danger">Cancel</a>
	</form>
</div>

<!-- Modal -->
<div id="paymentModal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Informasi Pembayaran</h4>
			</div>
			<div class="modal-body" id="paymentInfo">
				<!-- Konten akan dimasukkan lewat JavaScript -->
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary" onclick="submitForm()">Lanjutkan Order</button>
				<button class="btn btn-secondary" data-dismiss="modal">Batal</button>
			</div>
		</div>
	</div>
</div>

<!-- Bootstrap + jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

<!-- JavaScript -->
<script>
function handleOrder() {
	const metode = document.querySelector('input[name="metode_pembayaran"]:checked');
	if (!metode) {
		alert("Pilih metode pembayaran terlebih dahulu.");
		return;
	}

	let content = '';
	const total = <?= $hasil; ?>;

	switch (metode.value) {
		case 'ShopeePay':
			content = `<p>Silakan transfer ke Virtual Account ShopeePay berikut:</p>
					   <h4>VA ShopeePay: 8890 1234 5678 9012</h4>`;
			break;
		case 'Transfer Bank':
			content = `<p>Transfer ke Virtual Account berikut:</p>
					   <h4>VA BCA: 1234 5678 9012 3456</h4>`;
			break;
		case 'Tunai':
			content = `<p>Silakan siapkan uang sebesar:</p>
					   <h4>Rp. <?= number_format($hasil); ?></h4>`;
			break;
	}

	document.getElementById('paymentInfo').innerHTML = content;
	$('#paymentModal').modal('show');
}

function submitForm() {
	document.getElementById('checkoutForm').submit();
}
</script>

<?php include 'footer.php'; ?>
