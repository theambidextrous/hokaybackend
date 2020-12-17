<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Config;
use App\Models\User;
use App\Mail\Subscribed;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function signin(Request $request)
    {
        $login = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if( !Auth::attempt( $login ) )
        {
            return response([
                'status' => 201,
                'message' => "Invalid username or password. Try again",
                'errors' => [],
            ], 403);
        }
        $accessToken = Auth::user()->createToken('authToken')->accessToken;
        $user = Auth::user();
        $user['token'] = $accessToken;
        return response([
            'status' => 200,
            'message' => 'Success. logged in',
            'payload' => $user,
        ], 200);
    }
    public function u_alert(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'period' => 'required',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => "enter valid email",
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            if( $this->isSubscribed($input['email']) )
            {
                return response([
                    'status' => 200,
                    'message' => 'You are already subscribed',
                    'payload' => [],
                ], 200);
            }
            $user = User::create($input);
            $a_jobs = Config::get('app.front_url');
            $a_period = $this->alert_period($input['period']);
            Mail::to($input['email'])->send(new Subscribed($a_period, $a_jobs));
            return response([
                'status' => 200,
                'message' => 'Success. Alert set',
                'payload' => [],
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 201,
                'message' => "System refused your request",
                'errors' => [$e->getMessage()],
            ], 403);
        } catch (PDOException $e) {
            return response([
                'status' => 201,
                'message' => "Network refused your request",
                'errors' => [],
            ], 403);
        }catch (Exception $e) {
            return response([
                'status' => 201,
                'message' => "Network refused. Try again",
                'errors' => [],
            ], 403);
        }
    }
    protected function isSubscribed($email)
    {
        if( User::where('email', $email)->count() )
        {
            return true;
        }
        return false;
    }
    protected function alert_period($p)
    {
        if( $p == 1 )
        {
            return 'daily';
        }
        return 'weekly';
    }
    public function signup(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'company' => 'string',
                'password' => 'required|string',
                'c_password' => 'required|same:password',
                'has_news' => 'required'
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => "Forbidden. Errors occured",
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $access_token = $user->createToken('authToken')->accessToken;
            $user['token'] = $access_token;
            return response([
                'status' => 200,
                'message' => 'Success. User created',
                'payload' => $user,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 201,
                'message' => "Server error. Invalid data",
                'errors' => [],
            ], 403);
        } catch (PDOException $e) {
            return response([
                'status' => 201,
                'message' => "Db error. Invalid data",
                'errors' => [],
            ], 403);
        }
    }
    public function info()
    {
        return response([
            'status' => 0,
            'message' => 'fetch successful',
            'payload' => [
                'user' => []
            ] 
        ], 200);
    }
    public function is_active()
    {
        return response([
            'status' => 0,
            'message' => 'check successful',
            'payload' => [
                'user' => []
            ] 
        ], 200);
    }
}
