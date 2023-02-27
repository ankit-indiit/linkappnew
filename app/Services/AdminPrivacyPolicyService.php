<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\PrivacyPolicy;
use DataTables;
use DB;

class AdminPrivacyPolicyService
{
    public function index($request)
    {
        $data = PrivacyPolicy::get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('title', function($row){
                return $row->title;                
            })
            ->editColumn('description', function($row){
                return $row->short_desc;    
            })            
            ->addColumn('action', function($row){
                return '<a href="'.route('privacy-policy.edit', $row->id).'" class="btn bg-warning-light "><i class="fas fa-edit"></i> Edit</a><a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deletePrivacyPolicy" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i> Delete</a></td>';                
            })
            ->rawColumns(['description','action'])
            ->make(true);
    }

    public function store($request)
    {        
        DB::beginTransaction();
        
        try {           
           
            PrivacyPolicy::create($request->all());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Privacy Policy has been successfully created!',
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

            PrivacyPolicy::where('id', $id)->update($request->except('_token', '_method'));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Privacy Policy has been successfully updated!',
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

            PrivacyPolicy::findOrFail($id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Privacy Policy has been successfully deleted!',
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
