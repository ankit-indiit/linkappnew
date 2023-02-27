<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Term;
use DataTables;
use DB;

class AdminTermsService
{
    public function index($request)
    {
        $data = Term::get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('title', function($row){
                return $row->title;                
            })
            ->editColumn('description', function($row){
                return '<span data-toggle="tooltip" data-placement="top" title="'.$row->description.'">'.$row->short_desc.'</span>';
            })            
            ->addColumn('action', function($row){
                return '<a href="'.route('terms.edit', $row->id).'" class="btn bg-warning-light "><i class="fas fa-edit"></i> Edit</a><a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deleteTerm" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i> Delete</a></td>';                
            })
            ->rawColumns(['description','action'])
            ->make(true);
    }

    public function store($request)
    {        
        DB::beginTransaction();
        
        try {           
           
            Term::create($request->all());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Term has been successfully created!',
            ]);

        } catch (\Exception $e) {            
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update($request, $id)
    {                
        DB::beginTransaction();
        
        try {

            Term::where('id', $id)->update($request->except('_token', '_method'));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Term has been successfully updated!',
            ]);

        } catch (\Exception $e) {            
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }        
    }

    public function destroy($id)
    {
        DB::beginTransaction();
    
        try {

            Term::findOrFail($id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Term has been successfully deleted!',
            ]);

        } catch (\Exception $e) {            
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
