<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	protected $center;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('tpsModel');
		$this->load->library('googlemaps');
		$this->center = getPengaturanWebsite("center_map_lat") . ',' . getPengaturanWebsite("center_map_lng");

		logged_in();
	}


	public function index()
	{

		$D = $this->db->select("COUNT(id_tps) as tps, (
			SELECT COUNT(id_jenistps) from tb_jenistps
		) jenistps,(
			SELECT COUNT(id_kontak) from tb_kontak
		) kontak,(
			SELECT COUNT(id_user) from tb_user
		) user")->get('tb_tps')->row_array();


		// $globalSettingMarker = "icon:{url:'" . base_url("assets/img/icon/me.png") . "'}, animation: google.maps.Animation.BOUNCE";
		// ambil semua tps
		$tps = $this->tpsModel->getData()->result_array();






		$config['center'] = $this->center;
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);
		$HTML = "";
		foreach ($tps as $value) {
			$gambar = 'default.jpg';
			if (!empty($value['gambar']))
				$gambar = base_url("uploads/img/" . $value['gambar']);
			$alamat = str_replace(array("\n", "\r"), '', $value['alamat']);
			$keterangan = str_replace(array("\n", "\r"), '', $value['keterangan']);

			$HTML .= "<div class='media'>";
			$HTML .= "<div class='media-left'>";
			$HTML .= "<img src='$gambar' class='media-object' style='width:150px;'>";
			$HTML .= "</div>";
			$HTML .= "<div class='media-body'>";
			$HTML .= "<ul style='list-style-type:none;'>";
			$HTML .= "<li><h5>$value[nama_tps]</h5></li>";
			$HTML .= "<li>ALamat, $alamat</li>";
			$HTML .= "<li>No Telp, $value[telp]</li>";
			$HTML .= "<li>Keterangan, $keterangan</li>";
			$HTML .= "</ul>";
			$HTML .= '<a style="float:right;"class="btn btn-info" href="' . base_url('persebaran/' . encode($value['id_tps'])) . '" >Cari </a>';

			$HTML .= "</div>";
			$HTML .= "</div>";

			$marker = array();
			$marker['position'] = "$value[lat],$value[lng]";
			$marker['title'] = $value['nama_tps'];
			$marker['infowindow_content'] = $HTML;
			$HTML = "";


			$marker['icon'] = base_url("uploads/img/" . $value['marker']);
			$this->googlemaps->add_marker($marker);
		}



		$data = [
			'title' => 'Dashboard',
			'data' => $D,
			'map' => $this->googlemaps->create_map(),
		];
		$this->render('admin/v_dashboard_index', $data);
	}

	public function cetak_laporan()
	{

		if ($this->session->userdata('logged_in') != "" && $this->session->userdata('id_role') == "1") {

			$data = array();
			$data['cetak_laporan'] = $this->m_dashboard->all_costumer();

			$this->load->library('pdf');
			$this->pdf->load_view('cetak_laporan', $data);
			$this->pdf->setPaper('A4', 'portrait');
			$this->pdf->render();
			$this->pdf->stream("Cetak Laporan.pdf");
		} else {
			redirect('login/logout');
		}
	}
}
