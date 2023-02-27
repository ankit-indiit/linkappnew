<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Subscription;
use DataTables;
use DB;

class AdminSubscriptionService
{
    public function index($request)
    {
        $data = Subscription::get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('title', function($row){
                return $row->title;                
            })
            ->editColumn('price', function($row){
                return $row->price;                
            })
            ->editColumn('type', function($row){
                return $row->type;                
            })
            ->editColumn('description', function($row){
                return $row->description;                
            })
            ->editColumn('date', function($row){
                return $row->created_at;                
            })        
            ->addColumn('action', function($row){
                return '<a href="'.route('subscription.edit', $row->id).'" class="btn bg-warning-light "><i class="fas fa-edit"></i> Edit</a><a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deletePlan" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i> Delete</a></td>';                
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store($request)
    {        
        DB::beginTransaction();
        
        try {           
           
            Subscription::create($request->all());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Subscription has been successfully created!',
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

            Subscription::where('id', $id)->update($request->except('_token', '_method'));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Subscription has been successfully updated!',
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

            Subscription::findOrFail($id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Subscription has been successfully deleted!',
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
