<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \stdClass;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Reporte;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ApiUserController extends Controller
{
    public function login(Request $request){
        $email=$request->email;
        $password=$request->password;

        $user=User::where('email',$email)->get()->first();
        $res = new stdClass();        
        if ($user!=null){
            if (Hash::check($password, $user->password)){
                return $user;
            }else{
                $res->error="ERROR101";
                $res->message="Password Incorrecto";
                $myJSON= json_encode($res);
                return $myJSON; 
            }
        }else{
            $res->error="ERROR100";
            $res->message="No existe la cuenta con email: $email";
            $myJSON= json_encode($res); 
            return $myJSON;
        } 
    }

    public function register(Request $request){
        $res = new stdClass();        
        $email=User::where('email',$request->email)->get()->first();
        if ($email==null){
            /* do {
                $token = Str::uuid();
            } while (User::where("token", $token)->first() instanceof User); */
            $user=User::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'fechanacimiento'=>$request->fechanacimiento,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'token' =>Str::uuid(),
            ]);
            return $user;
        }else{
            $res->error="ERROR100";
            $res->message="El email: $request->email ya se encuentra registrado";
            $myJSON= json_encode($res); 
            return $myJSON;
        }
    }
    public function uploadimage(Request $request){
        /* $url="";
        if ($request->url!==null){
        $imagenes=$request->file('url')->store('public/vehiculos');
        $url=Storage::url($imagenes);
        Reporte::create([
            'url' => $url,
        ]);
        } */
        $dir="imagenes/";
        $image=$request->file('image');
        if ($request->has('image')){
            $imageName=\Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . "png";
            if (!Storage::disk('public')->exists($dir)){
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->put($dir.$imageName,file_get_contents($image));
        }else{
            return response()->json(['message'=>trans('storage/imagenes/'.'def.png')],200);
        }
        Reporte::create([
            'url' => $imageName,
        ]);
        return response()->json(['message'=>trans('storage/imagenes'.$imageName)],200);
    }
}
