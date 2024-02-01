<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuForAppsController extends Controller
{
    public function index()
    {
        // Initialize
        $param = 'mengapa-memilih-kami-perusahaan-id';

        if (request('menu')) {
            $param = request('menu');
        }

        switch ($param) {
            case 'mengapa-memilih-kami-perusahaan-id':
                $data = $this->_submenuCompanyID();
                break;
            case 'mengapa-memilih-kami-perusahaan-en':
                $data = $this->_submenuCompanyEN();
                break;
            case 'mengapa-memilih-kami-klien-id':
                $data = $this->_submenuClientID();
                break;
            case 'mengapa-memilih-kami-klien-en':
                $data = $this->_submenuClientEN();
                break;
            
            default:
                $data = $this->_submenuCompanyID();
                break;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    private function _submenuCompanyID()
    {
        return '<p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Mengapa memilih kami?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">- Bangun atau Renov Properti Impian Anda</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Bangun properti impian Anda</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Anda DAPAT melakukan bidding proyek untuk Renovasi dan pembangunan properti impian Anda. Temukan Arsitek, desain interior dan penjual Barang dan Jasa Konstruksi lainnya sesuai kebutuhan dengan sistem transparan sehingga aman dan nyaman.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:#ffc000">Dapatkan Penawaran untuk Proyek Anda&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cari Barang dan Jasa yang Anda Butuhkan</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Temukan di Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">1. Penjual barang dan jasa terverifikasi. Memiliki portofolio, kompetensi, kantor dan tim yang jelas</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">2. Arsitek, Kontraktor, Desain Interior Berpengalaman sesuai bidangnya masing-masing&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">3. Biaya dapat dinegosiasi langsung dengan penyedia jasa dengan pengawasan Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">4. Detail pekerjaan dan biaya tercantum dengan jelas di surat perjanjian dan RAB</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">5. Progres tercatat langsung, Anda bisa lihat dan dibantu mediasi oleh Archiloka jika belum sesuai kesepakatan</span></span></span></span></span></span></p>

            <p>&nbsp;</p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">KENAPA MENGGUNAKAN ARCHILOKA, KENAPA TIDAK YANG LAINNYA SAJA?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Khawatir dengan Vendor yang Tidak Berpengalaman?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka vendor-vendornya berpengalaman dan terverifikasi&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Biaya yang tinggi dan harus dibayar penuh sebelum proyek di mulai?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka Anda dapat diskusi dan nego pembayaran penuh atau cicilan lengkap dengan uang muka dan termin-terminnya, RAB Diberikan Detail dan semua Transparan</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Sulit mengetahui progres pekerjaan yang sudah diselesaikan?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka progres proyek Anda dipantau dan vendor wajib melaporkan secara bertahap dan terkait dengan termin pembayaran. Anda dapat komplain di chat room dan dapat meminta panggilan video untuk melihat progres. Archiloka dan afiliasi kami melakukan mediasi agar Anda dan vendor mencapai solusi bersama.&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Khawatir vendor tidak tanggung jawab selama dan sesudah selesai proyek?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Anda dapat meminta MOU yang ditandatangani vendor dan Anda. Anda dapat meminta pembayaran bertahap/cicilan, uang muka dan termin dapat Anda usulkan dan negosiasikan sehingga Anda dapat menilai pertanggungjawaban vendor dan progresnya di setiap termin. Proyek disupervisi dan dimediasi Archiloka dan afiliasinya. Retensi disarankan untuk diberikan oleh vendor. Anda dapat komplain dan memberikan ulasan mengenai vendor.</span></span></span></span></span></span></p>

            <p>&nbsp;</p>';
    }

    private function _submenuCompanyEN()
    {
        return '<p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Why choose us?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">- Grow Your Business</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Grow your construction business</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">1. REGISTER Your company as a seller in Archiloka, Complete your portfolio, competencies, office photos and your Team.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">2. After verified by Archiloka&#39;s team, ADD to Archiloka all Goods and Services that you offer</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">3. SELL your Goods and Services, you CAN communicate directly with clients or project owners</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:#ffc000">Business List&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Get Lots of Projects and Clients via Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">1. Projects from various clients. Complete data accompanied by a clear estimate of the budget and completion time. You can offer the best deals for various projects.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">2. Offer your goods such as materials and equipment. You can offer goods to be bought or to be rented by clients.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">3. Offer your interior architectural services. Clients can discuss and negotiate directly with you</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">WHY SHOULD YOU USE ARCHILOKA, WHY NOT THE OTHERS?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">As an experienced and verified vendor YOU can sell goods and services and submit bids for all existing projects.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">You can display your portfolio and competencies as a good showcase so that your company is easily be found by your existing and new clients</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">You can discuss and negotiate with clients to reach the best deal</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Transparency of your work progresses can be done while strengthening your company&#39;s image and establishing good relations with clients</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">You are accompanied by Archiloka and its affiliates at every stage so that client complaints can meet your best solutions</span></span></span></span></span></span></p>
            ';
    }

    private function _submenuClientID()
    {
        return '<p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">- Bangun atau Renov Properti Impian Anda</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Bangun properti impian Anda</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Anda DAPAT melakukan bidding proyek untuk Renovasi dan pembangunan properti impian Anda. Temukan Arsitek, desain interior dan penjual Barang dan Jasa Konstruksi lainnya sesuai kebutuhan dengan sistem transparan sehingga aman dan nyaman.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:#ffc000">Dapatkan Penawaran untuk Proyek Anda&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cari Barang dan Jasa yang Anda Butuhkan</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Temukan di Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">1. Penjual barang dan jasa terverifikasi. Memiliki portofolio, kompetensi, kantor dan tim yang jelas</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">2. Arsitek, Kontraktor, Desain Interior Berpengalaman sesuai bidangnya masing-masing&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">3. Biaya dapat dinegosiasi langsung dengan penyedia jasa dengan pengawasan Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">4. Detail pekerjaan dan biaya tercantum dengan jelas di surat perjanjian dan RAB</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">5. Progres tercatat langsung, Anda bisa lihat dan dibantu mediasi oleh Archiloka jika belum sesuai kesepakatan</span></span></span></span></span></span></p>

            <p>&nbsp;</p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">KENAPA MENGGUNAKAN ARCHILOKA, KENAPA TIDAK YANG LAINNYA SAJA?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Khawatir dengan Vendor yang Tidak Berpengalaman?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka vendor-vendornya berpengalaman dan terverifikasi&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Biaya yang tinggi dan harus dibayar penuh sebelum proyek di mulai?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka Anda dapat diskusi dan nego pembayaran penuh atau cicilan lengkap dengan uang muka dan termin-terminnya, RAB Diberikan Detail dan semua Transparan</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Sulit mengetahui progres pekerjaan yang sudah diselesaikan?&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Di Archiloka progres proyek Anda dipantau dan vendor wajib melaporkan secara bertahap dan terkait dengan termin pembayaran. Anda dapat komplain di chat room dan dapat meminta panggilan video untuk melihat progres. Archiloka dan afiliasi kami melakukan mediasi agar Anda dan vendor mencapai solusi bersama.&nbsp;</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Khawatir vendor tidak tanggung jawab selama dan sesudah selesai proyek?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Anda dapat meminta MOU yang ditandatangani vendor dan Anda. Anda dapat meminta pembayaran bertahap/cicilan, uang muka dan termin dapat Anda usulkan dan negosiasikan sehingga Anda dapat menilai pertanggungjawaban vendor dan progresnya di setiap termin. Proyek disupervisi dan dimediasi Archiloka dan afiliasinya. Retensi disarankan untuk diberikan oleh vendor. Anda dapat komplain dan memberikan ulasan mengenai vendor.</span></span></span></span></span></span></p>

            <p>&nbsp;</p>';
    }

    private function _submenuClientEN()
    {
        return '<p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Why choose us?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">- Build or Renovate Your Dream Property</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Build Your DREAM PROPERTY</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">You CAN submit renovation or construction projects of your dream property. Find Architects, interior designers and sellers of other Construction Goods and Services as needed within a transparent system in Archiloka so that it is safe and comfortable for you.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:#ffc000">Get Best Offer for Your Project&nbsp; &nbsp; &nbsp; Find the Goods and Services You Need</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Find at Archiloka</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">1. Verified sellers of goods and services. Have a clear portfolio, competencies, office and teams</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">2. Experienced Architects, Contractors, Interior Designs in their respective fields</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">3. You can negotiate prices or tariffs directly with sellers under Archiloka supervision</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">4. Details of work and costs are clearly stated in the MOU and RAB</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">5. Progresses are reported by sellers immediately, you can see and be assisted in mediation by Archiloka if it is not according to the agreement</span></span></span></span></span></span></p>

            <p>&nbsp;</p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">WHY SHOULD YOU USE ARCHILOKA, WHY NOT THE OTHERS?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Worried about Inexperienced Vendors?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">At Archiloka, the vendors are experienced and verified</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Afraid of High costs and full payments before the project starts?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">At Archiloka you can discuss and negotiate full payments or installments including down payment and terms, the RAB is given details by sellers and all transparent</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Difficult to know the progress of work that has been completed?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">At Archiloka the progress of your project is monitored and vendors are required to report in stages and the reports are related to payment terms. You can complain in the chat room and you can request a video call to see progress. Archiloka and our affiliates mediate you and seller so that you and the vendor reach a best solution.</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">Worried that the vendor is not responsible during and after the project is finished?</span></span></span></span></span></span></p>

            <p><span style="font-size:12pt"><span style="background-color:white"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Calibri&quot;,sans-serif"><span style="color:black">You can request an MOU signed by the vendor and you. You can request a gradual/installment payment, you can propose and negotiate the down payment and terms so that you can assess the vendor&#39;s accountability and progress in each term. The project is supervised and mediated by Archiloka and its affiliates. Retention is suggested to be provided by the vendor. You can complain and provide reviews about vendors.</span></span></span></span></span></span></p>
            ';
    }
}
