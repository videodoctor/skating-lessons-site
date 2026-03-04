<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index()
    {
        return view('admin.assessments');
    }
    
    public function create()
    {
        return view('admin.assessments');
    }
    
    public function show($id)
    {
        return view('admin.assessments');
    }
    
    public function update(Request $request, $id)
    {
        return redirect()->back();
    }
}
