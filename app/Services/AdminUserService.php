<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Like;
use DataTables;
use DB;

class AdminUserService
{
    public function index($request)
    {
        $data = User::get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('name', function($row){
                return $row->name;                
            })
            ->editColumn('email', function($row){
                return $row->email;                
            })
            ->editColumn('image', function($row){
                return '<img class="avatar-img rounded-circle" alt="" src="'.$row->image.'">';                
            })
            ->addColumn('action', function($row){
                return '<a href="'.route('users.show', $row->id).'" class="btn bg-primary-light"><i class="fas fa-eye"></i> View</a><a href="'.route('users.edit', $row->id).'" class="btn bg-warning-light "><i class="fas fa-edit"></i> Edit</a><a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deleteUser" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i> Delete</a></td>';                
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $data = Like::where('from_id', $id)->where('match', '>', 0)->where('status', 1)->orderby('id', 'DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('to_id', function($row){
                return getUserNameById($row->to_id);                
            })
            ->editColumn('matches', function($row){
                return $row->matche;                
            })
            ->editColumn('date', function($row){
                return date('d F Y', strtotime($row->created_at));                
            })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    public function store($request)
    {        
        DB::beginTransaction();
        
        try {            

            if ($request->hasFile('user_image')) {
                $image = $request->file('user_image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/customer');
                $image->move($destinationPath, $imagename);
                $request['image'] = $imagename;
            }

           
            $request['password'] = Hash::make($request->confirm_password);
            $user = User::create($request->all());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User has been successfully created!',
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

            if ($request->hasFile('user_image')) {
                $image = $request->file('user_image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/customer');
                $image->move($destinationPath, $imagename);
                $request['image'] = $imagename;
            }
            
            $pass = User::where('id', $id)->pluck('password')->first();
            if (isset($request->confirm_password) && Hash::check($request->current_password, $pass)) {                                
                $request['password'] = Hash::make($request->confirm_password);                                    
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incurrect!',
                ]);
            }
           
            $user = User::where('id', $id)->update($request->except('_token', '_method', 'current_password', 'confirm_password', 'user_image', 'image_name'));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User has been successfully updated!',
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

            User::findOrFail($id)->delete();
            DB::table('notifications')->where('from_id', $id)->orWhere('to_id', $id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User has been successfully deleted!',
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
