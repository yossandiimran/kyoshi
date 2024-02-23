<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\ProdukBarang;
use App\Http\Helpers\helpers;
use PDF;

class ProdukBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

        return view('produk_barang.index', compact('kategori'));
    }

    public function data()
    {
        $produk_barang = ProdukBarang::leftJoin('kategori', 'kategori.id_kategori', 'produk_barang.id_kategori')
            ->select('produk_barang.*', 'nama_kategori')
            // ->orderBy('kode_produk', 'asc')
            ->get();

        return datatables()
            ->of($produk_barang)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk_barang) {
                return '
                    <input type="checkbox" name="id_produk_barang[]" value="'. $produk_barang->id_produk_barang .'">
                ';
            })
            ->addColumn('kode_produk', function ($produk_barang) {
                return '<span class="label label-success">'. $produk_barang->kode_produk .'</span>';
            })
            ->addColumn('harga_beli', function ($produk_barang) {
                return format_uang($produk_barang->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk_barang) {
                return format_uang($produk_barang->harga_jual);
            })
            ->addColumn('stok', function ($produk_barang) {
                return format_uang($produk_barang->stok);
            })
            ->addColumn('aksi', function ($produk_barang) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('produk_barang.update', $produk_barang->id_produk_barang) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('produk_barang.destroy', $produk_barang->id_produk_barang) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_produk', 'select_all'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $produk_barang = ProdukBarang::latest()->first() ?? new ProdukBarang();
        $request['kode_produk'] = 'B-'. tambah_nol_didepan((int)$produk_barang->id_produk_barang+1, 6);

        $produk_barang = ProdukBarang::create($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk_barang = ProdukBarang::find($id);

        return response()->json($produk_barang);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $produk_barang = ProdukBarang::find($id);
        $produk_barang->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk_barang = ProdukBarang::find($id);
        $produk_barang->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk_barang as $id) {
            $produk_barang = ProdukBarang::find($id);
            $produk_barang->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->id_produk_barang as $id) {
            $produk_barang = ProdukBarang::find($id);
            $dataproduk[] = $produk_barang;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }
}
