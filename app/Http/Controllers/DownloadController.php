<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;

class DownloadController extends Controller
{
    
    public function __invoke(Request $request){


        $filename = $request->file_name;
        $headers = array(
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename='.$filename,
            'Expires' => '0',
            'Pragma' => 'public',
        );
    
        if(Storage::exists($filename)){
            return Storage::download($filename, $filename, $headers);
        }
    
        abort(404);
    }
}
