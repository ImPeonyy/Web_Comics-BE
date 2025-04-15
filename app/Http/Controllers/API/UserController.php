<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;

class UserController extends Controller
{
    // Đăng ký
    public function register(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $request->validate([
                'username' => 'required|string|max:50|unique:users',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
            ], [
                'username.unique' => 'Tên người dùng đã được sử dụng!',
                'email.unique' => 'Email đã được sử dụng!',
            ]);

            // Tạo user mới
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'exp' => 0,
                'avatar' => User::DEFAULT_AVATAR
            ]);

            // Tạo token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Trả về phản hồi thành công
            return response()->json([
                'message' => 'Đăng ký thành công',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (ValidationException $e) {
            // Lấy tất cả lỗi validation dưới dạng mảng phẳng
            $errors = array_merge(...array_values($e->errors()));
            return response()->json([
                'messages' => $errors, // Trả về mảng các thông báo lỗi
            ], 422);
        } catch (\Exception $e) {
            // Trả về mảng chứa một lỗi hệ thống
            return response()->json([
                'messages' => ['Đăng ký thất bại: ' . $e->getMessage()],
            ], 500);
        }
    }

    // Đăng nhập
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không hợp lệ'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'id' => $user->id,
            'token' => $token,
        ], 200);
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        // Lấy user hiện tại từ token (yêu cầu đã được xác thực)
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Không có người dùng nào đang đăng nhập',
            ], 401);
        }

        // Thu hồi token hiện tại
        $request->user()->currentAccessToken()->delete();

        // Trả về phản hồi
        return response()->json([
            'message' => 'Đăng xuất thành công',
        ], 200);
    }

    public function uploadAvatar(Request $request)
    {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        $user = auth()->user();
        $userId = auth()->user()->id;

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $uploadedFile = $cloudinary->uploadApi()->upload($request->file('avatar')->getRealPath(), [
            'folder' => 'avatars',
            'public_id' => 'avatar_' . $userId,
            'overwrite' => true,
            'transformation' => [
                ['width' => 250, 'crop' => 'scale'],
                ['quality' => 'auto'],
                ['fetch_format' => 'avif'],
            ],
        ]);

        $url = $uploadedFile['secure_url'];
        $user->update(['avatar' => $url]);
        return response()->json([
            'url' => $url
        ]);

    }

    // Thay đổi mật khẩu
    public function changePassword(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $request->validate([
                'oldPassword' => 'required|string',
                'newPassword' => 'required|string|min:6',
                'confirmPassword' => 'required|same:newPassword',
            ]);

            $user = Auth::user();

            // Kiểm tra mật khẩu hiện tại
            if (!Hash::check($request->oldPassword, $user->password)) {
                return response()->json([
                    'message' => 'Mật khẩu cũ không chính xác!'
                ], 400);
            }

            // Cập nhật mật khẩu mới
            $user->update([
                'password' => Hash::make($request->newPassword)
            ]);

            return response()->json([
                'message' => 'Đổi mật khẩu thành công'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đổi mật khẩu thất bại!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Lấy tất cả users (chỉ admin)
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Lấy 1 user
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Sửa user
    public function update(Request $request)
    {
        try {
            // Lấy user từ Auth
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Validate dữ liệu đầu vào
            $request->validate([
                'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id,
                'email' => 'sometimes|string|email|max:100|unique:users,email,' . $user->id,
            ], [
                'username.unique' => 'Tên người dùng đã được sử dụng!',
                'email.unique' => 'Email đã được sử dụng!',
            ]);

            // Chỉ cập nhật username và email
            $updateData = array_filter([
                'username' => $request->username,
                'email' => $request->email,
            ]);

            $user->update($updateData);

            return response()->json([
                'message' => 'Cập nhật thông tin thành công',
                'user' => $user
            ], 200);

        } catch (ValidationException $e) {
            // Lấy tất cả lỗi validation dưới dạng mảng phẳng
            $errors = array_merge(...array_values($e->errors()));
            return response()->json([
                'messages' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cập nhật thông tin thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xóa user (chỉ admin)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Xóa thành công'
        ], 204);
    }


}