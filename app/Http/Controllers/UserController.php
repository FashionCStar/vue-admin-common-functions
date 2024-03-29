<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmUserMail;
use App\User;
use App\Mail\VerifyMail;
use Mail;
use Illuminate\Support\Facades\Storage;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  public function login()
  {
    $credentials = [
      'email' => request('email'),
      'password' => request('password')
    ];

    if (Auth::attempt($credentials)) {
      $user = User::find(Auth::id());
      return response()->json($this->successResponse($user), 200);
    }

    return response()->json([
      'error' => 'Unauthorised',
      'message' => 'Wrong email or password.'
    ], 404);
  }

  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required|email',
      'password' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json(['message'=>'User Info is not correct, Please check again','error' => $validator->errors()], 401);
    }

    $input = $request->all();
    $input['password'] = bcrypt($input['password']);

    $user = User::create($input);
    $successUser = $this->successResponse($user);
    $this->sendConfirmEmail($user->email, $successUser['token']);
    return response()->json(['success' => $successUser]);
  }

  public function sendConfirmEmail($email, $token) {
    Mail::to($email)->send(new ConfirmUserMail($email, $token));
  }

  public function uploadUserAvatar(Request $request)
  {
    $this->validate($request, [
      'image' => 'required|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
      $file = $request->file('image');
      $name = time() . $file->getClientOriginalName();
      $filePath = $name;
      Storage::disk('s3')->put($filePath, file_get_contents($file));
      $path = Storage::disk('s3')->url($name);
      return response()->json([
        'success' => 'upload success',
        'path' => $path
      ], 200);
    } else {
      return response()->json([
        'message' => 'Upload failed',
      ], 404);
    }
  }

  public function confirmUser(Request $request) {
//    return $request->user();
    $user = Auth::user();
    $user->update(['is_active' => 1]);
    return response()->json([
      'success' => 'User Verification is success',
      'user' => $user
    ], 200);
  }
  public function validateUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'phone' => 'required',
      'driver_license' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json(['message' => $validator->errors()], 401);
    }
    $input = $request->all();
    $validateItems = ['email', 'phone', 'driver_license'];
    $validateKeys = ['email' => 'Email', 'phone' => 'Phone Number', 'driver_license' => 'Driver License / ID Number'];
    foreach ($validateItems as $item) {
      $userCount = count(User::where($item, $input[$item])->get());
      if ($userCount) {
        return response()->json(['message' => $validateKeys[$item] . ' ' . $input[$item] . ' is already exist'], 409);
      }
    }
    return response()->json(['success' => $input]);
  }

  public function sendVerifyEmail(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email'
    ]);

    if ($validator->fails()) {
      return response()->json(['message' => 'Email Verification is failed, Please try again'], 401);
    }
    $input = $request->all();
    $to_email = $input['email'];
    $verification_code = mt_rand(100000, 999999);
    $encoded_code = base64_encode($verification_code + 111111);

    try {
      Mail::to($to_email)->send(new VerifyMail($input, $verification_code));
      return response()->json([
        'success' => 'Verification is sent to your email',
        'code' => $encoded_code
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Email Verification is failed, Please try again'], 401);
    }
  }

  public function myProfile(Request $request)
  {
    return response()->json(['success' => 'success', 'user' => Auth::user()]);
  }
  public function updateProfile(Request $request)
  {
    $userData = $request['data'];
    $user = Auth::user();
    $user->update($userData);
    return response()->json(['success' => 'success', 'user' => $user]);
  }

  public function getBookings(): \Illuminate\Support\Collection
  {
    $bookings = DB::table('bookings')
      ->join('meeting_rooms', 'bookings.room_id', '=', 'meeting_rooms.id')
      ->select('bookings.*', 'meeting_rooms.name')
      ->where('user_id', '=', Auth::id())
      ->where('end_at', '>=', date('Y-m-d H:i:s'))
      ->orderBy('start_at')
      ->get();
    return $bookings->groupBy('name');
  }

  private function successResponse(User $user)
  {
    $freshToken = $user->createToken('MyApp');
    $success['user'] = $user;
    $success['token'] = $freshToken->accessToken;
    $success['expiresAt'] = $freshToken->token->expires_at;

    return $success;
  }
}
