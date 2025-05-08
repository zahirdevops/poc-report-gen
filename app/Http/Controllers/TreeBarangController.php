<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TreeBarangController extends Controller
{
    //

    function tree()
    {

        $data = DB::select("
            select trb.kd_brg,nama_brg, concat(trb.kd_brg,trb2.kd_bidbrg) as kdbrg, trb2.nama_bidbrg , trk.kd_kelbrg, trk.nm_kel , 
                trs.kd_skelbrg , trs.nm_subkel,
                trs2.kd_brg, trs2.nm_brg
                from t_ref_barang trb 
                join t_ref_bidbrg trb2  on  trb2.kd_brg  = trb.kd_brg
                join t_ref_kelompok trk  on trk.kd_bidbrg = concat(trb2.kd_brg ,trb2.kd_bidbrg )
                join t_ref_subkelompok trs on trs.kd_kelbrg = trk.kd_kelbrg 
                join t_ref_subsubkelompok trs2 on trs2.kd_skelbrg = trs.kd_skelbrg
                join t_silog_alsuspol_has_kelompok tsahk on tsahk.kode_barang  = trs2.kd_brg 
                join t_ref_alsuspol tra on tra.id_alsuspol  = tsahk.id_alsuspol
                where tra.jenis='alat'
        ");

        $tree = [];

        foreach ($data as $row) {
            $kd_brg = $row->kd_brg;
            $kd_bidbrg = $row->kdbrg;
            $kd_kelbrg = $row->kd_kelbrg;
            $kd_skelbrg = $row->kd_skelbrg;

            // Level 1 - kd_brg
            if (!isset($tree[$kd_brg])) {
                $tree[$kd_brg] = [
                    'kd_brg' => $row->kd_brg,
                    'nama_brg' => $row->nama_brg,
                    'bidangs' => []
                ];
            }

            // Level 2 - kd_bidbrg
            if (!isset($tree[$kd_brg]['bidangs'][$kd_bidbrg])) {
                $tree[$kd_brg]['bidangs'][$kd_bidbrg] = [
                    'kd_bidbrg' => $kd_bidbrg,
                    'nama_bidbrg' => $row->nama_bidbrg,
                    'kelompoks' => []
                ];
            }

            // Level 3 - kd_kelbrg
            if (!isset($tree[$kd_brg]['bidangs'][$kd_bidbrg]['kelompoks'][$kd_kelbrg])) {
                $tree[$kd_brg]['bidangs'][$kd_bidbrg]['kelompoks'][$kd_kelbrg] = [
                    'kd_kelbrg' => $kd_kelbrg,
                    'nm_kel' => $row->nm_kel,
                    'subkelompoks' => []
                ];
            }

            // Level 4 - kd_skelbrg
            if (!isset($tree[$kd_brg]['bidangs'][$kd_bidbrg]['kelompoks'][$kd_kelbrg]['subkelompoks'][$kd_skelbrg])) {
                $tree[$kd_brg]['bidangs'][$kd_bidbrg]['kelompoks'][$kd_kelbrg]['subkelompoks'][$kd_skelbrg] = [
                    'kd_skelbrg' => $kd_skelbrg,
                    'nm_subkel' => $row->nm_subkel,
                    'leafs' => []
                ];
            }

            // Level 5 - Leaf
            $tree[$kd_brg]['bidangs'][$kd_bidbrg]['kelompoks'][$kd_kelbrg]['subkelompoks'][$kd_skelbrg]['leafs'][] = [
                'kd_brg' => $row->leaf_kd_brg,
                'nm_brg' => $row->leaf_nm_brg,
            ];
        }

        // Reindex arrays
        $tree = array_values(array_map(function ($item) {
            $item['bidangs'] = array_values(array_map(function ($bidang) {
                $bidang['kelompoks'] = array_values(array_map(function ($kelompok) {
                    $kelompok['subkelompoks'] = array_values(array_map(function ($sub) {
                        $sub['leafs'] = array_values($sub['leafs']);
                        return $sub;
                    }, $kelompok['subkelompoks']));
                    return $kelompok;
                }, $bidang['kelompoks']));
                return $bidang;
            }, $item['bidangs']));
            return $item;
        }, $tree));
        return response()->json($tree);
    }
}
